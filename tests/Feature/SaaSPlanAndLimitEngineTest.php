<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class SaaSPlanAndLimitEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_cannot_exceed_farm_limit(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $plan = $this->makePlan('starter-a', ['max_farms' => 1], ['dashboard' => true]);
        $this->attachSubscription($tenant, $plan, SubscriptionStatus::ACTIVE);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/farms', ['name' => 'Farm One'])
            ->assertCreated();

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/farms', ['name' => 'Farm Two'])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Plan limit reached for `max_farms`.');
    }

    public function test_tenant_cannot_exceed_active_cycle_limit(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $plan = $this->makePlan('starter-b', ['max_cycles_active' => 1], ['dashboard' => true]);
        $this->attachSubscription($tenant, $plan, SubscriptionStatus::ACTIVE);

        $farm = Farm::query()->create(['tenant_id' => $tenant->id, 'name' => 'Farm A']);
        $pondA = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-A1',
            'area_ha' => 2.5,
            'is_active' => true,
        ]);
        $pondB = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-A2',
            'area_ha' => 2.8,
            'is_active' => true,
        ]);

        $payload = ['status' => 'active', 'started_at' => '2026-02-01'];

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/ponds/'.$pondA->id.'/cycles', $payload)
            ->assertCreated();

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/ponds/'.$pondB->id.'/cycles', $payload)
            ->assertStatus(403)
            ->assertJsonPath('message', 'Plan limit reached for `max_cycles_active`.');
    }

    public function test_feature_disabled_blocks_endpoint(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $plan = $this->makePlan('starter-c', [], ['dashboard' => false]);
        $this->attachSubscription($tenant, $plan, SubscriptionStatus::ACTIVE);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Feature `dashboard` is not enabled for the current plan.');
    }

    public function test_expired_subscription_blocks_access(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $plan = $this->makePlan('pro-a', [], ['dashboard' => true]);
        $subscription = $this->attachSubscription(
            $tenant,
            $plan,
            SubscriptionStatus::ACTIVE,
            now()->subDays(10),
            now()->subDay()
        );

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/farms')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Tenant subscription is not active.');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::EXPIRED->value,
        ]);
    }

    public function test_enterprise_plan_has_no_limits(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $plan = $this->makePlan('enterprise-a', [
            'max_farms' => null,
            'max_cycles_active' => null,
            'max_users' => null,
        ], [
            'dashboard' => true,
            'alerts' => true,
            'cost_engine' => true,
            'water_quality' => true,
        ]);
        $this->attachSubscription($tenant, $plan, SubscriptionStatus::ACTIVE);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/farms', ['name' => 'Farm One'])
            ->assertCreated();
        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/farms', ['name' => 'Farm Two'])
            ->assertCreated();

        $farms = Farm::query()->where('tenant_id', $tenant->id)->get();
        $pondA = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farms[0]->id,
            'code' => 'EN-1',
            'area_ha' => 3.0,
            'is_active' => true,
        ]);
        $pondB = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farms[1]->id,
            'code' => 'EN-2',
            'area_ha' => 3.1,
            'is_active' => true,
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/ponds/'.$pondA->id.'/cycles', ['status' => 'active', 'started_at' => '2026-02-01'])
            ->assertCreated();
        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/ponds/'.$pondB->id.'/cycles', ['status' => 'active', 'started_at' => '2026-02-02'])
            ->assertCreated();
    }

    public function test_tenant_isolation_saas(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $planEnabled = $this->makePlan('pro-b', [], ['dashboard' => true]);
        $planDisabled = $this->makePlan('starter-d', [], ['dashboard' => false]);

        $this->attachSubscription($tenantA, $planEnabled, SubscriptionStatus::ACTIVE);
        $this->attachSubscription($tenantB, $planDisabled, SubscriptionStatus::ACTIVE);

        $this->withToken($tokenA)->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertOk();

        $this->withToken($tokenB)->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertStatus(403);
    }

    /** @return array{Tenant, string} */
    private function tenantToken(string $slug, string $email): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug, 'name' => strtoupper($slug)]);
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => UserRole::OWNER->value,
        ]);

        return [$tenant, $user->createToken('test-device')->plainTextToken];
    }

    /**
     * @param  array<string, int|null>  $limits
     * @param  array<string, bool>  $features
     */
    private function makePlan(string $code, array $limits, array $features): Plan
    {
        $plan = Plan::query()->create([
            'code' => $code,
            'name' => strtoupper($code),
            'billing_type' => PlanBillingType::MONTHLY->value,
            'price_usd' => 100,
            'is_active' => true,
        ]);

        foreach ($limits as $key => $value) {
            $plan->limits()->create(['key' => $key, 'value' => $value]);
        }

        foreach ($features as $featureKey => $enabled) {
            $plan->features()->create(['feature_key' => $featureKey, 'is_enabled' => $enabled]);
        }

        return $plan;
    }

    private function attachSubscription(
        Tenant $tenant,
        Plan $plan,
        SubscriptionStatus $status,
        ?\DateTimeInterface $startsAt = null,
        ?\DateTimeInterface $endsAt = null,
    ): TenantSubscription {
        return TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => $status->value,
            'starts_at' => $startsAt ?? now()->subDay(),
            'ends_at' => $endsAt,
        ]);
    }
}

