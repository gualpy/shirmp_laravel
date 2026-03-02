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
                'max_cycles_active' => 3,
                'max_users' => 3,
            ],
            features: [
                'dashboard' => true,
                'alerts' => false,
                'cost_engine' => false,
                'water_quality' => false,
            ],
        );

        $this->upsertPlan(
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

        $tenant = Tenant::query()->where('slug', 'demo')->first();
        if ($tenant !== null) {
            TenantSubscription::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $starter->id],
                [
                    'status' => SubscriptionStatus::TRIAL->value,
                    'starts_at' => now()->subDays(5),
                    'ends_at' => now()->addDays(25),
                ],
            );
        }
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
}

