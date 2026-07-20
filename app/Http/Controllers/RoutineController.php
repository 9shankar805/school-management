<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoutineStoreRequest;
use App\Models\Routine;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\RoutineRepository;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;

class RoutineController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    /** Full timetable grid view (all classes / teacher view) */
    public function index()
    {
        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        return view('routines.index', compact('classes', 'session_id'));
    }

    /** Create form */
    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'classes'                   => $school_classes,
        ];

        return view('routines.create', $data);
    }

    /**
     * Store with conflict detection.
     * A conflict exists when the same section already has a slot that overlaps
     * on the same weekday, OR the same teacher is already booked in any section
     * at an overlapping time on that day.
     */
    public function store(RoutineStoreRequest $request)
    {
        $validated  = $request->validated();
        $session_id = $validated['session_id'];
        $weekday    = $validated['weekday'];
        $start      = $validated['start'];
        $end        = $validated['end'];
        $section_id = $validated['section_id'];
        $teacher_id = $request->input('teacher_id');

        // ── Section conflict: same section, same day, overlapping time ──────
        $sectionConflict = Routine::where('session_id', $session_id)
            ->where('section_id', $section_id)
            ->where('weekday', $weekday)
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->where('start', '<', $end)->where('end', '>', $start))
            )
            ->first();

        if ($sectionConflict) {
            return back()
                ->withInput()
                ->withErrors(['conflict' => "This section already has a class from {$sectionConflict->start} to {$sectionConflict->end} on that day."]);
        }

        // ── Teacher conflict: same teacher, same day, overlapping time ───────
        if ($teacher_id) {
            $teacherConflict = Routine::where('session_id', $session_id)
                ->where('teacher_id', $teacher_id)
                ->where('weekday', $weekday)
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2->where('start', '<', $end)->where('end', '>', $start))
                )
                ->first();

            if ($teacherConflict) {
                return back()
                    ->withInput()
                    ->withErrors(['conflict' => "The assigned teacher is already scheduled from {$teacherConflict->start} to {$teacherConflict->end} on that day."]);
            }
        }

        try {
            $routineRepository = new RoutineRepository();
            $routineRepository->saveRoutine(array_merge($validated, [
                'teacher_id' => $teacher_id,
                'room'       => $request->input('room'),
                'color'      => $request->input('color'),
            ]));

            return back()->with('status', 'Timetable slot saved successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /** Show timetable grid for a class+section */
    public function show(Request $request)
    {
        $class_id   = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $routineRepository = new RoutineRepository();
        $routines = $routineRepository->getAll($class_id, $section_id, $current_school_session_id);
        $routines = $routines->sortBy('start')->groupBy('weekday');

        $classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'routines'   => $routines,
            'classes'    => $classes,
            'class_id'   => (int) $class_id,
            'section_id' => (int) $section_id,
        ];

        return view('routines.show', $data);
    }

    /** Edit a slot */
    public function edit(Routine $routine)
    {
        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        return view('routines.edit', compact('routine', 'classes', 'session_id'));
    }

    /** Update a slot with conflict detection */
    public function update(Request $request, Routine $routine)
    {
        $request->validate([
            'start'   => 'required',
            'end'     => 'required',
            'weekday' => 'required|integer',
            'room'    => 'nullable|string|max:50',
            'color'   => 'nullable|string|max:20',
        ]);

        $session_id = $routine->session_id;
        $weekday    = $request->weekday;
        $start      = $request->start;
        $end        = $request->end;

        // Section conflict (exclude self)
        $conflict = Routine::where('session_id', $session_id)
            ->where('section_id', $routine->section_id)
            ->where('weekday', $weekday)
            ->where('id', '!=', $routine->id)
            ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
            ->first();

        if ($conflict) {
            return back()
                ->withInput()
                ->withErrors(['conflict' => "Slot conflicts with {$conflict->start}–{$conflict->end}."]);
        }

        $routine->update([
            'start'   => $start,
            'end'     => $end,
            'weekday' => $weekday,
            'room'    => $request->room,
            'color'   => $request->color,
        ]);

        return back()->with('status', 'Timetable slot updated.');
    }

    /** Delete a slot */
    public function destroy(Routine $routine)
    {
        $routine->delete();
        return back()->with('status', 'Slot removed from timetable.');
    }

    /**
     * JSON endpoint: return all slots for teacher timetable widget
     */
    public function teacherTimetable(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $teacher_id = $request->query('teacher_id', auth()->id());

        $slots = Routine::with(['course', 'schoolClass', 'section'])
            ->where('session_id', $session_id)
            ->where('teacher_id', $teacher_id)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get();

        return response()->json($slots);
    }
}
