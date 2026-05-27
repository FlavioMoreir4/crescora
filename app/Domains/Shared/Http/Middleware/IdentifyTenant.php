<?php

declare(strict_types=1);

namespace App\Domains\Shared\Http\Middleware;

use App\Domains\Shared\Context\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = $request->user()?->current_team_id;

        if ($teamId !== null) {
            TenantContext::setTeamId((int) $teamId);
        }

        return $next($request);
    }
}
