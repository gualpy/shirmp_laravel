<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\SaaS\Application\Services\TenantOnboardingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminTenantCreateController extends Controller
{
    public function __invoke(Request $request, BackofficeShellService $shellService, TenantOnboardingService $service): View
    {
        return view('backoffice.admin.tenant-create', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->formViewModel(),
            'activeMenu' => 'admin_tenants',
        ]);
    }
}
