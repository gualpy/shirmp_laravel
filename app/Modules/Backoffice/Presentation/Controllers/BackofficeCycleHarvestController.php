<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleHarvestService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeCycleHarvestController extends Controller
{
    public function __invoke(
        int $cycleId,
        Request $request,
        BackofficeCycleHarvestService $harvestService,
        BackofficeShellService $shellService,
    ): View {
        $cycle = Cycle::query()->with(['pond.farm'])->findOrFail($cycleId);

        return view('backoffice.cycle-harvest', [
            'shell' => $shellService->build($request->user()),
            'vm' => $harvestService->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
