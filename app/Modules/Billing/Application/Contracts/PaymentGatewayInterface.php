<?php

namespace App\Modules\Billing\Application\Contracts;

use App\Modules\Billing\Application\DTO\CheckoutSession;
use App\Modules\Billing\Application\DTO\WebhookEvent;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createCheckoutSession(BillingInvoice $invoice, Plan $plan, string $successUrl, string $cancelUrl): CheckoutSession;

    public function verifyWebhookSignature(Request $request): bool;

    public function parseWebhookEvent(Request $request): WebhookEvent;

    /**
     * Called when the buyer is redirected back to the app (successUrl).
     * No-op for providers that auto-capture (Stripe Checkout) -> returns false,
     * the webhook remains the source of truth. PayPal needs an explicit capture
     * call here since Orders v2 only "approves" on redirect; returns true when
     * the capture confirms the payment synchronously so the caller can activate
     * immediately instead of waiting on the webhook.
     */
    public function finalizeReturn(Request $request, BillingPayment $payment): bool;
}
