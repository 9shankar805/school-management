<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * AttendanceShortageAlert
 * ─────────────────────────────────────────────────────────────────────────────
 * Sent to a student (and optionally their parent) when their attendance
 * percentage drops below the configured threshold (default 75%).
 *
 * Also sent to the attendance officer / admin as a summary digest when the
 * daily shortage check command runs.
 *
 * Usage (single student warning):
 *   $student->notify(new AttendanceShortageAlert(
 *       studentName: 'John Doe',
 *       percentage:  68.5,
 *       threshold:   75.0,
 *       className:   'Class 10',
 *   ));
 *
 * Usage (admin digest):
 *   $admin->notify(new AttendanceShortageAlert(
 *       studentName: null,          // null = digest mode
 *       percentage:  null,
 *       threshold:   75.0,
 *       className:   null,
 *       digestCount: 12,            // number of students below threshold
 *   ));
 */
class AttendanceShortageAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?string $studentName  = null,
        public readonly ?float  $percentage   = null,
        public readonly float   $threshold    = 75.0,
        public readonly ?string $className    = null,
        public readonly ?int    $digestCount  = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    // ── In-app (database) ────────────────────────────────────────────────────

    public function toDatabase(object $notifiable): array
    {
        if ($this->digestCount !== null) {
            // Admin digest
            return [
                'title'   => 'Attendance Shortage Alert',
                'message' => "{$this->digestCount} student(s) are below the {$this->threshold}% attendance threshold.",
                'type'    => 'attendance_shortage',
            ];
        }

        // Per-student warning
        return [
            'title'   => 'Low Attendance Warning',
            'message' => "{$this->studentName} ({$this->className}) has only {$this->percentage}% attendance — below the required {$this->threshold}%.",
            'type'    => 'attendance_shortage',
        ];
    }

    // ── Mail ─────────────────────────────────────────────────────────────────

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->digestCount !== null) {
            return (new MailMessage)
                ->subject('Attendance Shortage Report — ' . config('app.name'))
                ->greeting('Hello ' . ($notifiable->first_name ?? 'Admin') . ',')
                ->line("{$this->digestCount} student(s) currently have attendance below {$this->threshold}%.")
                ->action('View Shortage Report', route('attendance.shortage'))
                ->line('Please follow up with the students and their parents.');
        }

        return (new MailMessage)
            ->subject('Low Attendance Warning — ' . config('app.name'))
            ->greeting('Dear ' . ($notifiable->first_name ?? 'Parent/Guardian') . ',')
            ->line("This is to inform you that **{$this->studentName}** ({$this->className}) currently has an attendance of **{$this->percentage}%**, which is below the minimum required attendance of **{$this->threshold}%**.")
            ->action('View Attendance', url('/students/view/attendance/' . ($notifiable->id ?? '')))
            ->line('Please ensure regular attendance to avoid academic penalties.');
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
