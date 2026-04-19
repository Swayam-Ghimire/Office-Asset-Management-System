<?php

namespace App\Notifications\Admin;

use App\Models\AssetAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnedAssetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AssetAssignment $assignment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->assignment->user->name
                .' has returned '
                .$this->assignment->asset->model_name.'.',
            'type' => 'asset_returned',
            'asset_id' => $this->assignment->asset_id,
            'asset_name' => $this->assignment->asset->model_name,
            'user_name' => $this->assignment->user->name,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $asset = $this->assignment->asset;
        $user = $this->assignment->user;

        return (new MailMessage)
            ->subject($user->name.' Returned: '.$asset->model_name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($user->name.' has returned **'.$asset->model_name.'**.')
            ->line('The asset is now available in the inventory.')
            ->action('View Asset', route('assets.show', $asset->id))
            ->line('No action is required unless there is an open maintenance report for this asset.');
    }
}
