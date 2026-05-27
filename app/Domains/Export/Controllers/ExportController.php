<?php

declare(strict_types=1);

namespace App\Domains\Export\Controllers;

use App\Domains\Export\Jobs\ProcessExportJob;
use App\Domains\Export\Models\Export;
use App\Domains\Shared\Context\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportController
{
    public function index(): Response
    {
        \Gate::authorize('viewAny', Export::class);

        $exports = Export::query()
            ->where('team_id', TenantContext::getTeamId())
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('exports/Index', [
            'exports' => $exports,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        \Gate::authorize('create', Export::class);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:leads,forms'],
            'filters' => ['nullable', 'array'],
            'columns' => ['nullable', 'array'],
        ]);

        $export = Export::query()->create([
            'team_id' => TenantContext::getTeamId(),
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'status' => 'pending',
            'filters' => $validated['filters'] ?? [],
            'columns' => $validated['columns'] ?? [],
        ]);

        ProcessExportJob::dispatch($export->id)
            ->onQueue('exports');

        return to_route('exports.index');
    }

    public function download(Export $export): StreamedResponse
    {
        abort_if((int) $export->team_id !== TenantContext::getTeamId(), 403);

        \Gate::authorize('view', $export);

        abort_if($export->status !== 'completed', 404);

        return Storage::disk('local')->download(
            $export->file_path,
            $export->file_name,
        );
    }
}
