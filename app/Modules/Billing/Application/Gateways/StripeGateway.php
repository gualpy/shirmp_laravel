<?php

namespace App\Modules\Billing\Application\Gateways;

use App\Modules\Billing\Application\Contracts\PaymentGatewayInterface;
use App\Modules\Billing\Application\DTO\CheckoutSession;
use App\Modules\Billing\Application\DTO\WebhookEvent;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Talks to Stripe's REST API directly (no stripe/stripe-php dependency).
 * Checkout Session runs in mode=payment (single upfront charge for the
 * plan's first period). Recurring auto-renewal via Stripe Subscriptions
 * would require syncing Plan -> Stripe Price objects; not implemented yet.
 */
final class StripeGateway implements PaymentGatewayInterface
{
    private const API_BASE = 'https://api.stripe.com/v1';

    public function createCheckoutSession(BillingInvoice $invoice, Plan $plan, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('STRIPE_SECRET is not configured.');
        }

        $response = Http::asForm()
            ->withBasicAuth($secret, '')
            ->post(self::API_BASE.'/checkout/sessions', [
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'success_url' => $successUrl.(str_contains($successUrl, '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $invoice->id,
                'metadata' => [
                    'invoice_id' => (string) $invoice->id,
                    'tenant_id' => (string) $invoice->tenant_id,
                ],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($invoice->currency),
                        'unit_amount' => (int) round(((float) $invoice->amount_usd) * 100),
                        'product_data' => [
                            'name' => sprintf('Plan %s - %s', $plan->name, $invoice->invoice_number),
                        ],
                    ],
                ]],
            ])
            ->throw();

        $data = $response->json();

        return new CheckoutSession(sessionId: (string) $data['id'], redirectUrl: (string) $data['url']);
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = (string) config('services.stripe.webhook_secret');
        $header = (string) $request->header('Stripe-Signature', '');

        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if ($timestamp === null || $signatures === []) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    public function parseWebhookEvent(Request $request): WebhookEvent
    {
        $payload = (array) $request->json()->all();
        $eventType = (string) ($payload['type'] ?? '');
        $object = (array) ($payload['data']['object'] ?? []);

        $type = match ($eventType) {
            'checkout.session.completed' => 'payment_succeeded',
            'checkout.session.expired' => 'payment_failed',
            default => 'other',
        };

        return new WebhookEvent(
            type: $type,
            sessionId: isset($object['id']) ? (string) $object['id'] : null,
            providerReference: isset($object['payment_intent']) ? (string) $object['payment_intent'] : null,
        );
    }

    public function finalizeReturn(Request $request, BillingPayment $payment): bool
    {
        // Stripe Checkout (mode=payment) captures at the moment of payment;
        // nothing to do on return, the webhook is the source of truth.
        return false;
    }
}
