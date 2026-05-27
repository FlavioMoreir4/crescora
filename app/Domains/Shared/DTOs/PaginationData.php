<?php

declare(strict_types=1);

namespace App\Domains\Shared\DTOs;

use Spatie\LaravelData\Data;

final class PaginationData extends Data
{
    public function __construct(
        public readonly int $perPage = 15,
        public readonly int $page = 1,
        public readonly string $sortField = 'created_at',
        public readonly string $sortDirection = 'desc',
    ) {}
}
