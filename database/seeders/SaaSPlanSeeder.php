<?php

namespace Database\Seeders;

use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SaaSPlanSeeder extends Seeder
{
    public function run(): void
    {
        $starter = $this->upsertPlan(
            code: 'starter',
            name: 'Starter',
            billingType: PlanBillingType::MONTHLY,
            priceUsd: 49.00,
            limits: [
                'max_farms' => 1,
                'max_cycles_active' => 4,
                'max_users' => 3,
            ],
            features: [
                'dashboard' => true,
                'alerts' => false,
                'cost_engine' => false,
                'water_quality' => false,
            ],
        );

        $pro = $this->upsertPlan(
            code: 'pro',
            name: 'Pro',
            billingType: PlanBillingType::MONTHLY,
            priceUsd: 149.00,
            limits: [
                'max_farms' => 5,
                'max_cycles_active' => 15,
                'max_users' => 10,
            ],
            features: [
                'dashboard' => true,
                'alerts' => true,
                'cost_engine' => true,
                'water_quality' => true,
                'advanced_reports' => true,
                'api_access' => true,
                'export_excel' => true,
                'export_pdf' => true,
            ],
        );

        $this->upsertPlan(
            code: 'enterprise',
            name: 'Enterprise',
            billingType: PlanBillingType::YEARLY,
            priceUsd: null,
            limits: [
                'max_farms' => null,
                'max_cycles_active' => null,
                'max_users' => null,
                'max_ponds' => null,
                'max_storage_mb' => null,
            ],
            features: [
                'dashboard' => true,
                'alerts' => true,
                'cost_engine' => true,
                'water_quality' => true,
                'advanced_reports' => true,
                'api_access' => true,
                'export_excel' => true,
                'export_pdf' => true,
            ],
        );

        $this->upsertPlan(
            code: 'onprem',
            name: 'OnPrem',
            billingType: PlanBillingType::ONPREM,
            priceUsd: null,
            limits: [
                'max_farms' => null,
                'max_cycles_active' => null,
                'max_users' => null,
                'max_ponds' => null,
                'max_storage_mb' => null,
            ],
            features: [
                'dashboard' => true,
                'alerts' => true,
                'cost_engine' => true,
                'water_quality' => true,
                'advanced_reports' => true,
                'api_access' => true,
                'export_excel' => true,
                'export_pdf' => true,
            ],
        );

        $this->assignSeedSubscription('demo', $starter, SubscriptionStatus::TRIAL);
        $this->assignSeedSubscription('tenant-a', $pro, SubscriptionStatus::ACTIVE);
    }

    /**
     * @param  array<string, int|null>  $limits
     * @param  array<string, bool>  $features
     */
    private function upsertPlan(
        string $code,
        string $name,
        PlanBillingType $billingType,
        ?float $priceUsd,
        array $limits,
        array $features,
    ): Plan {
        $plan = Plan::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'billing_type' => $billingType->value,
                'price_usd' => $priceUsd,
                'is_active' => true,
            ],
        );

        foreach ($limits as $key => $value) {
            $plan->limits()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        foreach ($features as $featureKey => $enabled) {
            $plan->features()->updateOrCreate(
                ['feature_key' => $featureKey],
                ['is_enabled' => $enabled],
            );
        }

        return $plan;
    }

    private function assignSeedSubscription(string $tenantSlug, Plan $plan, SubscriptionStatus $status): void
    {
        $tenant = Tenant::query()->where('slug', $tenantSlug)->first();

        if ($tenant === null) {
            return;
        }

        TenantSubscription::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plan_id' => $plan->id,
                'status' => $status->value,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
                'offline_mode_enabled' => false,
                'offline_grace_days' => 7,
                'verification_source' => 'cloud',
                'last_verified_at' => now(),
            ],
        );
    }
}
