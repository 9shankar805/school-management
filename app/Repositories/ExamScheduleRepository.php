<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ExamScheduleRepository
{
    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function create(array $data): ExamSchedule
    {
        return ExamSchedule::create($data);
    }

    public function update(int $id, array $data): ExamSchedule
    {
        $schedule = ExamSchedule::findOrFail($id);
        $schedule->update($data);
        return $schedule->fresh();
    }

    public function delete(int $id): void
    {
        ExamSchedule::findOrFail($id)->delete();
    }

    public function findById(int $id): ExamSchedule
    {
        return ExamSchedule::with(['exam.course', 'hall', 'invigilator', 'seatAllocations'])
            ->findOrFail($id);
    }

    // ── Queries ───────────────────────────────────────────────────────────────

    /**
     * Full timetable for a session, optionally filtered by class or semester.
     * Ordered by exam_date ASC, start_time ASC.
     */
    public function getTimetable(int $sessionId, int $classId = 0, int $semesterId = 0): Collection
    {
        return ExamSchedule::with(['exam.course', 'exam.schoolClass', 'exam.semester', 'hall', 'invigilator'])
            ->where('exam_schedules.session_id', $sessionId)
            ->when($classId || $semesterId, function ($q) use ($classId, $semesterId) {
                $q->whereHas('exam', function ($eq) use ($classId, $semesterId) {
                    if ($classId)    $eq->where('class_id',    $classId);
                    if ($semesterId) $eq->where('semester_id', $semesterId);
                });
            })
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Upcoming schedules within the next N days.
     */
    public function getUpcoming(int $sessionId, int $days = 7): Collection
    {
        return ExamSchedule::with(['exam.course', 'exam.schoolClass', 'hall'])
            ->where('session_id', $sessionId)
            ->whereBetween('exam_date', [today(), today()->addDays($days)])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * All schedules for a specific exam.
     */
    public function getByExam(int $examId): Collection
    {
        return ExamSchedule::with(['hall', 'invigilator', 'seatAllocations.student'])
            ->where('exam_id', $examId)
            ->orderBy('exam_date')
            ->get();
    }

    /**
     * Check for hall/invigilator conflicts on a given date+time window.
     * Returns true if a conflict exists (excluding $excludeId if editing).
     */
    public function hasConflict(
        int     $hallId,
        string  $date,
        string  $startTime,
        string  $endTime,
        ?int    $excludeId = null
    ): bool {
        $query = ExamSchedule::where('hall_id', $hallId)
            ->whereDate('exam_date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                // Overlap condition: existing.start < new.end AND existing.end > new.start
                $q->where('start_time', '<', $endTime)
                  ->where('end_time',   '>',  $startTime);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
