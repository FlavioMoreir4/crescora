<?php

declare(strict_types=1);

namespace App\Domains\Shared\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function getCreatedAtFormattedAttribute(): ?string
    {
        if ($this->created_at === null) {
            return null;
        }

        return Carbon::make($this->created_at)->format('d/m/Y H:i');
    }

    public function getUpdatedAtFormattedAttribute(): ?string
    {
        if ($this->updated_at === null) {
            return null;
        }

        return Carbon::make($this->updated_at)->format('d/m/Y H:i');
    }

    public function scopeOrdered(Builder $query, string $column = 'created_at', string $direction = 'desc'): void
    {
        $query->orderBy($column, $direction);
    }
}
