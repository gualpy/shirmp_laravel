<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Presentation\Requests\StoreBillingInvoiceRequest;
use App\Modules\SaaS\Application\Services\SaaSService;
use Illuminate\Http\RedirectResponse;

final class BackofficeAdminBillingInvoiceStoreController extends Controller
{
    public function __invoke(
        Tenant $tenant,
        StoreBillingInvoiceRequest $request,
        BillingService $billingService,
        SaaSService $saasService,
    ): RedirectResponse {
        $subscription = $saasService->currentSubscription($tenant);

        $billingService->createInvoiceForSubscription($tenant, $subscription, $request->validated());

        return redirect()
            ->route('backoffice.admin.tenants.billing', $tenant)
            ->with('status', 'Invoice creada correctamente.');
    }
}
