<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingCheckoutService;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use Illuminate\Http\RedirectResponse;
use Throwable;

final class BackofficeBillingCheckoutStartController extends Controller
{
    public function __invoke(
        int $invoiceId,
        BillingCheckoutService $checkoutService,
    ): RedirectResponse {
        $invoice = BillingInvoice::query()->findOrFail($invoiceId);
        $plan = $invoice->subscription?->plan;
        $status = $invoice->status instanceof BillingInvoiceStatus ? $invoice->status : BillingInvoiceStatus::from($invoice->status);

        if ($plan === null || ! in_array($status, [BillingInvoiceStatus::PENDING, BillingInvoiceStatus::OVERDUE], true)) {
            return redirect()->route('backoffice.billing.index')
                ->withErrors(['checkout' => __('billing.checkout_not_payable')]);
        }

        $successUrl = route('backoffice.billing.checkout.return', ['invoiceId' => $invoice->id]);
        $cancelUrl = route('backoffice.billing.index');

        try {
            $checkoutSession = $checkoutService->start($invoice, $plan, BillingPaymentProvider::PAYPAL, $successUrl, $cancelUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('backoffice.billing.index')
                ->withErrors(['checkout' => __('billing.checkout_error')]);
        }

        return redirect()->away($checkoutSession->redirectUrl);
    }
}
