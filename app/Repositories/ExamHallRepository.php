<?php

namespace App\Repositories;

use App\Models\ExamHall;
use App\Models\ExamSeatAllocation;
use Illuminate\Database\Eloquent\Collection;

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
     * Auto-allocate seats for all students in a schedule.
     * Generates seat numbers like "A-01", "A-02", … up to hall capacity.
     *
     * @param  int    $scheduleId
     * @param  array  $studentIds   Ordered list of student IDs to seat
     * @param  string $prefix       Seat label prefix (default "A")
     * @return int    Number of seats allocated
     */
    public function autoAllocateSeats(int $scheduleId, array $studentIds, string $prefix = 'A'): int
    {
        $count = 0;
        foreach ($studentIds as $i => $studentId) {
            $seatNumber = $prefix . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);

            ExamSeatAllocation::updateOrCreate(
                ['schedule_id' => $scheduleId, 'student_id' => $studentId],
                ['seat_number' => $seatNumber]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Return seat allocations for a schedule, ordered by seat_number.
     */
    public function getAllocations(int $scheduleId): Collection
    {
        return ExamSeatAllocation::with('student')
            ->where('schedule_id', $scheduleId)
            ->orderBy('seat_number')
            ->get();
    }

    public function clearAllocations(int $scheduleId): void
    {
        ExamSeatAllocation::where('schedule_id', $scheduleId)->delete();
    }
}
