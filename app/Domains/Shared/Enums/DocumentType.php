<?php

declare(strict_types=1);

namespace App\Domains\Shared\Enums;

enum DocumentType: string
{
    case CPF = 'cpf';
    case CNPJ = 'cnpj';

    public function label(): string
    {
        return match ($this) {
            self::CPF => 'CPF',
            self::CNPJ => 'CNPJ',
        };
    }

    public function length(): int
    {
        return match ($this) {
            self::CPF => 11,
            self::CNPJ => 14,
        };
    }
}
