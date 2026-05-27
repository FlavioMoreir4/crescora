<?php

declare(strict_types=1);

namespace App\Domains\Billing\Enums;

enum SubscriptionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Active => 'Ativo',
            self::Trialing => 'Período de Teste',
            self::PastDue => 'Pagamento Pendente',
            self::Canceled => 'Cancelado',
            self::Expired => 'Expirado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Trialing => 'blue',
            self::PastDue => 'yellow',
            self::Canceled => 'red',
            self::Expired => 'gray',
            self::Pending => 'gray',
        };
    }
}
