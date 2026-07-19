<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $studentName,
        public readonly string $date,
        public readonly string $courseName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Attendance Alert',
            'message' => "{$this->studentName} was marked absent in {$this->courseName} on {$this->date}.",
            'type'    => 'attendance',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Attendance Alert — ' . config('app.name'))
            ->greeting('Dear Parent/Guardian,')
            ->line("{$this->studentName} was marked **absent** in **{$this->courseName}** on **{$this->date}**.")
            ->action('View Attendance', url('/students/view/attendance/' . $notifiable->id))
            ->line('Please contact the school if this is an error.');
    }
}
