<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Costing\Application\Actions\CreateOperationalCostAction;
use App\Modules\Costing\Application\DTO\OperationalCostEntryDTO;
use App\Modules\Costing\Presentation\Requests\StoreOperationalCostRequest;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeOperationalCostStoreController extends Controller
{
    public function __invoke(
        int $cycleId,
        StoreOperationalCostRequest $request,
        CreateOperationalCostAction $action,
        TenantContext $tenantContext,
        SaaSService $saasService,
        LicenseService $licenseService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_unless($saasService->checkFeature($tenant, 'cost_engine'), 403, 'Cost Engine no disponible en el plan actual.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'La suscripción actual está en modo solo lectura.');

        $cycle = Cycle::query()->findOrFail($cycleId);
        $action->execute($cycle, OperationalCostEntryDTO::fromArray($request->validated()));

        return redirect('/backoffice/cycles/'.$cycleId.'/costs')->with('status', 'Costo operativo registrado.');
    }
}
