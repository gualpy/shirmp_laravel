<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Feeding\Application\Actions\CreateFeedEntryAction;
use App\Modules\Feeding\Application\DTO\FeedEntryDataDTO;
use App\Modules\Feeding\Presentation\Requests\StoreFeedEntryRequest;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeCycleFeedingStoreController extends Controller
{
    public function __invoke(
        int $cycleId,
        StoreFeedEntryRequest $request,
        CreateFeedEntryAction $action,
        TenantContext $tenantContext,
        LicenseService $licenseService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'La suscripción actual está en modo solo lectura.');

        $cycle = Cycle::query()->findOrFail($cycleId);

        $action->execute($cycle, FeedEntryDataDTO::fromArray($request->validated()));

        return redirect('/backoffice/cycles/'.$cycleId.'/feeding')->with('status', __('cycle.feeding_saved'));
    }
}
