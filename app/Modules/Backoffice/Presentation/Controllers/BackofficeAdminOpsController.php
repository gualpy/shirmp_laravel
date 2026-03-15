<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeOpsService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminOpsController extends Controller
{
    public function __invoke(Request $request, BackofficeShellService $shellService, BackofficeOpsService $service): View
    {
        return view('backoffice.admin.ops', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->build(),
            'activeMenu' => 'admin',
        ]);
    }
}
