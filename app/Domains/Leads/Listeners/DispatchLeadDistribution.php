<?php

declare(strict_types=1);

namespace App\Domains\Leads\Listeners;

use App\Domains\Distribution\Services\DistributionService;
use App\Domains\Leads\Events\LeadCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class DispatchLeadDistribution implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DistributionService $distribution,
    ) {}

    public function handle(LeadCreated $event): void
    {
        $this->distribution->assign($event->lead);
    }
}
