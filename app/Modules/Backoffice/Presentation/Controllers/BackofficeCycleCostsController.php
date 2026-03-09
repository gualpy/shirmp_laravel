<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleCostsService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeCycleCostsController extends Controller
{
    public function __invoke(
        int $cycleId,
        Request $request,
        BackofficeCycleCostsService $costsService,
        BackofficeShellService $shellService,
    ): View {
        $cycle = Cycle::query()->with(['pond.farm'])->findOrFail($cycleId);

        return view('backoffice.cycle-costs', [
            'shell' => $shellService->build($request->user()),
            'vm' => $costsService->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
