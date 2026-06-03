<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeProductionSetupService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeStockingCreateController extends Controller
{
    public function __invoke(Request $request, BackofficeProductionSetupService $service, BackofficeShellService $shellService): View
    {
        return view('backoffice/stocking-create', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->stockingCreateView(),
            'activeMenu' => 'stocking',
        ]);
    }
}
