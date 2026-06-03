<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeProductionSetupService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeFarmsController extends Controller
{
    public function __invoke(Request $request, BackofficeProductionSetupService $service, BackofficeShellService $shellService): View
    {
        return view('backoffice.farms-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->farmsView(),
            'activeMenu' => 'farms',
        ]);
    }
}
