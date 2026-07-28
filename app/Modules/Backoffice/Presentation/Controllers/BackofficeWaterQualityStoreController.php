<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\WaterQuality\Application\Actions\CreateWaterQualityEntryAction;
use App\Modules\WaterQuality\Application\DTO\WaterQualityEntryDTO;
use App\Modules\WaterQuality\Presentation\Requests\StoreQuickWaterQualityEntryRequest;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

final class BackofficeWaterQualityStoreController extends Controller
{
    public function __invoke(
        StoreQuickWaterQualityEntryRequest $request,
        TenantContext $tenantContext,
        SaaSService $saasService,
        LicenseService $licenseService,
        CreateWaterQualityEntryAction $action,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_unless($saasService->checkFeature($tenant, 'water_quality'), 403, 'Water Quality is not available in the current plan.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'The current subscription is in read-only mode.');

        $payload = $request->normalized();
        $pond = Pond::query()->findOrFail($payload['pond_id']);
        $cycle = $pond->cycles()
            ->where('status', CycleStatus::ACTIVE->value)
            ->latest('started_at')
            ->first();

        if ($cycle === null) {
            throw ValidationException::withMessages([
                'pond' => [__('messages.pond.no_active_cycle')],
            ]);
        }

        $action->execute($cycle, WaterQualityEntryDTO::fromArray(array_merge($payload, [
            'measured_by_user_id' => $request->user()?->id,
        ])));

        return redirect('/backoffice/water?cycle='.$cycle->id)->with('status', 'Water quality entry saved.');
    }
}
