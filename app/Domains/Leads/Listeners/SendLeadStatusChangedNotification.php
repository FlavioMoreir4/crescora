<?php

declare(strict_types=1);

namespace App\Domains\Leads\Listeners;

use App\Domains\Leads\Events\LeadStatusChanged;
use App\Domains\Notifications\Notifications\LeadStatusChangedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendLeadStatusChangedNotification implements ShouldQueue
{
    use Queueable;

    public function handle(LeadStatusChanged $event): void
    {
        $lead = $event->lead;

        if ($lead->owner_id === null) {
            return;
        }

        $lead->owner->notify(new LeadStatusChangedNotification(
            lead: $lead,
            fromStatus: $event->from?->value,
            toStatus: $event->to->value,
        ));
    }
}
