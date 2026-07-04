<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateDailyMortalityAction;
use App\Modules\Production\Application\DTO\DailyMortalityDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Presentation\Requests\StoreDailyMortalityRequest;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeDailyMortalityStoreController extends Controller
{
    public function __invoke(
        int $cycleId,
        StoreDailyMortalityRequest $request,
        CreateDailyMortalityAction $action,
        TenantContext $tenantContext,
        LicenseService $licenseService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'The current subscription is in read-only mode.');

        $cycle = Cycle::query()->findOrFail($cycleId);
        $pond = Pond::query()->findOrFail((int) $request->validated()['pond_id']);

        abort_if($cycle->pond_id !== $pond->id, 422, 'The selected pond does not belong to this cycle.');

        $action->execute($pond, DailyMortalityDTO::fromArray(array_merge(
            $request->validated(),
            ['created_by' => $request->user()?->id],
        )));

        return redirect('/backoffice/cycles/'.$cycleId.'/mortalities')->with('status', 'Daily mortality recorded.');
    }
}
