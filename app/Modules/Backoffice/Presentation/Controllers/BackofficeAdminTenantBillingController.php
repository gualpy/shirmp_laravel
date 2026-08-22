<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Backoffice\Application\Services\BackofficeBillingViewService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminTenantBillingController extends Controller
{
    public function __invoke(Tenant $tenant, Request $request, BackofficeShellService $shellService, BackofficeBillingViewService $service): View
    {
        return view('backoffice.admin.tenant-billing', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->adminTenantView($tenant),
            'activeMenu' => 'admin_tenants',
        ]);
    }
}
