<?php

declare(strict_types=1);

namespace App\Domains\Leads\Requests;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Shared\Context\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('team_id', TenantContext::getTeamId())),
            ],
            'owner_id' => [
                'nullable',
                Rule::exists('team_members', 'user_id')->where(fn ($query) => $query->where('team_id', TenantContext::getTeamId())),
            ],
            'status' => ['sometimes', 'required', Rule::enum(LeadStatus::class)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:50'],
            'data' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
