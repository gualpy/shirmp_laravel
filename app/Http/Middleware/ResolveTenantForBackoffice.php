<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Multitenancy\TenantContext;
use App\Multitenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $tenant = $this->tenantFromSessionOrQuery($request);
        }

        if ($tenant === null) {
            if (! Auth::check()) {
                return redirect()->route('login');
            }

            abort(400, 'Tenant could not be resolved. Use subdomain {tenant}.localhost, header X-Tenant or ?tenant=<slug>.');
        }

        $request->session()->put('backoffice_tenant_slug', $tenant->slug);
        $this->tenantContext->setCurrentTenant($tenant);

        if ($request->user() !== null && (int) $request->user()->tenant_id !== (int) $tenant->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'tenant' => 'La sesión no coincide con el tenant seleccionado.',
            ]);
        }

        return $next($request);
    }

    private function tenantFromSessionOrQuery(Request $request): ?Tenant
    {
        $slug = strtolower(trim((string) $request->query('tenant', '')));

        if ($slug === '') {
            $slug = strtolower(trim((string) $request->session()->get('backoffice_tenant_slug', '')));
        }

        if ($slug === '') {
            return null;
        }

        return Tenant::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
