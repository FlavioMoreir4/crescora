<?php

declare(strict_types=1);

namespace App\Http\Requests\Teams;

use App\Enums\TeamResourceAccessLevel;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberResourceAccessRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $team = $this->route('team');
        $teamId = $team instanceof Team ? $team->id : null;
        $levels = array_column(TeamResourceAccessLevel::options(), 'value');

        return [
            'resources' => ['required', 'array'],
            'resources.units' => ['nullable', 'array'],
            'resources.units.*.resource_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where('team_id', $teamId),
            ],
            'resources.units.*.access_level' => ['nullable', 'string', Rule::in($levels)],
            'resources.forms' => ['nullable', 'array'],
            'resources.forms.*.resource_id' => [
                'required',
                'integer',
                Rule::exists('forms', 'id')->where('team_id', $teamId),
            ],
            'resources.forms.*.access_level' => ['nullable', 'string', Rule::in($levels)],
        ];
    }
}
