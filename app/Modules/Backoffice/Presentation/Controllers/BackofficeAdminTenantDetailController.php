<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Backoffice\Application\Services\BackofficeTenantAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminTenantDetailController extends Controller
{
    public function __invoke(Tenant $tenant, Request $request, BackofficeShellService $shellService, BackofficeTenantAdminService $service): View
    {
        return view('backoffice.admin.tenant-detail', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->detail($tenant),
            'activeMenu' => 'admin',
        ]);
    }
}
