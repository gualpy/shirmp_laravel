<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Presentation\Requests\StoreTenantOnboardingRequest;
use App\Modules\SaaS\Application\Services\TenantOnboardingService;
use Illuminate\Http\RedirectResponse;

final class BackofficeAdminTenantStoreController extends Controller
{
    public function __invoke(StoreTenantOnboardingRequest $request, TenantOnboardingService $service): RedirectResponse
    {
        $tenant = $service->onboard($request->validated());

        return redirect()
            ->route('backoffice.admin.tenants.show', $tenant)
            ->with('status', 'Tenant creado correctamente.');
    }
}
