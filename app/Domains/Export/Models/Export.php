<?php

declare(strict_types=1);

namespace App\Domains\Export\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends BaseModel
{
    protected $fillable = [
        'team_id',
        'user_id',
        'type',
        'status',
        'filters',
        'columns',
        'file_path',
        'file_name',
        'file_size',
        'error',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
