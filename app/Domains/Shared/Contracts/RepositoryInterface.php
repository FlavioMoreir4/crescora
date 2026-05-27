<?php

declare(strict_types=1);

namespace App\Domains\Shared\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function findById(int $id): ?Model;

    public function getAll(array $columns = ['*']): Collection;

    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function paginate(int $perPage = 15, array $columns = ['*']);
}
