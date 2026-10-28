<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoutineStoreRequest;
use App\Models\Routine;
use App\Models\Course;
use App\Models\AssignedTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $user = auth()->user();
        $teachersQuery = \App\Models\User::where(function ($q) {
            $q->whereIn('role', ['teacher', 'class-teacher', 'admin', 'principal'])
              ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['teacher', 'class-teacher']));
        });
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $teachersQuery->where('school_id', $user->school_id);
        }
        $teachers = $teachersQuery->orderBy('first_name')->get();

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'classes'                   => $school_classes,
            'teachers'                  => $teachers,
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
            return back()->with('error', $e->getMessage());
        }
    }

    /** Show timetable grid for a class+section */
    public function show(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $class_id   = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);

        // Auto-select first available class & section if none selected
        if (!$class_id && $classes->isNotEmpty()) {
            $firstClass = $classes->first();
            $class_id = $firstClass->id;
            if (!$section_id && $firstClass->sections->isNotEmpty()) {
                $section_id = $firstClass->sections->first()->id;
            }
        } elseif ($class_id && !$section_id) {
            $selectedCls = $classes->firstWhere('id', $class_id);
            if ($selectedCls && $selectedCls->sections->isNotEmpty()) {
                $section_id = $selectedCls->sections->first()->id;
            }
        }

        $routineRepository = new RoutineRepository();
        $rawRoutines = $routineRepository->getAll($class_id, $section_id, $current_school_session_id);
        $routines = $rawRoutines->sortBy('start')->groupBy('weekday');

        $selectedClass   = $classes->firstWhere('id', $class_id);
        $selectedSection = $selectedClass?->sections?->firstWhere('id', $section_id);

        $user = auth()->user();
        $teachersQuery = \App\Models\User::where(function ($q) {
            $q->whereIn('role', ['teacher', 'class-teacher', 'admin', 'principal'])
              ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['teacher', 'class-teacher']));
        });
        if ($user && $user->school_id && !$user->hasRole('super-admin')) {
            $teachersQuery->where('school_id', $user->school_id);
        }
        $teachers = $teachersQuery->orderBy('first_name')->get();

        $courses = $class_id ? Course::uniqueByName()->where('class_id', $class_id)->orderBy('course_name')->get() : collect();

        // Mappings from AssignedTeacher (course_id => teacher_id)
        $teacherMappings = [];
        if ($class_id && $section_id) {
            $teacherMappings = AssignedTeacher::where('session_id', $current_school_session_id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->pluck('teacher_id', 'course_id')
                ->toArray();
            if (empty($teacherMappings)) {
                $teacherMappings = AssignedTeacher::where('session_id', $current_school_session_id)
                    ->where('class_id', $class_id)
                    ->pluck('teacher_id', 'course_id')
                    ->toArray();
            }
        }

        // Available sections for cloning
        $cloneableSections = [];
        foreach ($classes as $cls) {
            foreach ($cls->sections as $sec) {
                if ($cls->id != $class_id || $sec->id != $section_id) {
                    $cloneableSections[] = [
                        'class_id'     => $cls->id,
                        'class_name'   => $cls->class_name,
                        'section_id'   => $sec->id,
                        'section_name' => $sec->section_name,
                        'label'        => "{$cls->class_name} - {$sec->section_name}",
                    ];
                }
            }
        }

        $currentSchool = $user?->school ?? \App\Models\School::first();
        $allSchools = ($user && $user->hasRole('super-admin')) ? \App\Models\School::all() : collect();

        $data = [
            'routines'          => $routines,
            'rawRoutines'       => $rawRoutines,
            'classes'           => $classes,
            'class_id'          => (int) $class_id,
            'section_id'        => (int) $section_id,
            'selectedClass'     => $selectedClass,
            'selectedSection'   => $selectedSection,
            'teachers'          => $teachers,
            'courses'           => $courses,
            'teacherMappings'   => $teacherMappings,
            'cloneableSections' => $cloneableSections,
            'currentSchool'     => $currentSchool,
            'allSchools'        => $allSchools,
            'session_id'        => $current_school_session_id,
        ];

        return view('routines.show', $data);
    }

    /** Edit a slot */
    public function edit(Routine $routine)
    {
        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        $user = auth()->user();
        $teachersQuery = \App\Models\User::where(function ($q) {
            $q->whereIn('role', ['teacher', 'class-teacher', 'admin', 'principal'])
              ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['teacher', 'class-teacher']));
        });
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $teachersQuery->where('school_id', $user->school_id);
        }
        $teachers = $teachersQuery->orderBy('first_name')->get();
        $courses  = \App\Models\Course::uniqueByName()->where('class_id', $routine->class_id)->orderBy('course_name')->get();

        return view('routines.edit', compact('routine', 'classes', 'session_id', 'teachers', 'courses'));
    }

    /** Update a slot with conflict detection */
    public function update(Request $request, Routine $routine)
    {
        $data = $request->validate([
            'start'      => 'required',
            'end'        => 'required',
            'weekday'    => 'required|integer|min:1|max:7',
            'course_id'  => 'nullable|exists:courses,id',
            'teacher_id' => 'nullable|exists:users,id',
            'room'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|max:20',
        ]);

        $session_id = $routine->session_id;
        $weekday    = $data['weekday'];
        $start      = $data['start'];
        $end        = $data['end'];
        $teacher_id = $request->input('teacher_id');

        // Section conflict (exclude self)
        $conflict = Routine::where('session_id', $session_id)
            ->where('section_id', $routine->section_id)
            ->where('weekday', $weekday)
            ->where('id', '!=', $routine->id)
            ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
            ->first();

        if ($conflict) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Slot conflicts with an existing class ({$conflict->start}–{$conflict->end}) on that day."
                ], 422);
            }
            return back()
                ->withInput()
                ->withErrors(['conflict' => "Slot conflicts with an existing class ({$conflict->start}–{$conflict->end}) on that day."]);
        }

        // Teacher conflict (exclude self)
        if ($teacher_id) {
            $teacherConflict = Routine::where('session_id', $session_id)
                ->where('teacher_id', $teacher_id)
                ->where('weekday', $weekday)
                ->where('id', '!=', $routine->id)
                ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
                ->first();

            if ($teacherConflict) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "The selected teacher is already scheduled in another section from {$teacherConflict->start} to {$teacherConflict->end} on that day."
                    ], 422);
                }
                return back()
                    ->withInput()
                    ->withErrors(['conflict' => "The selected teacher is already scheduled in another section from {$teacherConflict->start} to {$teacherConflict->end} on that day."]);
            }
        }

        $updateData = [
            'start'   => $start,
            'end'     => $end,
            'weekday' => $weekday,
            'room'    => $request->room,
            'color'   => $request->color,
        ];
        if ($request->filled('course_id')) {
            $updateData['course_id'] = $request->course_id;
        }
        if ($request->has('teacher_id')) {
            $updateData['teacher_id'] = $teacher_id;
        }

        $routine->update($updateData);

        if ($request->expectsJson() || $request->ajax()) {
            $routine->load(['course:id,course_name', 'teacher:id,first_name,last_name,name']);
            return response()->json([
                'success' => true,
                'message' => 'Timetable slot updated successfully.',
                'routine' => [
                    'id'           => $routine->id,
                    'weekday'      => (int) $routine->weekday,
                    'start'        => substr($routine->start, 0, 5),
                    'end'          => substr($routine->end, 0, 5),
                    'course_id'    => $routine->course_id,
                    'course_name'  => $routine->course?->course_name ?? 'Subject',
                    'teacher_id'   => $routine->teacher_id,
                    'teacher_name' => $routine->teacher?->full_name ?? ($routine->teacher?->name ?? 'Faculty'),
                    'room'         => $routine->room,
                    'color'        => $routine->color,
                ]
            ]);
        }

        return redirect()->route('section.routine.show', [
            'class_id'   => $routine->class_id,
            'section_id' => $routine->section_id,
        ])->with('status', 'Timetable slot updated successfully.');
    }

    /** Delete a slot */
    public function destroy(Routine $routine)
    {
        $routine->delete();
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Slot removed from timetable.']);
        }
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($slots);
        }

        $teacher = \App\Models\User::find($teacher_id) ?? auth()->user();
        return view('routines.teacher-timetable', compact('slots', 'teacher', 'session_id'));
    }

    /**
     * Store a timetable slot from the teacher profile page.
     * Includes the same conflict detection as store().
     */
    public function storeForTeacher(Request $request, int $teacherId)
    {
        $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'required|integer',
            'course_id'  => 'required|integer',
            'weekday'    => 'required|integer|min:0|max:6',
            'start'      => 'required|string',
            'end'        => 'required|string',
            'room'       => 'nullable|string|max:50',
        ]);

        $session_id = $this->getSchoolCurrentSession();
        $weekday    = $request->weekday;
        $start      = $request->start;
        $end        = $request->end;
        $section_id = $request->section_id;

        // Section conflict
        $sectionConflict = Routine::where('session_id', $session_id)
            ->where('section_id', $section_id)
            ->where('weekday', $weekday)
            ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
            ->first();

        if ($sectionConflict) {
            return back()->withErrors(['conflict' => "This section already has a class from {$sectionConflict->start} to {$sectionConflict->end} on that day."])->withInput();
        }

        // Teacher conflict
        $teacherConflict = Routine::where('session_id', $session_id)
            ->where('teacher_id', $teacherId)
            ->where('weekday', $weekday)
            ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
            ->first();

        if ($teacherConflict) {
            return back()->withErrors(['conflict' => "Teacher is already scheduled from {$teacherConflict->start} to {$teacherConflict->end} on that day."])->withInput();
        }

        Routine::create([
            'session_id' => $session_id,
            'teacher_id' => $teacherId,
            'class_id'   => $request->class_id,
            'section_id' => $request->section_id,
            'course_id'  => $request->course_id,
            'weekday'    => $weekday,
            'start'      => $start,
            'end'        => $end,
            'room'       => $request->room,
        ]);

        return back()->with('status', 'Timetable slot added successfully.');
    }

    /**
     * Delete a single timetable slot (from teacher profile page).
     */
    public function destroySlot(int $routineId)
    {
        $routine = Routine::findOrFail($routineId);
        $routine->delete();
        return back()->with('status', 'Timetable slot removed.');
    }

    /**
     * AJAX: return courses for a given class_id (for the timetable form dropdown).
     */
    public function coursesByClass(Request $request)
    {
        $classId    = $request->query('class_id');
        $sessionId  = $this->getSchoolCurrentSession();
        $courses    = Course::uniqueByName()->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->get(['id', 'course_name']);
        return response()->json($courses);
    }

    /**
     * AJAX: Get complete data for the Rapid Matrix Editor
     */
    public function matrixData(Request $request)
    {
        $classId   = (int) $request->query('class_id');
        $sectionId = (int) $request->query('section_id');
        $sessionId = $this->getSchoolCurrentSession();

        $user = auth()->user();
        $schoolId = $user?->school_id;

        // Fetch courses for this class
        $courses = Course::uniqueByName()->where('class_id', $classId)
            ->where(function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId)->orWhereNull('session_id');
            })
            ->get(['id', 'course_name', 'course_code', 'color_code']);

        // Fetch teachers
        $teachersQuery = User::where(function ($q) {
            $q->whereIn('role', ['teacher', 'class-teacher', 'admin', 'principal'])
              ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['teacher', 'class-teacher']));
        });
        if ($schoolId && !$user->hasRole('super-admin')) {
            $teachersQuery->where('school_id', $schoolId);
        }
        $teachers = $teachersQuery->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'name', 'email']);

        // Fetch AssignedTeacher mappings: course_id => teacher_id
        $teacherMappings = AssignedTeacher::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('teacher_id', 'course_id')
            ->toArray();

        // If no section-specific mapping, fallback to class-level mapping
        if (empty($teacherMappings)) {
            $teacherMappings = AssignedTeacher::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->pluck('teacher_id', 'course_id')
                ->toArray();
        }

        // Fetch existing routine slots for this section
        $existingSlots = Routine::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with(['course:id,course_name,course_code', 'teacher:id,first_name,last_name,name'])
            ->get();

        // Fetch busy teachers in other sections in the same session for live conflict prevention
        $otherBookings = Routine::where('session_id', $sessionId)
            ->where('section_id', '!=', $sectionId)
            ->whereNotNull('teacher_id')
            ->with(['schoolClass:id,class_name', 'section:id,section_name', 'teacher:id,first_name,last_name,name'])
            ->get(['id', 'weekday', 'start', 'end', 'teacher_id', 'class_id', 'section_id'])
            ->map(function ($r) {
                return [
                    'weekday'      => (int) $r->weekday,
                    'start'        => substr($r->start, 0, 5),
                    'end'          => substr($r->end, 0, 5),
                    'teacher_id'   => (int) $r->teacher_id,
                    'teacher_name' => $r->teacher?->full_name ?? $r->teacher?->name ?? 'Teacher',
                    'class_name'   => $r->schoolClass?->class_name ?? '',
                    'section_name' => $r->section?->section_name ?? '',
                ];
            });

        // Other sections available for cloning
        $classesWithSections = $this->schoolClassRepository->getAllBySession($sessionId);
        $cloneableSections = [];
        foreach ($classesWithSections as $cls) {
            foreach ($cls->sections as $sec) {
                if ($cls->id != $classId || $sec->id != $sectionId) {
                    $slotCount = Routine::where('session_id', $sessionId)
                        ->where('class_id', $cls->id)
                        ->where('section_id', $sec->id)
                        ->count();
                    $cloneableSections[] = [
                        'class_id'     => $cls->id,
                        'class_name'   => $cls->class_name,
                        'section_id'   => $sec->id,
                        'section_name' => $sec->section_name,
                        'label'        => "{$cls->class_name} - {$sec->section_name} ({$slotCount} slots)",
                        'slot_count'   => $slotCount,
                    ];
                }
            }
        }

        return response()->json([
            'success'            => true,
            'class_id'           => $classId,
            'section_id'         => $sectionId,
            'courses'            => $courses,
            'teachers'           => $teachers,
            'teacher_mappings'   => $teacherMappings,
            'existing_slots'     => $existingSlots,
            'other_bookings'     => $otherBookings,
            'cloneable_sections' => $cloneableSections,
        ]);
    }

    /**
     * AJAX: Bulk save entire weekly timetable matrix in 1 fast transaction
     */
    public function matrixSave(Request $request)
    {
        $classId   = (int) $request->input('class_id');
        $sectionId = (int) $request->input('section_id');
        $slots     = $request->input('slots', []);
        $sessionId = $this->getSchoolCurrentSession();
        $schoolId  = auth()->user()?->school_id;

        if (!$classId || !$sectionId) {
            return response()->json(['success' => false, 'message' => 'Class and Section are required.'], 422);
        }

        // Conflict checking across other sections
        $conflicts = [];
        $conflictingSlots = [];
        foreach ($slots as $slot) {
            $teacherId = $slot['teacher_id'] ?? null;
            if (!$teacherId || empty($slot['course_id'])) {
                continue;
            }

            $w = (int) $slot['weekday'];
            $s = substr($slot['start'], 0, 5);
            $e = substr($slot['end'], 0, 5);

            $clash = Routine::where('session_id', $sessionId)
                ->where('section_id', '!=', $sectionId)
                ->where('teacher_id', $teacherId)
                ->where('weekday', $w)
                ->where(function ($q) use ($s, $e) {
                    $q->where('start', '<', $e)->where('end', '>', $s);
                })
                ->with(['schoolClass', 'section', 'teacher'])
                ->first();

            if ($clash) {
                $tName = $clash->teacher?->full_name ?? $clash->teacher?->name ?? 'Teacher';
                $cName = $clash->schoolClass?->class_name ?? 'Class';
                $secName = $clash->section?->section_name ?? 'Section';
                $msg = "Conflict: {$tName} is already scheduled in {$cName} ({$secName}) at {$s} - {$e} on Day {$w}.";
                $conflicts[] = $msg;
                $conflictingSlots[] = [
                    'weekday'    => $w,
                    'start'      => $s,
                    'end'        => $e,
                    'teacher_id' => $teacherId,
                    'message'    => $msg
                ];
            }
        }

        $forceOverride = $request->boolean('force_override');
        if (!empty($conflicts) && !$forceOverride) {
            return response()->json([
                'success'           => false,
                'conflict'          => true,
                'can_override'      => true,
                'message'           => implode("<br>", array_unique($conflicts)),
                'conflicts'         => array_unique($conflicts),
                'conflicting_slots' => $conflictingSlots,
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Delete current routines for this section in current session
            Routine::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->delete();

            $insertCount = 0;
            $palette = ['#4f46e5', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0284c7', '#db2777', '#0d9488'];

            foreach ($slots as $idx => $slot) {
                if (empty($slot['course_id'])) {
                    continue;
                }

                $cId = (int) $slot['course_id'];
                $color = !empty($slot['color']) ? $slot['color'] : $palette[$cId % count($palette)];

                Routine::create([
                    'school_id'  => $schoolId,
                    'session_id' => $sessionId,
                    'class_id'   => $classId,
                    'section_id' => $sectionId,
                    'course_id'  => $cId,
                    'teacher_id' => !empty($slot['teacher_id']) ? (int) $slot['teacher_id'] : null,
                    'weekday'    => (int) $slot['weekday'],
                    'start'      => substr($slot['start'], 0, 5),
                    'end'        => substr($slot['end'], 0, 5),
                    'room'       => $slot['room'] ?? null,
                    'color'      => $color,
                ]);
                $insertCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Timetable saved successfully! {$insertCount} periods active.",
                'count'   => $insertCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving timetable: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Clone timetable slots from one section to another
     */
    public function cloneSection(Request $request)
    {
        $sourceClassId   = (int) $request->input('source_class_id');
        $sourceSectionId = (int) $request->input('source_section_id');
        $targetClassId   = (int) $request->input('target_class_id');
        $targetSectionId = (int) $request->input('target_section_id');
        $sessionId       = $this->getSchoolCurrentSession();

        if (!$sourceClassId || !$sourceSectionId || !$targetClassId || !$targetSectionId) {
            return response()->json(['success' => false, 'message' => 'Source and target sections are required.'], 422);
        }

        $sourceRoutines = Routine::where('session_id', $sessionId)
            ->where('class_id', $sourceClassId)
            ->where('section_id', $sourceSectionId)
            ->with(['course:id,course_name', 'teacher:id,first_name,last_name,name'])
            ->get();

        if ($sourceRoutines->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Source section has no timetable slots to clone.'], 404);
        }

        $targetCourses = Course::uniqueByName()->where('class_id', $targetClassId)->orderBy('course_name')->get(['id', 'course_name']);
        $targetTeacherMappings = AssignedTeacher::where('session_id', $sessionId)
            ->where('class_id', $targetClassId)
            ->where('section_id', $targetSectionId)
            ->pluck('teacher_id', 'course_id')
            ->toArray();

        $clonedSlots = [];
        foreach ($sourceRoutines as $sr) {
            $targetCourseId = null;
            if ($sourceClassId == $targetClassId) {
                $targetCourseId = $sr->course_id;
            } else {
                $match = $targetCourses->first(function ($c) use ($sr) {
                    return strcasecmp(trim($c->course_name), trim($sr->course?->course_name ?? '')) === 0;
                });
                $targetCourseId = $match ? $match->id : null;
            }

            $targetTeacherId = $targetCourseId && isset($targetTeacherMappings[$targetCourseId])
                ? $targetTeacherMappings[$targetCourseId]
                : $sr->teacher_id;

            $clonedSlots[] = [
                'weekday'    => (int) $sr->weekday,
                'start'      => substr($sr->start, 0, 5),
                'end'        => substr($sr->end, 0, 5),
                'course_id'  => $targetCourseId,
                'teacher_id' => $targetTeacherId,
                'room'       => $sr->room,
                'color'      => $sr->color,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Cloned ' . count($clonedSlots) . ' slots successfully.',
            'slots'   => $clonedSlots,
        ]);
    }
}

