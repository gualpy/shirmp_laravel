<?php

namespace App\Http\Middleware;

use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $tenant = $this->tenantContext->currentTenant();

        if ($tenant !== null) {
            $this->saasService->enforceFeatureOrFail($tenant, $featureKey);
        }

        return $next($request);
    }
}

