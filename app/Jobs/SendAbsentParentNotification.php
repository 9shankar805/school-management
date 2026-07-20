<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\StudentParentInfo;
use App\Models\User;
use App\Notifications\AttendanceAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendAbsentParentNotification
 * ─────────────────────────────────────────────────────────────────────────────
 * Dispatched after attendance is saved (or by the scheduler after school
 * hours) to notify parents of absent students via database + mail.
 *
 * Usage:
 *   SendAbsentParentNotification::dispatch($attendance);
 *   // or bulk:
 *   SendAbsentParentNotification::dispatchForDate($sessionId, $date);
 */
class SendAbsentParentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Max queue attempts before the job is failed. */
    public int $tries = 3;

    /** Wait 60 s before retrying after a failure. */
    public int $backoff = 60;

    public function __construct(
        public readonly int    $studentId,
        public readonly string $studentName,
        public readonly string $date,
        public readonly string $courseName,
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Handle
    // ──────────────────────────────────────────────────────────────────────────

    public function handle(): void
    {
        // Resolve the parent record
        $parentInfo = StudentParentInfo::where('student_id', $this->studentId)->first();

        if (! $parentInfo) {
            Log::info("SendAbsentParentNotification: no parent info for student {$this->studentId} — skipped.");
            return;
        }

        // Find the parent User account (if they have one)
        $parent = User::where('email', $parentInfo->father_email)
            ->orWhere('email', $parentInfo->mother_email)
            ->first();

        if ($parent) {
            $parent->notify(new AttendanceAlertNotification(
                studentName: $this->studentName,
                date:        $this->date,
                courseName:  $this->courseName,
            ));
        } else {
            // No User account — just log for now; SMS gateway can hook in here
            Log::info("SendAbsentParentNotification: parent has no user account for student {$this->studentId}. Email(s): {$parentInfo->father_email} / {$parentInfo->mother_email}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Static helper: dispatch for all absent students on a given date
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Dispatch one job per absent student for the given session + date.
     * Call this from the console command or after bulk attendance save.
     */
    public static function dispatchForDate(int $sessionId, string $date): int
    {
        $absentRecords = Attendance::with(['student', 'course'])
            ->where('session_id', $sessionId)
            ->where('status', 'off')
            ->whereDate('date', $date)
            ->get();

        // Deduplicate per student (a student could be absent in multiple courses)
        $dispatched = 0;
        $seen       = [];

        foreach ($absentRecords as $record) {
            $key = "{$record->student_id}:{$date}";
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $courseName = $record->course?->name ?? 'School';
            $student    = $record->student;

            if (! $student) continue;

            static::dispatch(
                studentId:   $student->id,
                studentName: $student->full_name,
                date:        $date,
                courseName:  $courseName,
            );

            $dispatched++;
        }

        return $dispatched;
    }
}
