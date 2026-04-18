<?php

namespace App\Notifications\Employee;

use App\Models\AssetMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AssetMaintenance $maintenance,
        public ?string $reason = null,
    ) {}

    /**
     * Both DB (bell icon) and email.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Stored in the notifications table — read by NotificationBell.vue via notif.data.message
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Admin has requested you return: '.$this->maintenance->asset->model_name.'.',
            'asset_id' => $this->maintenance->asset_id,
            'asset_name' => $this->maintenance->asset->model_name,
            'maintenance_id' => $this->maintenance->id,
            'reason' => $this->reason,
            'type' => 'return_requested',
        ];
    }

    /**
     * Email sent to the employee.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $asset = $this->maintenance->asset;
        $mail = (new MailMessage)
            ->subject('Action Required: Please Return '.$asset->model_name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('The admin team has requested that you return the following asset:')
            ->line('**Asset:** '.$asset->model_name.($asset->brand ? ' ('.$asset->brand.')' : ''))
            ->line('**Reason for maintenance:** '.$this->maintenance->description);

        if ($this->reason) {
            $mail->line('**Admin note:** '.$this->reason);
        }

        $mail
            ->line('Please return the asset as soon as possible so it can be serviced.')
            ->action('View My Assignments', route('asset-assignments.index'))
            ->line('Thank you for your cooperation.');

        return $mail;
    }
}
