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

        return view('exams.halls.index', compact('halls', 'sessionId'));
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

        // Students enrolled in the exam's class (for allocation form)
        $students = \App\Models\Promotion::with('student')
            ->where('session_id', $schedule->session_id)
            ->where('class_id',   $schedule->exam->class_id)
            ->get();

        return view('exams.halls.seats', compact('schedule', 'allocations', 'students'));
    }

    /**
     * POST /exam/halls/{scheduleId}/seats/auto
     * Auto-allocate seats alphabetically by student name.
     */
    public function autoAllocate(Request $request, int $scheduleId)
    {
        $this->authorize('create exams');

        $scheduleRepo = new ExamScheduleRepository();
        $hallRepo     = new ExamHallRepository();

        $schedule   = $scheduleRepo->findById($scheduleId);
        $prefix     = $request->input('prefix', 'A');

        $students = \App\Models\Promotion::with('student')
            ->where('session_id', $schedule->session_id)
            ->where('class_id',   $schedule->exam->class_id)
            ->get()
            ->sortBy('student.first_name')
            ->pluck('student_id')
            ->toArray();

        $count = $hallRepo->autoAllocateSeats($scheduleId, $students, $prefix);

        return back()->with('status', "{$count} seat(s) allocated automatically.");
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
            'seats.*.seat_number'=> 'required|string|max:20',
        ]);

        foreach ($request->seats as $row) {
            ExamSeatAllocation::updateOrCreate(
                ['schedule_id' => $scheduleId, 'student_id' => $row['student_id']],
                ['seat_number' => $row['seat_number']]
            );
        }

        return back()->with('status', 'Seat allocations saved.');
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
