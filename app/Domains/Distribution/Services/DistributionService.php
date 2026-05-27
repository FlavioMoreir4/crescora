<?php

declare(strict_types=1);

namespace App\Domains\Distribution\Services;

use App\Domains\Distribution\Contracts\DistributionStrategy;
use App\Domains\Distribution\Strategies\RoundRobinStrategy;
use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Services\LeadOwnershipService;

final class DistributionService
{
    private DistributionStrategy $strategy;

    public function __construct(
        private readonly LeadOwnershipService $ownership,
        ?DistributionStrategy $strategy = null,
    ) {
        $this->strategy = $strategy ?? new RoundRobinStrategy;
    }

    public function assign(Lead $lead): void
    {
        if ($lead->owner_id !== null) {
            return;
        }

        $owner = $this->strategy->assign($lead);

        if ($owner !== null) {
            $this->ownership->assign(
                $lead,
                $owner,
                null,
                'distribution',
                null,
                false,
            );
        }
    }
}
