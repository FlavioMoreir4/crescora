<?php

declare(strict_types=1);

namespace App\Domains\Forms\Models;

use App\Domains\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends BaseModel
{
    protected $fillable = [
        'form_id',
        'type',
        'name',
        'label',
        'placeholder',
        'options',
        'rules',
        'order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'rules' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public static function allowedTypes(): array
    {
        return [
            'text',
            'textarea',
            'email',
            'tel',
            'number',
            'select',
            'radio',
            'checkbox',
            'cpf',
            'cnpj',
            'cep',
            'date',
        ];
    }
}
