<?php

declare(strict_types=1);

namespace App\Domains\Leads\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Novo',
            self::Contacted => 'Contactado',
            self::Qualified => 'Qualificado',
            self::Proposal => 'Proposta',
            self::Negotiation => 'Negociação',
            self::Won => 'Ganho',
            self::Lost => 'Perdido',
            self::Archived => 'Arquivado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Contacted => 'blue',
            self::Qualified => 'indigo',
            self::Proposal => 'violet',
            self::Negotiation => 'amber',
            self::Won => 'green',
            self::Lost => 'red',
            self::Archived => 'neutral',
        };
    }

    public static function activePipeline(): array
    {
        return [
            self::New,
            self::Contacted,
            self::Qualified,
            self::Proposal,
            self::Negotiation,
        ];
    }

    /** @return LeadStatus[] */
    public static function closed(): array
    {
        return [self::Won, self::Lost, self::Archived];
    }

    public function isActive(): bool
    {
        return in_array($this, self::activePipeline(), true);
    }

    public function isClosed(): bool
    {
        return in_array($this, self::closed(), true);
    }
}
