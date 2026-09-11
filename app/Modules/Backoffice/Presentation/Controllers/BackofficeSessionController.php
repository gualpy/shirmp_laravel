<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Backoffice\Presentation\Requests\BackofficeLoginRequest;
use App\Modules\Auth\Application\DTO\LoginDTO;
use App\Modules\Auth\Application\Services\AuthService;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class BackofficeSessionController extends Controller
{
    public function create(Request $request, TenantContext $tenantContext): View|RedirectResponse
    {
        return view('backoffice.auth.login', [
            'defaultTenant' => (string) $request->query('tenant', ''),
        ]);
    }

    public function store(
        BackofficeLoginRequest $request,
        AuthService $authService,
        TenantContext $tenantContext,
    ): RedirectResponse
    {
        $tenantSlug = strtolower(trim((string) $request->string('tenant')));

        $tenant = Tenant::query()
            ->where('slug', $tenantSlug)
            ->where('is_active', true)
            ->first();

        if ($tenant === null) {
            return back()
                ->withErrors(['tenant' => 'Tenant inválido o inactivo.'])
                ->withInput($request->except('password'));
        }

        $dto = new LoginDTO(
            email: (string) $request->string('email'),
            password: (string) $request->string('password'),
            deviceName: 'backoffice-web',
        );

        $tenantContext->setCurrentTenant($tenant);
        $user = $authService->authenticate($dto, $tenant);
        $tenantContext->clear();

        if ($user === null) {
            return back()
                ->withErrors(['email' => 'Credenciales inválidas para este tenant.'])
                ->withInput($request->except('password'));
        }

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('backoffice_tenant_slug', $tenant->slug);
        $request->session()->forget('backoffice_login_redirected');

        $defaultRoute = $user->role === UserRole::SUPER_ADMIN ? 'backoffice.admin.dashboard' : 'backoffice.home';

        return redirect()->intended(route($defaultRoute));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->forget('backoffice_tenant_slug');
        $request->session()->forget('backoffice_login_redirected');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
