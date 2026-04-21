<?php

namespace App\Notifications\Employee;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $credentials)
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
        $url = url(route('login'));
        return (new MailMessage)
            ->subject('Account Credentials')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been created. Here are your credentials:')
            ->line('Email: ' . $this->credentials['email'])
            ->line('Password: ' . $this->credentials['password'])
            ->line('Department: ' . $notifiable->department->name)
            ->line('Please login into your account using the button below')
            ->action('Login', $url)
            ->line('Hope you have a great experience using our asset management system!');
    }
}
