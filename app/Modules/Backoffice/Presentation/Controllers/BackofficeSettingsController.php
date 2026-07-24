<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeSettingsController extends Controller
{
    public function __invoke(Request $request, TenantContext $tenantContext, BackofficeShellService $shellService, LicenseService $licenseService): View
    {
        $tenant = $tenantContext->currentTenant();
        abort_unless($tenant instanceof Tenant, 400, 'Tenant context is not available.');

        return view('backoffice.settings-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => [
                'tenant' => $tenant,
                'read_only_mode' => (bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            ],
            'activeMenu' => 'settings',
        ]);
    }
}
