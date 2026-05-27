<?php

declare(strict_types=1);

namespace App\Domains\Shared\DTOs;

use Spatie\LaravelData\Data;

final class FilterData extends Data
{
    public function __construct(
        public readonly array $filters = [],
        public readonly ?string $search = null,
    ) {}
}
