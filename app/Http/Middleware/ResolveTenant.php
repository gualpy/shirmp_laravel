<?php

namespace App\Http\Middleware;

use App\Multitenancy\TenantContext;
use App\Multitenancy\TenantResolver;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private readonly TenantResolver $tenantResolver,
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantResolver->resolve($request);

        if ($tenant === null) {
            return new JsonResponse([
                'message' => 'Tenant could not be resolved. Use subdomain {tenant}.localhost or header X-Tenant.',
            ], 400);
        }

        $this->tenantContext->setCurrentTenant($tenant);

        return $next($request);
    }
}
