<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Notifications;

use App\Domains\Leads\Models\Lead;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeadStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $queue = 'notifications';

    public function __construct(
        public readonly Lead $lead,
        public readonly ?string $fromStatus,
        public readonly string $toStatus,
    ) {}

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => 'lead_status_changed',
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'message' => "Lead {$this->lead->name} mudou para: {$this->toStatus}",
        ];
    }
}
