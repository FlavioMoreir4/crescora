<?php

declare(strict_types=1);

namespace App\Domains\Users\Models;

use App\Concerns\HasTeams;
use App\Domains\Teams\Models\TeamResourceAccess;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements PasskeyUser
{
    use HasFactory, HasRoles, HasTeams, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable {
        HasRoles::teams as permissionTeams;
        HasTeams::teams insteadof HasRoles;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
        'is_system_admin',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_system_admin' => 'boolean',
        ];
    }

    public function isSystemAdmin(): bool
    {
        return (bool) $this->is_system_admin;
    }

    /**
     * @return HasMany<TeamResourceAccess, $this>
     */
    public function teamResourceAccesses(): HasMany
    {
        return $this->hasMany(TeamResourceAccess::class, 'user_id');
    }

    public function accessibleResourceIds(
        TeamResourceType|string $resourceType,
        TeamResourceAccessLevel|string $minimumLevel = TeamResourceAccessLevel::View,
        ?int $teamId = null,
    ): array {
        $teamId ??= $this->current_team_id;

        if ($teamId === null) {
            return [];
        }

        $resourceType = $resourceType instanceof TeamResourceType
            ? $resourceType->value
            : $resourceType;

        $minimumLevel = $minimumLevel instanceof TeamResourceAccessLevel
            ? $minimumLevel
            : TeamResourceAccessLevel::from($minimumLevel);

        $allowedLevels = collect(TeamResourceAccessLevel::cases())
            ->filter(fn (TeamResourceAccessLevel $level) => $level->allows($minimumLevel))
            ->map(fn (TeamResourceAccessLevel $level) => $level->value)
            ->all();

        return $this->teamResourceAccesses()
            ->where('team_id', $teamId)
            ->where('resource_type', $resourceType)
            ->whereIn('access_level', $allowedLevels)
            ->pluck('resource_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function hasResourceAccess(
        TeamResourceType|string $resourceType,
        int $resourceId,
        TeamResourceAccessLevel|string $minimumLevel = TeamResourceAccessLevel::View,
        ?int $teamId = null,
    ): bool {
        return in_array(
            $resourceId,
            $this->accessibleResourceIds($resourceType, $minimumLevel, $teamId),
            true,
        );
    }

    public function hasAnyResourceAccess(
        TeamResourceType|string $resourceType,
        TeamResourceAccessLevel|string $minimumLevel = TeamResourceAccessLevel::View,
        ?int $teamId = null,
    ): bool {
        return $this->accessibleResourceIds($resourceType, $minimumLevel, $teamId) !== [];
    }
}
