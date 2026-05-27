<?php

declare(strict_types=1);

namespace App\Domains\Leads\Events;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Leads\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
        public readonly ?LeadStatus $from,
        public readonly LeadStatus $to,
    ) {}
}
