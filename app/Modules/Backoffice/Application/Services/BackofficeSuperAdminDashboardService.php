<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\Tenant;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\TenantSubscription;

final class BackofficeSuperAdminDashboardService
{
    public function __construct(
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
    )
    {
    }

    /** @return array<string, int> */
    public function build(): array
    {
        $tenantIdsInReadOnly = Tenant::query()
            ->pluck('id')
            ->filter(function (int $tenantId): bool {
                $tenant = Tenant::query()->find($tenantId);
                if ($tenant === null) {
                    return false;
                }

                try {
                    return (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'];
                } catch (\Throwable) {
                    return false;
                }
            })
            ->count();

        return [
            'total_tenants' => Tenant::query()->count(),
            'active_subscriptions' => TenantSubscription::query()->where('status', SubscriptionStatus::ACTIVE->value)->count(),
            'trial_subscriptions' => TenantSubscription::query()->where('status', SubscriptionStatus::TRIAL->value)->count(),
            'suspended_subscriptions' => TenantSubscription::query()->where('status', SubscriptionStatus::SUSPENDED->value)->count(),
            'expired_subscriptions' => TenantSubscription::query()->where('status', SubscriptionStatus::EXPIRED->value)->count(),
            'onprem_tenants' => TenantSubscription::query()->whereHas('plan', fn ($q) => $q->where('billing_type', PlanBillingType::ONPREM->value))->count(),
            'read_only_tenants' => $tenantIdsInReadOnly,
        ];
    }
}
