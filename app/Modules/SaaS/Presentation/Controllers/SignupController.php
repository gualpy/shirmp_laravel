<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Application\Services\PaymentGatewayFactory;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\SaaS\Application\Services\TenantOnboardingService;
use App\Modules\SaaS\Presentation\Requests\StoreSignupRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

final class SignupController extends Controller
{
    public function create(TenantOnboardingService $onboardingService): View
    {
        return view('public.signup', [
            'plans' => $onboardingService->signupPlans(),
        ]);
    }

    public function store(
        StoreSignupRequest $request,
        TenantOnboardingService $onboardingService,
        PaymentGatewayFactory $gatewayFactory,
        BillingService $billingService,
    ): RedirectResponse {
        $result = $onboardingService->onboardPending($request->validated());
        $provider = BillingPaymentProvider::from((string) $request->string('payment_provider'));

        $successUrl = route('signup.pending', ['tenant' => $result['tenant']->slug]);
        $cancelUrl = route('signup.create');

        try {
            $checkoutSession = $gatewayFactory->make($provider)
                ->createCheckoutSession($result['invoice'], $result['plan'], $successUrl, $cancelUrl);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['payment_provider' => __('signup.checkout_error')])
                ->withInput($request->except('admin_password', 'admin_password_confirmation'));
        }

        $billingService->createPendingPayment($result['invoice'], $provider, $checkoutSession->sessionId);

        return redirect()->away($checkoutSession->redirectUrl);
    }
}
