<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeProductionSetupService;
use App\Modules\Backoffice\Presentation\Requests\StoreBackofficePondRequest;
use Illuminate\Http\RedirectResponse;

final class BackofficePondStoreController extends Controller
{
    public function __invoke(StoreBackofficePondRequest $request, BackofficeProductionSetupService $service): RedirectResponse
    {
        $pond = $service->createPond($request->validated());

        return redirect()
            ->route('backoffice.ponds.index', ['farm' => $pond->farm_id])
            ->with('status', 'Piscina creada: '.$pond->code);
    }
}
