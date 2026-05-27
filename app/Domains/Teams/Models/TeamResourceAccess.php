<?php

declare(strict_types=1);

namespace App\Domains\Teams\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamResourceAccess extends BaseModel
{
    protected $fillable = [
        'team_id',
        'user_id',
        'resource_type',
        'resource_id',
        'access_level',
        'granted_by',
    ];

    protected function casts(): array
    {
        return [
            'resource_type' => TeamResourceType::class,
            'access_level' => TeamResourceAccessLevel::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
