<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Backoffice\Application\Services\BackofficeTenantAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminTenantsController extends Controller
{
    public function __invoke(Request $request, BackofficeShellService $shellService, BackofficeTenantAdminService $service): View
    {
        return view('backoffice.admin.tenants-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->list($request->only(['plan', 'status', 'billing_type', 'read_only'])),
            'activeMenu' => 'admin',
        ]);
    }
}
