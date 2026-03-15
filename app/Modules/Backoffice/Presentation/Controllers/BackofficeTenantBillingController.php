<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeBillingViewService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Multitenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeTenantBillingController extends Controller
{
    public function __invoke(
        Request $request,
        TenantContext $tenantContext,
        BackofficeShellService $shellService,
        BackofficeBillingViewService $service,
    ): View {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 404);

        return view('backoffice.billing-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->tenantView($tenant),
            'activeMenu' => 'billing',
        ]);
    }
}
