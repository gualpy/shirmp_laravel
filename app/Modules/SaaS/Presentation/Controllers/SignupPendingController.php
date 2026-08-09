<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Application\Services\PaymentGatewayFactory;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Enums\BillingPaymentStatus;
use App\Modules\SaaS\Application\Services\SubscriptionActivationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class SignupPendingController extends Controller
{
    public function __invoke(
        Request $request,
        string $tenant,
        BillingService $billingService,
        PaymentGatewayFactory $gatewayFactory,
        SubscriptionActivationService $activationService,
    ): View {
        $tenantModel = Tenant::query()->where('slug', $tenant)->firstOrFail();

        // Stripe appends ?session_id=..., PayPal appends ?token=<order_id>.
        $sessionId = (string) ($request->query('session_id') ?? $request->query('token') ?? '');

        if ($sessionId !== '') {
            $payment = $billingService->findPendingPaymentBySessionId($sessionId);

            if ($payment !== null && $payment->status === BillingPaymentStatus::PENDING) {
                $provider = $payment->provider instanceof BillingPaymentProvider
                    ? $payment->provider
                    : BillingPaymentProvider::from($payment->provider);

                try {
                    $confirmed = $gatewayFactory->make($provider)->finalizeReturn($request, $payment);

                    if ($confirmed) {
                        $completed = $billingService->completePendingPayment($payment, $payment->provider_session_id);
                        $activationService->activateFromPayment($completed);
                    }
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        return view('public.signup-pending', [
            'tenant' => $tenantModel->refresh(),
        ]);
    }
}
