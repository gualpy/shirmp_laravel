<?php

namespace App\Modules\SaaS\Application\Services;

use App\Models\Tenant;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Enums\VerificationSource;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

final class LicenseService
{
    public function __construct(private readonly SaaSService $saasService)
    {
    }

    public function verifySubscriptionNow(Tenant $tenant, VerificationSource $source = VerificationSource::MANUAL): ?TenantSubscription
    {
        $subscription = $this->saasService->currentSubscription($tenant);
        if ($subscription === null) {
            return null;
        }

        $subscription->last_verified_at = now();
        $subscription->verification_source = $source;
        $subscription->save();

        return $subscription->refresh();
    }

    public function isWithinOfflineGrace(Tenant $tenant): bool
    {
        $subscription = $this->saasService->currentSubscription($tenant);
        if ($subscription === null) {
            return false;
        }

        if (! $subscription->offline_mode_enabled) {
            return false;
        }

        if ($subscription->last_verified_at === null) {
            return false;
        }

        return now()->lessThanOrEqualTo(
            $subscription->last_verified_at->copy()->addDays((int) $subscription->offline_grace_days)
        );
    }

    /**
     * @return array{read_only_mode: bool, subscription: TenantSubscription|null}
     */
    public function requireActiveOrGrace(Tenant $tenant): array
    {
        $subscription = $this->saasService->currentSubscription($tenant);

        if ($subscription === null) {
            return ['read_only_mode' => false, 'subscription' => null];
        }

        if (in_array($subscription->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL], true)) {
            $this->ensureOnPremLicenseRequirements($subscription->loadMissing('plan'));
            return ['read_only_mode' => false, 'subscription' => $subscription];
        }

        if ($subscription->offline_mode_enabled && $this->isWithinOfflineGrace($tenant)) {
            return ['read_only_mode' => true, 'subscription' => $subscription];
        }

        throw new HttpResponseException(new JsonResponse([
            'message' => 'Tenant subscription is not active.',
        ], 403));
    }

    public function ensureOnPremLicenseRequirements(TenantSubscription $subscription): void
    {
        if ($subscription->plan?->billing_type !== PlanBillingType::ONPREM) {
            return;
        }

        if (empty($subscription->license_key)) {
            throw new HttpResponseException(new JsonResponse([
                'message' => 'license_key is required for onprem plans.',
            ], 422));
        }
    }
}
