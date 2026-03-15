<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeInventoryService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeWarehousesController extends Controller
{
    public function __invoke(Request $request, BackofficeInventoryService $service, BackofficeShellService $shellService): View
    {
        return view('backoffice.warehouses-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->warehousesView(),
            'activeMenu' => 'inventory',
        ]);
    }
}
