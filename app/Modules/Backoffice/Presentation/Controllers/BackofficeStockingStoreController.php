<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeProductionSetupService;
use App\Modules\Backoffice\Presentation\Requests\StoreBackofficeStockingFlowRequest;
use Illuminate\Http\RedirectResponse;

final class BackofficeStockingStoreController extends Controller
{
    public function __invoke(StoreBackofficeStockingFlowRequest $request, BackofficeProductionSetupService $service): RedirectResponse
    {
        $cycle = $service->createStockingFlow($request->validated(), $request->user());

        return redirect()
            ->route('backoffice.cycles.show', ['cycleId' => $cycle->id])
            ->with('status', 'Siembra registrada en '.$cycle->pond?->code.'.');
    }
}
