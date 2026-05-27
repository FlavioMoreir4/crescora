<?php

declare(strict_types=1);

namespace App\Domains\Leads\Observers;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Leads\Events\LeadStatusChanged;
use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Models\LeadAssignmentHistory;
use App\Domains\Leads\Models\LeadStatusHistory;

final class LeadObserver
{
    public function creating(Lead $lead): void
    {
        $lead->status ??= LeadStatus::New;
    }

    public function created(Lead $lead): void
    {
        LeadStatusHistory::query()->create([
            'lead_id' => $lead->id,
            'from_status' => null,
            'to_status' => $lead->status,
            'actor_id' => $lead->owner_id,
        ]);

        if ($lead->owner_id !== null) {
            LeadAssignmentHistory::query()->create([
                'lead_id' => $lead->id,
                'from_owner_id' => null,
                'to_owner_id' => $lead->owner_id,
                'actor_id' => $lead->owner_id,
                'source' => 'creation',
            ]);
        }
    }

    public function updating(Lead $lead): void
    {
        if ($lead->isDirty('status')) {
            $original = $lead->getOriginal('status');
            $original = is_string($original) ? LeadStatus::tryFrom($original) : $original;

            LeadStatusHistory::query()->create([
                'lead_id' => $lead->id,
                'from_status' => $original,
                'to_status' => $lead->status,
                'actor_id' => request()?->user()?->id,
            ]);

            LeadStatusChanged::dispatch($lead, $original, $lead->status);
        }
    }
}
