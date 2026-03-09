<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeAlertsService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAlertsController extends Controller
{
    public function __invoke(
        Request $request,
        BackofficeAlertsService $alertsService,
        BackofficeShellService $shellService,
    ): View {
        return view('backoffice.alerts-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $alertsService->build($request->query()),
            'activeMenu' => 'alerts',
        ]);
    }
}
