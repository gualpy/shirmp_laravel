<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeProductionSetupService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficePondsController extends Controller
{
    public function __invoke(Request $request, BackofficeProductionSetupService $service, BackofficeShellService $shellService): View
    {
        $farmId = $request->query('farm') !== null ? (int) $request->query('farm') : null;

        return view('backoffice.ponds-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->pondsView($farmId),
            'activeMenu' => 'ponds',
        ]);
    }
}
