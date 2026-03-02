<?php

namespace App\Http\Middleware;

use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionActive
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantContext->currentTenant();

        if ($tenant === null) {
            return new JsonResponse(['message' => 'Tenant context is not available.'], 400);
        }

        $subscription = $this->saasService->currentSubscription($tenant);

        // Backward-compatible mode: tenants without subscription are allowed.
        if ($subscription === null) {
            return $next($request);
        }

        $resolution = $this->licenseService->requireActiveOrGrace($tenant);
        $readOnly = (bool) $resolution['read_only_mode'];
        $this->tenantContext->setReadOnlyMode($readOnly);
        $request->attributes->set('read_only_mode', $readOnly);

        $response = $next($request);

        if ($readOnly) {
            $response->headers->set('X-Read-Only-Mode', '1');
        }

        return $response;
    }
}
