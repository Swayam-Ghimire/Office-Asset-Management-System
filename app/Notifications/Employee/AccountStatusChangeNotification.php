<?php

namespace App\Notifications\Employee;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountStatusChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public int $status)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isActive = $this->status === 1;
        return (new MailMessage)
            ->subject('Account Status Update Vayo')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account status has been updated.')
            ->line(
            $isActive
                ? 'Your account is now active. You can login now: ' . url('/login')
                : 'Your account has been suspended.'
        );
    }
}
