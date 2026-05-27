<?php

declare(strict_types=1);

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Domains\Shared\Models\Concerns\BelongsToTeam;
use App\Domains\Shared\Context\TenantContext;
use App\Enums\TeamResourceAccessLevel;
use App\Enums\TeamResourceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Form extends BaseModel
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'description',
        'is_active',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Form $form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name);
            }
        });

        static::updating(function (Form $form) {
            if ($form->isDirty('name') && ! $form->isDirty('slug')) {
                $form->slug = Str::slug($form->name);
            }
        });
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public static function resourceType(): TeamResourceType
    {
        return TeamResourceType::Form;
    }

    public function leadAssignmentMode(): string
    {
        return (string) data_get($this->config, 'lead_assignment.mode', 'distribution');
    }

    public function leadAssignmentOwnerId(): ?int
    {
        $ownerId = data_get($this->config, 'lead_assignment.owner_id');

        return $ownerId !== null ? (int) $ownerId : null;
    }

    public function leadAssignmentConfig(): array
    {
        return [
            'mode' => $this->leadAssignmentMode(),
            'owner_id' => $this->leadAssignmentOwnerId(),
        ];
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
