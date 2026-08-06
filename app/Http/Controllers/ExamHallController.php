<?php

namespace App\Http\Controllers;

use App\Models\ExamSeatAllocation;
use App\Repositories\ExamHallRepository;
use App\Repositories\ExamScheduleRepository;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;

class ExamHallController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->middleware(['auth', 'can:view exams']);
    }

    // ── Hall CRUD ─────────────────────────────────────────────────────────────

    public function index()
    {
        $sessionId = $this->getSchoolCurrentSession();
        $repo      = new ExamHallRepository();
        $halls     = $repo->getAll($sessionId);

        $schedules = \App\Models\ExamSchedule::with(['exam', 'hall', 'exam.schoolClass'])
            ->where('session_id', $sessionId)
            ->where('school_id', auth()->user()->school_id)
            ->get();

        return view('exams.halls.index', compact('halls', 'schedules', 'sessionId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'hall_name' => 'required|string|max:100',
            'building'  => 'nullable|string|max:100',
            'floor'     => 'nullable|string|max:50',
            'capacity'  => 'required|integer|min:1|max:1000',
            'notes'     => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $repo      = new ExamHallRepository();
        $repo->create(array_merge($data, ['session_id' => $sessionId, 'is_active' => $request->boolean('is_active', true)]));

        return back()->with('status', 'Hall "' . $data['hall_name'] . '" created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'hall_name' => 'required|string|max:100',
            'building'  => 'nullable|string|max:100',
            'floor'     => 'nullable|string|max:50',
            'capacity'  => 'required|integer|min:1|max:1000',
            'notes'     => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        (new ExamHallRepository())->update($id, array_merge($data, ['is_active' => $request->boolean('is_active', true)]));

        return back()->with('status', 'Hall updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create exams');
        (new ExamHallRepository())->delete($id);
        return back()->with('status', 'Hall deleted.');
    }

    // ── Seat allocation ───────────────────────────────────────────────────────

    /**
     * GET /exam/halls/{scheduleId}/seats
     * Show seat allocation for a schedule.
     */
    public function seats(int $scheduleId)
    {
        $this->authorize('view exams');

        $scheduleRepo = new ExamScheduleRepository();
        $hallRepo     = new ExamHallRepository();

        $schedule    = $scheduleRepo->findById($scheduleId);
        $allocations = $hallRepo->getAllocations($scheduleId);

        // Students enrolled in the exam's class (for manual override form)
        $students = \App\Models\Promotion::with(['student', 'schoolClass'])
            ->where('session_id', $schedule->session_id)
            ->where('class_id',   $schedule->exam?->class_id ?? 0)
            ->orderBy('id_card_number')
            ->get();

        // All classes available for multi-class selection
        $allClasses = \App\Models\SchoolClass::where('session_id', $schedule->session_id)
            ->orderBy('class_name')
            ->get();

        return view('exams.halls.seats', compact('schedule', 'allocations', 'students', 'allClasses'));
    }

    /**
     * POST /exam/halls/{scheduleId}/seats/auto
     * Auto-allocate seats into Columns, Benches & Bench Positions (1, 2, or 3 students per bench).
     */
    public function autoAllocate(Request $request, int $scheduleId)
    {
        $this->authorize('create exams');

        $scheduleRepo = new ExamScheduleRepository();
        $hallRepo     = new ExamHallRepository();

        $schedule = $scheduleRepo->findById($scheduleId);

        $numCols          = max(1, (int) $request->input('columns', 4));
        $benchesPerCol    = max(1, (int) $request->input('benches_per_column', 5));
        $studentsPerBench = max(1, min(3, (int) $request->input('students_per_bench', 2)));
        $strategy         = $request->input('strategy', 'alphabetical');
        $selectedClassIds = array_filter((array) $request->input('class_ids', []));

        // Update hall bench settings if hall exists
        if ($schedule->hall) {
            $schedule->hall->update([
                'columns'            => $numCols,
                'benches_per_column' => $benchesPerCol,
                'students_per_bench' => $studentsPerBench,
            ]);
        }

        $query = \App\Models\Promotion::with(['student'])
            ->where('session_id', $schedule->session_id);

        if (!empty($selectedClassIds)) {
            $query->whereIn('class_id', $selectedClassIds);
        } else {
            $query->where('class_id', $schedule->exam?->class_id ?? 0);
        }

        $promotionsQuery = $query->get();

        if ($strategy === 'roll_number') {
            $studentIds = $promotionsQuery
                ->sortBy(fn($p) => $p->id_card_number ?? 'zzz')
                ->pluck('student_id')
                ->filter()
                ->values()
                ->toArray();
        } else {
            $studentIds = $promotionsQuery
                ->sortBy(fn($p) => $p->student?->first_name ?? 'zzz')
                ->pluck('student_id')
                ->filter()
                ->values()
                ->toArray();
        }

        $count = $hallRepo->autoAllocateSeats($scheduleId, $studentIds, $numCols, $benchesPerCol, $studentsPerBench, $strategy);

        return back()->with('status', "{$count} seat(s) allocated automatically on Benches ({$studentsPerBench} Students/Bench, {$numCols} Cols x {$benchesPerCol} Benches).");
    }

    /**
     * GET /exam/halls/{scheduleId}/seats/print
     * Printable seating arrangement notice sheet for hall doors and invigilators.
     */
    public function printSeats(int $scheduleId)
    {
        $this->authorize('view exams');

        $scheduleRepo = new ExamScheduleRepository();
        $hallRepo     = new ExamHallRepository();

        $schedule    = $scheduleRepo->findById($scheduleId);
        $allocations = $hallRepo->getAllocations($scheduleId);

        return view('exams.halls.print-seats', compact('schedule', 'allocations'));
    }

    /**
     * POST /exam/halls/{scheduleId}/seats/manual
     * Save manually entered seat numbers.
     */
    public function saveSeats(Request $request, int $scheduleId)
    {
        $this->authorize('create exams');

        $request->validate([
            'seats'              => 'required|array',
            'seats.*.student_id' => 'required|integer|exists:users,id',
            'seats.*.seat_number'=> 'nullable|string|max:20',
        ]);

        foreach ($request->seats as $row) {
            if (!empty($row['seat_number'])) {
                // Hard-delete any existing (including soft-deleted) row first to avoid unique constraint issues
                \DB::table('exam_seat_allocations')
                    ->where('schedule_id', $scheduleId)
                    ->where('student_id', $row['student_id'])
                    ->delete();

                ExamSeatAllocation::create([
                    'schedule_id' => $scheduleId,
                    'student_id'  => $row['student_id'],
                    'seat_number' => $row['seat_number'],
                ]);
            }
        }

        return back()->with('status', 'Seat allocations saved successfully.');
    }

    /**
     * DELETE /exam/halls/{scheduleId}/seats
     * Clear all seat allocations for a schedule.
     */
    public function clearSeats(int $scheduleId)
    {
        $this->authorize('create exams');
        (new ExamHallRepository())->clearAllocations($scheduleId);
        return back()->with('status', 'All seat allocations cleared.');
    }
}
