<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleMortalityService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeCycleMortalityController extends Controller
{
    public function __invoke(
        int $cycleId,
        Request $request,
        BackofficeCycleMortalityService $mortalityService,
        BackofficeShellService $shellService,
    ): View {
        $cycle = Cycle::query()->with(['pond.farm'])->findOrFail($cycleId);

        return view('backoffice.cycle-mortalities', [
            'shell' => $shellService->build($request->user()),
            'vm' => $mortalityService->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
