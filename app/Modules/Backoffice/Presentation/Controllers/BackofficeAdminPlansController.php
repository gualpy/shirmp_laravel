<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficePlansAdminService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminPlansController extends Controller
{
    public function __invoke(Request $request, BackofficeShellService $shellService, BackofficePlansAdminService $service): View
    {
        return view('backoffice.admin.plans-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => ['rows' => $service->list()],
            'activeMenu' => 'admin',
        ]);
    }
}
