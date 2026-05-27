<?php

declare(strict_types=1);

namespace App\Enums;

enum TeamResourceType: string
{
    case Unit = 'unit';
    case Form = 'form';

    public function label(): string
    {
        return match ($this) {
            self::Unit => 'Unidade',
            self::Form => 'Formulário',
        };
    }
}
