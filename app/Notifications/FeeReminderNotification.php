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
        $channels = ['database'];

        // Only add mail channel if SMTP credentials are configured
        // to prevent queue job failures when MAIL_PASSWORD is empty
        if ($notifiable->email && config('mail.mailers.smtp.password')) {
            $channels[] = 'mail';
        }

        return $channels;
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
