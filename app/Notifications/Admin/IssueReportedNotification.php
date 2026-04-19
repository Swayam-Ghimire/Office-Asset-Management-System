<?php

namespace App\Notifications\Admin;

use App\Models\AssetMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AssetMaintenance $maintenance,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * DB record — read by NotificationBell.vue and Notifications/Index.vue
     * via notif.data.message and notif.data.type.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message'        => $this->maintenance->reporter->name
                . ' reported an issue with '
                . $this->maintenance->asset->model_name . '.',
            'type'           => 'issue_reported',
            'asset_id'       => $this->maintenance->asset_id,
            'asset_name'     => $this->maintenance->asset->model_name,
            'maintenance_id' => $this->maintenance->id,
            'reporter_name'  => $this->maintenance->reporter->name,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $asset    = $this->maintenance->asset;
        $reporter = $this->maintenance->reporter;

        return (new MailMessage)
            ->subject('Issue Reported: ' . $asset->model_name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($reporter->name . ' has reported an issue with **' . $asset->model_name . '**.')
            ->line('**Description:** ' . $this->maintenance->description)
            ->line('The asset has been flagged as under maintenance.')
            ->action('View Maintenance Record', route('maintenance.index'))
            ->line('Please review and take appropriate action.');
    }
}