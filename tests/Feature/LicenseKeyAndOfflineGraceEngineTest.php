<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\LicenseActivation;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LicenseKeyAndOfflineGraceEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_subscription_allows_reads_within_offline_grace_but_blocks_writes(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local', UserRole::OWNER);
        $plan = $this->makePlan('pro-a', PlanBillingType::MONTHLY);
        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::EXPIRED->value,
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->subDays(1),
            'offline_mode_enabled' => true,
            'offline_grace_days' => 7,
            'last_verified_at' => now()->subDays(2),
            'verification_source' => 'cloud',
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/farms')
            ->assertOk()
            ->assertHeader('X-Read-Only-Mode', '1');

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/farms', ['name' => 'Should Fail'])
            ->assertStatus(403)
            ->assertHeader('X-Read-Only-Mode', '1')
            ->assertJsonPath('message', 'Tenant is in read-only mode during offline grace period.');
    }

    public function test_expired_subscription_blocks_when_grace_exceeded(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local', UserRole::OWNER);
        $plan = $this->makePlan('pro-b', PlanBillingType::MONTHLY);
        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::EXPIRED->value,
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(20),
            'offline_mode_enabled' => true,
            'offline_grace_days' => 7,
            'last_verified_at' => now()->subDays(15),
            'verification_source' => 'cloud',
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/farms')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Tenant subscription is not active.');
    }

    public function test_onprem_plan_requires_license_key_and_enables_offline_mode(): void
    {
        [$tenant, ] = $this->tenantToken('tenant-a', 'owner@a.local', UserRole::OWNER);
        [, $adminToken] = $this->tenantToken('tenant-a', 'root@local', UserRole::SUPER_ADMIN);
        $plan = $this->makePlan('onprem', PlanBillingType::ONPREM);

        $this->withToken($adminToken)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/admin/tenants/'.$tenant->id.'/activate-onprem', [])
            ->assertStatus(422);

        $response = $this->withToken($adminToken)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/admin/tenants/'.$tenant->id.'/activate-onprem', [
                'license_key' => 'LIC-ABC-123',
                'offline_grace_days' => 15,
                'machine_fingerprint' => 'fingerprint-xyz',
            ])
            ->assertCreated();

        $subscriptionId = (int) $response->json('data.id');

        $this->assertDatabaseHas('tenant_subscriptions', [
            'id' => $subscriptionId,
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'license_key' => 'LIC-ABC-123',
            'offline_mode_enabled' => 1,
            'verification_source' => 'onprem',
            'offline_grace_days' => 15,
        ]);

        $this->assertDatabaseHas('license_activations', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscriptionId,
            'machine_fingerprint' => 'fingerprint-xyz',
        ]);
    }

    public function test_verify_now_updates_last_verified_at(): void
    {
        [$tenant, ] = $this->tenantToken('tenant-a', 'owner@a.local', UserRole::OWNER);
        [, $adminToken] = $this->tenantToken('tenant-a', 'root@local', UserRole::SUPER_ADMIN);
        $plan = $this->makePlan('pro-c', PlanBillingType::MONTHLY);

        $subscription = TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'starts_at' => now()->subDays(5),
            'last_verified_at' => now()->subDays(5),
            'offline_grace_days' => 7,
            'offline_mode_enabled' => false,
            'verification_source' => 'cloud',
        ]);

        $this->withToken($adminToken)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/admin/tenants/'.$tenant->id.'/verify-now')
            ->assertOk()
            ->assertJsonPath('data.id', $subscription->id)
            ->assertJsonPath('data.verification_source', 'manual');

        $subscription->refresh();
        $this->assertTrue($subscription->last_verified_at !== null && $subscription->last_verified_at->gt(now()->subMinute()));
    }

    public function test_tenant_isolation_license_activation(): void
    {
        [$tenantA, ] = $this->tenantToken('tenant-a', 'owner@a.local', UserRole::OWNER);
        [$tenantB, ] = $this->tenantToken('tenant-b', 'owner@b.local', UserRole::OWNER);
        [, $adminToken] = $this->tenantToken('tenant-a', 'root@local', UserRole::SUPER_ADMIN);
        $this->makePlan('onprem', PlanBillingType::ONPREM);

        $this->withToken($adminToken)->withHeader('X-Tenant', $tenantA->slug)
            ->postJson('/api/v1/admin/tenants/'.$tenantA->id.'/activate-onprem', [
                'license_key' => 'TENANT-A-LIC',
            ])
            ->assertCreated();

        $this->assertDatabaseCount('license_activations', 1);
        $this->assertDatabaseHas('license_activations', ['tenant_id' => $tenantA->id]);
        $this->assertDatabaseMissing('license_activations', ['tenant_id' => $tenantB->id]);
    }

    /** @return array{Tenant, string} */
    private function tenantToken(string $slug, string $email, UserRole $role): array
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => strtoupper($slug), 'is_active' => true],
        );
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role->value,
        ]);

        return [$tenant, $user->createToken('test-device')->plainTextToken];
    }

    private function makePlan(string $code, PlanBillingType $billingType): Plan
    {
        $plan = Plan::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => strtoupper($code),
                'billing_type' => $billingType->value,
                'price_usd' => null,
                'is_active' => true,
            ]
        );

        $plan->features()->updateOrCreate(['feature_key' => 'dashboard'], ['is_enabled' => true]);

        return $plan;
    }
}
