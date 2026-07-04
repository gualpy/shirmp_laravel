<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Alerts\Application\Actions\AcknowledgeAlertAction;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BackofficeAlertAcknowledgeController extends Controller
{
    public function __invoke(
        int $alertId,
        Request $request,
        AcknowledgeAlertAction $action,
        TenantContext $tenantContext,
        SaaSService $saasService,
        LicenseService $licenseService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_unless($saasService->checkFeature($tenant, 'alerts'), 403, 'Alerts are not available in the current plan.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'The current subscription is in read-only mode.');
        $alert = AlertEvent::query()->findOrFail($alertId);

        $action->execute($alert, $request->user());

        return back()->with('status', 'Alert acknowledged.');
    }
}
