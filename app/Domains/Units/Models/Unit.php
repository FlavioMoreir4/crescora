<?php

declare(strict_types=1);

namespace App\Domains\Units\Models;

use App\Domains\Leads\Models\Lead;
use App\Domains\Shared\Models\BaseModel;
use App\Domains\Shared\Models\Concerns\BelongsToTeam;
use App\Domains\Shared\Context\TenantContext;
use App\Domains\Teams\Models\TeamResourceAccess;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Unit extends BaseModel
{
    use BelongsToTeam, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Unit $unit) {
            if (empty($unit->slug)) {
                $unit->slug = Str::slug($unit->name);
            }
        });

        static::updating(function (Unit $unit) {
            if ($unit->isDirty('name') && ! $unit->isDirty('slug')) {
                $unit->slug = Str::slug($unit->name);
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'unit_id');
    }

    public static function resourceType(): TeamResourceType
    {
        return TeamResourceType::Unit;
    }

    /**
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();
        $team = TenantContext::currentTeam();

        if ($user === null || $team === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSystemAdmin() || $user->ownsTeam($team) || $user->hasRole('admin')) {
            return $query;
        }

        $ids = $user->accessibleResourceIds(self::resourceType(), TeamResourceAccessLevel::View, $team->id);

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($this->qualifyColumn('id'), $ids);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
