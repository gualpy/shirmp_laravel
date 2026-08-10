<?php

namespace App\Modules\Billing\Application\Services;

use App\Modules\Billing\Application\DTO\CheckoutSession;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\SaaS\Domain\Models\Plan;

final class BillingCheckoutService
{
    public function __construct(
        private readonly PaymentGatewayFactory $gatewayFactory,
        private readonly BillingService $billingService,
    ) {
    }

    public function start(
        BillingInvoice $invoice,
        Plan $plan,
        BillingPaymentProvider $provider,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutSession {
        $checkoutSession = $this->gatewayFactory->make($provider)
            ->createCheckoutSession($invoice, $plan, $successUrl, $cancelUrl);

        $this->billingService->createPendingPayment($invoice, $provider, $checkoutSession->sessionId);

        return $checkoutSession;
    }
}
