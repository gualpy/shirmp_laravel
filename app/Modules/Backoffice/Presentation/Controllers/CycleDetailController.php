<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Backoffice\Application\Services\CycleDetailViewService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

final class CycleDetailController extends Controller
{
    public function __invoke(
        Request $request,
        int $cycleId,
        CycleDetailViewService $service,
        BackofficeShellService $shellService,
    ): View {
        $cycle = Cycle::query()->find($cycleId);

        if ($cycle === null) {
            throw (new ModelNotFoundException())->setModel(Cycle::class, [$cycleId]);
        }

        return view('backoffice.cycle-detail', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->build($cycle),
            'activeMenu' => 'cycles',
        ]);
    }
}
