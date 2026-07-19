<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $invoiceNumber,
        public readonly string $amount,
        public readonly string $dueDate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'       => 'Fee Payment Reminder',
            'message'     => "Invoice #{$this->invoiceNumber} of {$this->amount} is due on {$this->dueDate}.",
            'type'        => 'fee',
            'action_url'  => '/payments',
            'action_text' => 'Pay Now',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fee Payment Reminder — ' . config('app.name'))
            ->greeting('Dear ' . ($notifiable->first_name ?? 'Student') . ',')
            ->line("This is a reminder that invoice **#{$this->invoiceNumber}** of **{$this->amount}** is due on **{$this->dueDate}**.")
            ->action('Pay Now', url('/payments'))
            ->line('Please make the payment before the due date to avoid late fees.');
    }
}
