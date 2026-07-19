<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * General-purpose notification that supports:
 *  - Database channel (in-app notification center)
 *  - Mail channel (email)
 *
 * Usage:
 *   $user->notify(new GeneralNotification(
 *       title:    'Assignment Due',
 *       message:  'Your assignment is due tomorrow.',
 *       type:     'assignment',
 *       actionUrl: '/assignments/5',
 *   ));
 */
class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string  $title,
        public readonly string  $message,
        public readonly string  $type = 'general',   // general|assignment|fee|attendance|exam|alert
        public readonly ?string $actionUrl = null,
        public readonly ?string $actionText = null,
        public readonly bool    $sendEmail = false,
    ) {}

    /**
     * Channels to deliver this notification through.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendEmail && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Database / in-app notification payload.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'action_url'  => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
    }

    /**
     * Mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . ($notifiable->first_name ?? 'there') . ',')
            ->line($this->message);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, url($this->actionUrl));
        }

        return $mail->line('Thank you for using ' . config('app.name') . '.');
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
