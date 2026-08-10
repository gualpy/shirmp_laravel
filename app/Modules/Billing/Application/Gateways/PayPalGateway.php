<?php

namespace App\Modules\Billing\Application\Gateways;

use App\Modules\Billing\Application\Contracts\PaymentGatewayInterface;
use App\Modules\Billing\Application\DTO\CheckoutSession;
use App\Modules\Billing\Application\DTO\WebhookEvent;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * PayPal Orders v2 REST API, no SDK dependency. Orders are created with
 * intent=CAPTURE; the buyer approves via redirect, then finalizeReturn()
 * performs the actual capture call when they land back on our success page.
 */
final class PayPalGateway implements PaymentGatewayInterface
{
    public function createCheckoutSession(BillingInvoice $invoice, Plan $plan, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $invoice->invoice_number,
                    'custom_id' => (string) $invoice->id,
                    'description' => sprintf('Plan %s - %s', $plan->name, $invoice->invoice_number),
                    'amount' => [
                        'currency_code' => strtoupper($invoice->currency),
                        'value' => number_format((float) $invoice->amount_usd, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name' => 'ShrimpApp',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ])
            ->throw();

        $data = $response->json();
        $approveUrl = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        if ($approveUrl === null) {
            throw new RuntimeException('PayPal order response did not include an approve link.');
        }

        return new CheckoutSession(sessionId: (string) $data['id'], redirectUrl: (string) $approveUrl);
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $webhookId = (string) config('services.paypal.webhook_id');

        if ($webhookId === '') {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post($this->baseUrl().'/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => $request->header('PAYPAL-CERT-URL'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $webhookId,
                'webhook_event' => $request->json()->all(),
            ]);

        return $response->successful() && ($response->json('verification_status') === 'SUCCESS');
    }

    public function parseWebhookEvent(Request $request): WebhookEvent
    {
        $payload = (array) $request->json()->all();
        $eventType = (string) ($payload['event_type'] ?? '');
        $resource = (array) ($payload['resource'] ?? []);

        $type = match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.APPROVED' => 'payment_succeeded',
            'PAYMENT.CAPTURE.DENIED', 'CHECKOUT.ORDER.VOIDED' => 'payment_failed',
            default => 'other',
        };

        $orderId = $resource['supplementary_data']['related_ids']['order_id']
            ?? $resource['id']
            ?? null;

        return new WebhookEvent(
            type: $type,
            sessionId: $orderId !== null ? (string) $orderId : null,
            providerReference: isset($resource['id']) ? (string) $resource['id'] : null,
        );
    }

    public function finalizeReturn(Request $request, BillingPayment $payment): bool
    {
        $orderId = $payment->provider_session_id;

        if ($orderId === null) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->withBody('{}', 'application/json')
            ->post($this->baseUrl()."/v2/checkout/orders/{$orderId}/capture")
            ->throw();

        return $response->json('status') === 'COMPLETED';
    }

    private function accessToken(): string
    {
        $clientId = (string) config('services.paypal.client_id');
        $secret = (string) config('services.paypal.client_secret');

        if ($clientId === '' || $secret === '') {
            throw new RuntimeException('PAYPAL_CLIENT_ID / PAYPAL_CLIENT_SECRET are not configured.');
        }

        return Cache::remember('paypal_access_token', 270, function () use ($clientId, $secret): string {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $secret)
                ->post($this->baseUrl().'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw();

            return (string) $response->json('access_token');
        });
    }

    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
