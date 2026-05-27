<?php

declare(strict_types=1);

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Domains\Units\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends BaseModel
{
    protected $fillable = [
        'form_id',
        'unit_id',
        'data',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'metadata' => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class);
    }
}
