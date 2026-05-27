<?php

declare(strict_types=1);

namespace App\Domains\Leads\Listeners;

use App\Domains\Leads\Events\LeadCreated;
use App\Domains\Notifications\Notifications\LeadAssignedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendLeadAssignedNotification implements ShouldQueue
{
    use Queueable;

    public function handle(LeadCreated $event): void
    {
        $lead = $event->lead;

        if ($lead->owner_id === null) {
            return;
        }

        $lead->owner?->notify(new LeadAssignedNotification($lead));
    }
}
