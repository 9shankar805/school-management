<?php

namespace App\Repositories;

use App\Models\ExamHall;
use App\Models\ExamSeatAllocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExamHallRepository
{
    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function getAll(int $sessionId): Collection
    {
        return ExamHall::where('session_id', $sessionId)
            ->orderBy('hall_name')
            ->get();
    }

    public function getActive(int $sessionId): Collection
    {
        return ExamHall::where('session_id', $sessionId)
            ->where('is_active', true)
            ->orderBy('hall_name')
            ->get();
    }

    public function findById(int $id): ExamHall
    {
        return ExamHall::findOrFail($id);
    }

    public function create(array $data): ExamHall
    {
        return ExamHall::create($data);
    }

    public function update(int $id, array $data): ExamHall
    {
        $hall = ExamHall::findOrFail($id);
        $hall->update($data);
        return $hall->fresh();
    }

    public function delete(int $id): void
    {
        ExamHall::findOrFail($id)->delete();
    }

    // ── Seat allocation helpers ───────────────────────────────────────────────

    /**
     * Auto-allocate seats for all students in a schedule into Room Columns, Benches & Bench Positions.
     * Bench capacity can be set to 1, 2, or 3 students per bench.
     *
     * @param  int    $scheduleId
     * @param  array  $studentIds        Ordered list of student IDs to seat
     * @param  int    $numCols           Number of columns in the hall
     * @param  int    $benchesPerCol     Number of benches per column
     * @param  int    $studentsPerBench  Students per bench (1, 2, or 3)
     * @param  string $strategy          Placement strategy ('alphabetical', 'roll_number', 'staggered')
     * @return int    Number of seats allocated
     */
    public function autoAllocateSeats(
        int $scheduleId,
        array $studentIds,
        int $numCols = 4,
        int $benchesPerCol = 5,
        int $studentsPerBench = 2,
        string $strategy = 'alphabetical'
    ): int {
        $this->clearAllocations($scheduleId);
        $count = 0;

        $studentsPerBench = max(1, min(3, $studentsPerBench));
        $benchesPerCol    = max(1, $benchesPerCol);
        $numCols          = max(1, $numCols);

        foreach ($studentIds as $i => $studentId) {
            // For staggered: place students in column-skip order (col1-b1, col2-b1, col3-b1, col4-b1, col1-b2...)
            // to ensure adjacent benches in the same column don't have same-class students
            if ($strategy === 'staggered') {
                $totalBenches = $numCols * $benchesPerCol;
                $benchGlobal  = ($i % $totalBenches);
                $colNo        = ($benchGlobal % $numCols) + 1;
                $benchNo      = intdiv($benchGlobal, $numCols) + 1;
                $pos          = intdiv($i, $totalBenches) + 1;
                if ($pos > $studentsPerBench) $pos = $studentsPerBench; // clamp
            } else {
                $benchIndex = intdiv($i, $studentsPerBench);
                $pos        = ($i % $studentsPerBench) + 1;
                $colNo      = intdiv($benchIndex, $benchesPerCol) + 1;
                $benchNo    = ($benchIndex % $benchesPerCol) + 1;
            }

            $seatNumber = "C{$colNo}-B{$benchNo}-P{$pos}";

            ExamSeatAllocation::create([
                'schedule_id'    => $scheduleId,
                'student_id'     => $studentId,
                'seat_number'    => $seatNumber,
                'column_no'      => $colNo,
                'bench_no'       => $benchNo,
                'bench_position' => $pos,
            ]);
            $count++;
        }
        return $count;
    }

    /**
     * Return seat allocations for a schedule, ordered by seat_number.
     */
    public function getAllocations(int $scheduleId): Collection
    {
        return ExamSeatAllocation::with([
                'student',
                'student.promotions.schoolClass',
            ])
            ->where('schedule_id', $scheduleId)
            ->orderByRaw("COALESCE(column_no,1) ASC, COALESCE(bench_no,1) ASC, COALESCE(bench_position,1) ASC")
            ->get();
    }

    public function clearAllocations(int $scheduleId): void
    {
        // Hard-delete all rows (including any soft-deleted ones) for this schedule
        // so the unique constraints on (schedule_id, student_id) don't block re-allocation
        \DB::table('exam_seat_allocations')->where('schedule_id', $scheduleId)->delete();
    }
}
