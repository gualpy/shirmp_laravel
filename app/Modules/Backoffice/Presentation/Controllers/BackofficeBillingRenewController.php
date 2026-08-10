<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingCheckoutService;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Throwable;

final class BackofficeBillingRenewController extends Controller
{
    public function __invoke(
        TenantContext $tenantContext,
        SaaSService $saasService,
        BillingService $billingService,
        BillingCheckoutService $checkoutService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 404);

        $subscription = $saasService->currentSubscription($tenant);

        if ($subscription === null) {
            return redirect()->route('backoffice.billing.index')
                ->withErrors(['checkout' => __('billing.checkout_not_payable')]);
        }

        try {
            $invoice = $billingService->createRenewalInvoiceForSubscription($tenant, $subscription);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('backoffice.billing.index')
                ->withErrors(['checkout' => __('billing.checkout_not_payable')]);
        }

        $successUrl = route('backoffice.billing.checkout.return', ['invoiceId' => $invoice->id]);
        $cancelUrl = route('backoffice.billing.index');

        try {
            $checkoutSession = $checkoutService->start($invoice, $subscription->plan, BillingPaymentProvider::PAYPAL, $successUrl, $cancelUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('backoffice.billing.index')
                ->withErrors(['checkout' => __('billing.checkout_error')]);
        }

        return redirect()->away($checkoutSession->redirectUrl);
    }
}
