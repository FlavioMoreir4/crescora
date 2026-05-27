<?php

declare(strict_types=1);

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Domains\Shared\Models\Concerns\BelongsToTeam;
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
