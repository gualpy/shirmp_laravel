<?php

namespace App\Modules\Billing\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Gateways\StripeGateway;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Enums\BillingPaymentStatus;
use App\Modules\SaaS\Application\Services\SubscriptionActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        StripeGateway $gateway,
        BillingService $billingService,
        SubscriptionActivationService $activationService,
    ): JsonResponse {
        if (! $gateway->verifyWebhookSignature($request)) {
            return new JsonResponse(['message' => 'Invalid signature.'], 400);
        }

        $event = $gateway->parseWebhookEvent($request);

        if ($event->sessionId === null) {
            return new JsonResponse(['message' => 'ignored'], 200);
        }

        $payment = $billingService->findPendingPaymentBySessionId($event->sessionId);

        if ($payment === null) {
            return new JsonResponse(['message' => 'unknown session'], 200);
        }

        if ($event->type === 'payment_succeeded' && $payment->status === BillingPaymentStatus::PENDING) {
            $completed = $billingService->completePendingPayment($payment, $event->providerReference);
            $activationService->activateFromPayment($completed);
        } elseif ($event->type === 'payment_failed') {
            $billingService->markPendingPaymentFailed($payment);
        }

        return new JsonResponse(['message' => 'ok'], 200);
    }
}
