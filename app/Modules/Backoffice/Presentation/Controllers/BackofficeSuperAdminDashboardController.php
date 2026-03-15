<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Backoffice\Application\Services\BackofficeSuperAdminDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeSuperAdminDashboardController extends Controller
{
    public function __invoke(Request $request, BackofficeShellService $shellService, BackofficeSuperAdminDashboardService $service): View
    {
        return view('backoffice.admin.dashboard', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->build(),
            'activeMenu' => 'admin',
        ]);
    }
}
