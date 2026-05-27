<?php

declare(strict_types=1);

namespace App\Domains\Forms\Requests;

use App\Domains\Forms\Models\FormField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'config' => ['nullable', 'array'],
            'fields' => ['nullable', 'array'],
            'fields.*.type' => ['required', 'string', Rule::in(FormField::allowedTypes())],
            'fields.*.name' => ['required', 'string', 'max:255'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.is_required' => ['boolean'],
            'fields.*.order' => ['integer', 'min:0'],
        ];
    }
}
