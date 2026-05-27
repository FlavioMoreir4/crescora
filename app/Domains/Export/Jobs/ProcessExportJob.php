<?php

declare(strict_types=1);

namespace App\Domains\Export\Jobs;

use App\Domains\Export\Exports\FormsExport;
use App\Domains\Export\Exports\LeadsExport;
use App\Domains\Export\Models\Export;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly int $exportId,
    ) {}

    public function handle(): void
    {
        $export = Export::findOrFail($this->exportId);

        $export->update(['status' => 'processing']);

        try {
            $exportClass = match ($export->type) {
                'leads' => new LeadsExport(
                    teamId: $export->team_id,
                    filters: $export->filters ?? [],
                    columns: $export->columns ?? [],
                ),
                'forms' => new FormsExport(
                    teamId: $export->team_id,
                    filters: $export->filters ?? [],
                ),
                default => throw new \InvalidArgumentException("Unknown export type: {$export->type}"),
            };

            $fileName = sprintf(
                '%s_%s_%s.xlsx',
                $export->type,
                $export->id,
                now()->format('Ymd_His'),
            );

            $path = sprintf('exports/%d/%s', $export->team_id, $fileName);

            Excel::store($exportClass, $path, 'local');

            $fullPath = Storage::disk('local')->path($path);

            $export->update([
                'status' => 'completed',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_size' => filesize($fullPath),
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $export->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
