<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeBillingViewService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminBillingController extends Controller
{
    public function __invoke(Request $request, BackofficeShellService $shellService, BackofficeBillingViewService $service): View
    {
        return view('backoffice.admin.billing-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->adminGlobalView(),
            'activeMenu' => 'admin',
        ]);
    }
}
