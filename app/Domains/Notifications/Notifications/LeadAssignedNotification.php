<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Notifications;

use App\Domains\Leads\Models\Lead;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $queue = 'notifications';

    public function __construct(
        public readonly Lead $lead,
    ) {}

    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => 'lead_assigned',
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'unit_name' => $this->lead->unit?->name,
            'message' => "Novo lead atribuído: {$this->lead->name}",
        ];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo lead atribuído')
            ->line("Um novo lead foi atribuído a você: {$this->lead->name}")
            ->action('Ver Lead', route('leads.show', $this->lead));
    }
}
