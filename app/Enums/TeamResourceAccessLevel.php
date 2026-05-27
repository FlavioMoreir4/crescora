<?php

declare(strict_types=1);

namespace App\Enums;

enum TeamResourceAccessLevel: string
{
    case View = 'view';
    case Manage = 'manage';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Visualizar',
            self::Manage => 'Gerenciar',
        };
    }

    public function allows(self $required): bool
    {
        return $this->rank() >= $required->rank();
    }

    public function rank(): int
    {
        return match ($this) {
            self::View => 1,
            self::Manage => 2,
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $level) => [
                'value' => $level->value,
                'label' => $level->label(),
            ])
            ->toArray();
    }
}
