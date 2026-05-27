<?php

declare(strict_types=1);

namespace App\Domains\Export\Exports;

use App\Domains\Leads\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly int $teamId,
        private readonly array $filters = [],
        private readonly array $columns = [],
    ) {}

    public function query(): Builder
    {
        return Lead::query()
            ->where('team_id', $this->teamId)
            ->with(['unit', 'owner'])
            ->when($this->filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($this->filters['unit_id'] ?? null, fn ($q, $v) => $q->where('unit_id', $v))
            ->when($this->filters['owner_id'] ?? null, fn ($q, $v) => $q->where('owner_id', $v))
            ->when($this->filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        $default = ['Nome', 'Email', 'Telefone', 'Documento', 'Status', 'Unidade', 'Responsável', 'Fonte', 'Criado em'];

        if (! empty($this->columns)) {
            return $this->resolveHeadings($this->columns);
        }

        return $default;
    }

    public function map($lead): array
    {
        $default = [
            $lead->name,
            $lead->email,
            $lead->phone,
            $lead->document,
            $lead->status?->label() ?? $lead->status,
            $lead->unit?->name,
            $lead->owner?->name,
            $lead->source,
            $lead->created_at?->format('d/m/Y H:i'),
        ];

        if (! empty($this->columns)) {
            return $this->mapColumns($lead, $this->columns);
        }

        return $default;
    }

    private function resolveHeadings(array $columns): array
    {
        $map = [
            'name' => 'Nome',
            'email' => 'Email',
            'phone' => 'Telefone',
            'document' => 'Documento',
            'status' => 'Status',
            'unit' => 'Unidade',
            'owner' => 'Responsável',
            'source' => 'Fonte',
            'created_at' => 'Criado em',
            'notes' => 'Observações',
        ];

        return array_map(fn ($c) => $map[$c] ?? $c, $columns);
    }

    private function mapColumns($lead, array $columns): array
    {
        $data = [];

        foreach ($columns as $column) {
            $data[] = match ($column) {
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'document' => $lead->document,
                'status' => $lead->status?->label() ?? $lead->status,
                'unit' => $lead->unit?->name,
                'owner' => $lead->owner?->name,
                'source' => $lead->source,
                'created_at' => $lead->created_at?->format('d/m/Y H:i'),
                'notes' => $lead->notes,
                default => data_get($lead, $column),
            };
        }

        return $data;
    }
}
