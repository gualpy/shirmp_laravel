<?php

namespace App\Http\Middleware;

use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
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

        if (! in_array($subscription->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL], true)) {
            return new JsonResponse(['message' => 'Tenant subscription is not active.'], 403);
        }

        return $next($request);
    }
}

