<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Application\Services\PaymentGatewayFactory;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Enums\BillingPaymentStatus;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

final class BackofficeBillingCheckoutReturnController extends Controller
{
    public function __invoke(
        Request $request,
        int $invoiceId,
        BillingService $billingService,
        PaymentGatewayFactory $gatewayFactory,
    ): RedirectResponse {
        $invoice = BillingInvoice::query()->findOrFail($invoiceId);

        // Stripe appends ?session_id=..., PayPal appends ?token=<order_id>.
        $sessionId = (string) ($request->query('token') ?? $request->query('session_id') ?? '');

        $payment = $sessionId !== '' ? $billingService->findPendingPaymentBySessionId($sessionId) : null;

        if ($payment === null || $payment->invoice_id !== $invoice->id) {
            return redirect()->route('backoffice.billing.index')
                ->withErrors(['checkout' => __('billing.checkout_pending')]);
        }

        if ($payment->status !== BillingPaymentStatus::PENDING) {
            return redirect()->route('backoffice.billing.index')->with('status', __('billing.checkout_success'));
        }

        $provider = $payment->provider instanceof BillingPaymentProvider
            ? $payment->provider
            : BillingPaymentProvider::from($payment->provider);

        try {
            $confirmed = $gatewayFactory->make($provider)->finalizeReturn($request, $payment);

            if ($confirmed) {
                $billingService->completePendingPayment($payment, $payment->provider_session_id);

                return redirect()->route('backoffice.billing.index')->with('status', __('billing.checkout_success'));
            }
        } catch (Throwable $e) {
            report($e);
        }

        return redirect()->route('backoffice.billing.index')
            ->withErrors(['checkout' => __('billing.checkout_pending')]);
    }
}
