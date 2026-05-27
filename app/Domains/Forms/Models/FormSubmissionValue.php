<?php

declare(strict_types=1);

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionValue extends BaseModel
{
    protected $fillable = [
        'form_submission_id',
        'field_id',
        'value',
        'value_type',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
