<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleListService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeCycleListController extends Controller
{
    public function __invoke(
        Request $request,
        BackofficeCycleListService $listService,
        BackofficeShellService $shellService,
    ): View {
        return view('backoffice.cycles-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $listService->build($request->query()),
            'activeMenu' => 'cycles',
        ]);
    }
}

