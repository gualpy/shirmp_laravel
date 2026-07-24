<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleFeedingService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeCycleFeedingController extends Controller
{
    public function __invoke(
        int $cycleId,
        Request $request,
        BackofficeCycleFeedingService $feedingService,
        BackofficeShellService $shellService,
    ): View {
        $cycle = Cycle::query()->with(['pond.farm'])->findOrFail($cycleId);

        return view('backoffice.cycle-feeding', [
            'shell' => $shellService->build($request->user()),
            'vm' => $feedingService->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
