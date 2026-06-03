<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeProductionSetupService;
use App\Modules\Backoffice\Presentation\Requests\StoreBackofficeFarmRequest;
use Illuminate\Http\RedirectResponse;

final class BackofficeFarmStoreController extends Controller
{
    public function __invoke(StoreBackofficeFarmRequest $request, BackofficeProductionSetupService $service): RedirectResponse
    {
        $farm = $service->createFarm($request->validated());

        return redirect()
            ->route('backoffice.farms.index')
            ->with('status', 'Finca creada: '.$farm->name);
    }
}
