<?php

declare(strict_types=1);

namespace App\Domains\Shared\Enums;

enum Currency: string
{
    case BRL = 'BRL';
    case USD = 'USD';

    public function label(): string
    {
        return match ($this) {
            self::BRL => 'Real (R$)',
            self::USD => 'Dólar (US$)',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::BRL => 'R$',
            self::USD => 'US$',
        };
    }
}
