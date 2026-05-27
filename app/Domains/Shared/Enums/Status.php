<?php

declare(strict_types=1);

namespace App\Domains\Shared\Enums;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
            self::Archived => 'Arquivado',
        };
    }
}
