<?php

declare(strict_types=1);

namespace App\Domains\Export\Exports;

use App\Domains\Forms\Models\FormSubmission;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FormsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly int $teamId,
        private readonly array $filters = [],
    ) {}

    public function query(): Builder
    {
        return FormSubmission::query()
            ->whereHas('form', fn ($q) => $q->where('team_id', $this->teamId))
            ->with('form')
            ->when($this->filters['form_id'] ?? null, fn ($q, $v) => $q->where('form_id', $v))
            ->when($this->filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['Formulário', 'Dados', 'Criado em'];
    }

    public function map($submission): array
    {
        return [
            $submission->form?->name,
            json_encode($submission->data, JSON_UNESCAPED_UNICODE),
            $submission->created_at?->format('d/m/Y H:i'),
        ];
    }
}
