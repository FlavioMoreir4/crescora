<?php

declare(strict_types=1);

namespace App\Domains\Distribution\Contracts;

use App\Domains\Leads\Models\Lead;
use App\Models\User;

interface DistributionStrategy
{
    public function assign(Lead $lead): ?User;
}
