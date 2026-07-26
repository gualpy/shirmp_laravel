<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateSamplingAction;
use App\Modules\Production\Application\DTO\SamplingDataDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Presentation\Requests\StoreSamplingRequest;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeCycleSamplingStoreController extends Controller
{
    public function __invoke(
        int $cycleId,
        StoreSamplingRequest $request,
        CreateSamplingAction $action,
        TenantContext $tenantContext,
        LicenseService $licenseService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'La suscripción actual está en modo solo lectura.');

        $cycle = Cycle::query()->findOrFail($cycleId);

        $action->execute($cycle, SamplingDataDTO::fromArray($request->validated()));

        return redirect('/backoffice/cycles/'.$cycleId.'/sampling')->with('status', __('cycle.sampling_saved'));
    }
}
