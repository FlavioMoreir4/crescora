<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Leads\Enums\LeadStatus;
use App\Domains\Leads\Models\Lead;
use App\Domains\Shared\Context\TenantContext;
use App\Domains\Units\Models\Unit;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController
{
    public function __invoke(): Response
    {
        $teamId = TenantContext::getTeamId();

        $leadStats = collect(LeadStatus::cases())->map(fn ($status) => [
            'status' => $status->value,
            'label' => $status->label(),
            'color' => $status->color(),
            'count' => Lead::query()
                ->where('team_id', $teamId)
                ->where('status', $status)
                ->count(),
        ]);

        $totalLeads = $leadStats->sum('count');
        $wonLeads = $leadStats->firstWhere('status', LeadStatus::Won->value)['count'] ?? 0;
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        $unitCount = Unit::query()->forCurrentTeam()->count();

        $recentLeads = Lead::query()
            ->forCurrentTeam()
            ->with('unit')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($lead) => [
                'id' => $lead->id,
                'name' => $lead->name,
                'status' => $lead->status,
                'unit_name' => $lead->unit?->name,
                'created_at' => $lead->created_at->diffForHumans(),
            ]);

        return Inertia::render('Dashboard', [
            'leadStats' => $leadStats,
            'totalLeads' => $totalLeads,
            'unitCount' => $unitCount,
            'conversionRate' => $conversionRate,
            'recentLeads' => $recentLeads,
        ]);
    }
}
