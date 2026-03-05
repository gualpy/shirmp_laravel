<?php

namespace App\Http\Middleware;

use App\Multitenancy\TenantContext;
use App\Multitenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantForBackoffice
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
            abort(400, 'Tenant could not be resolved. Use subdomain {tenant}.localhost, header X-Tenant or ?tenant=<slug>.');
        }

        $this->tenantContext->setCurrentTenant($tenant);

        return $next($request);
    }
}

