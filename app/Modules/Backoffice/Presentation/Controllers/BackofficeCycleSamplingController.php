<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleSamplingService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeCycleSamplingController extends Controller
{
    public function __invoke(
        int $cycleId,
        Request $request,
        BackofficeCycleSamplingService $samplingService,
        BackofficeShellService $shellService,
    ): View {
        $cycle = Cycle::query()->with(['pond.farm'])->findOrFail($cycleId);

        return view('backoffice.cycle-sampling', [
            'shell' => $shellService->build($request->user()),
            'vm' => $samplingService->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
