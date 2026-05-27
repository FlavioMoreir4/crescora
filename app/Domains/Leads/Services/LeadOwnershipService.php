<?php

declare(strict_types=1);

namespace App\Domains\Leads\Services;

use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Models\LeadAssignmentHistory;
use App\Domains\Notifications\Notifications\LeadAssignedNotification;
use App\Models\User;

final class LeadOwnershipService
{
    public function assign(
        Lead $lead,
        ?User $toOwner,
        ?User $actor = null,
        string $source = 'manual',
        ?User $fromOwner = null,
        bool $notify = false,
    ): void {
        $currentOwner = $lead->owner;
        $fromOwner ??= $currentOwner;

        if ($currentOwner?->id === $toOwner?->id && $fromOwner?->id === $currentOwner?->id) {
            return;
        }

        $lead->updateQuietly([
            'owner_id' => $toOwner?->id,
        ]);

        LeadAssignmentHistory::query()->create([
            'lead_id' => $lead->id,
            'from_owner_id' => $fromOwner?->id,
            'to_owner_id' => $toOwner?->id,
            'actor_id' => $actor?->id,
            'source' => $source,
        ]);

        $lead->unsetRelation('owner');
        $lead->loadMissing('owner');

        if ($notify) {
            $toOwner?->notify(new LeadAssignedNotification($lead));
        }
    }
}
