<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Backoffice\Application\Services\CycleDetailViewService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

final class CycleDetailController extends Controller
{
    public function __invoke(
        Request $request,
        Cycle $cycle,
        CycleDetailViewService $service,
        BackofficeShellService $shellService,
    ): View {
        return view('backoffice.cycle-detail', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
