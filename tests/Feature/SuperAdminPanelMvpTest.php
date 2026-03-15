<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Multitenancy\TenantScopeBypass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class SuperAdminPanelMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_dashboard_loads(): void
    {
        $superAdmin = $this->superAdmin();
        $tenant = $this->tenantWithSubscription('tenant-a', 'Tenant A', 'pro', SubscriptionStatus::ACTIVE, PlanBillingType::MONTHLY);

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin')
            ->assertOk()
            ->assertSee('Superadmin SaaS')
            ->assertSee('Total Tenants')
            ->assertSee('Active');
    }

    public function test_superadmin_tenants_list_loads(): void
    {
        $superAdmin = $this->superAdmin();
        $this->tenantWithSubscription('tenant-a', 'Tenant A', 'starter', SubscriptionStatus::TRIAL, PlanBillingType::MONTHLY);

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/tenants')
            ->assertOk()
            ->assertSee('Tenants')
            ->assertSee('Tenant A')
            ->assertSee('tenant-a');
    }

    public function test_superadmin_tenant_detail_loads(): void
    {
        $superAdmin = $this->superAdmin();
        $tenant = $this->tenantWithSubscription('tenant-a', 'Tenant A', 'enterprise', SubscriptionStatus::ACTIVE, PlanBillingType::ONPREM, [
            'license_key' => 'LIC-ABCDE-12345',
            'last_verified_at' => now()->subDay(),
            'offline_mode_enabled' => true,
            'verification_source' => 'onprem',
        ]);
        $tenant->update([
            'company_display_name' => 'Acuicola Tenant A',
            'company_phone' => '+593000111222',
        ]);

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/tenants/'.$tenant->id)
            ->assertOk()
            ->assertSee('Tenant A')
            ->assertSee('Acuicola Tenant A')
            ->assertSee('enterprise')
            ->assertSee('LIC-••••2345')
            ->assertSee('+593000111222');
    }

    public function test_non_superadmin_cannot_access_superadmin_routes(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'tenant-a', 'name' => 'Tenant A']);
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@a.local',
            'password' => Hash::make('password123'),
            'role' => UserRole::OWNER->value,
        ]);

        $this->actingAs($user)
            ->get('/backoffice/admin')
            ->assertStatus(403);
    }

    public function test_tenant_data_is_displayed_correctly(): void
    {
        $superAdmin = $this->superAdmin();
        $tenant = $this->tenantWithSubscription('tenant-z', 'Zeta Farm', 'pro', SubscriptionStatus::SUSPENDED, PlanBillingType::YEARLY, [
            'ends_at' => now()->addDays(10),
            'last_verified_at' => now()->subHours(5),
            'verification_source' => 'manual',
        ]);
        $tenant->update(['company_display_name' => 'Zeta Branding']);

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/tenants?status=suspended&billing_type=yearly')
            ->assertOk()
            ->assertSee('Zeta Branding')
            ->assertSee('suspended')
            ->assertSee('yearly');

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/plans')
            ->assertOk()
            ->assertSee('Planes SaaS')
            ->assertSee('pro');
    }

    private function superAdmin(): User
    {
        return app(TenantScopeBypass::class)->run(fn (): User => User::withoutGlobalScopes()->create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@example.test',
            'password' => Hash::make('password123'),
            'role' => UserRole::SUPER_ADMIN->value,
        ]));
    }

    private function tenantWithSubscription(
        string $slug,
        string $name,
        string $planCode,
        SubscriptionStatus $status,
        PlanBillingType $billingType,
        array $subscriptionOverrides = [],
    ): Tenant {
        $tenant = Tenant::factory()->create([
            'slug' => $slug,
            'name' => $name,
        ]);

        $plan = Plan::query()->create([
            'code' => $planCode,
            'name' => strtoupper($planCode),
            'billing_type' => $billingType->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);
        $plan->limits()->create(['key' => 'max_farms', 'value' => 5]);
        $plan->features()->create(['feature_key' => 'dashboard', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'alerts', 'is_enabled' => true]);

        TenantSubscription::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => $status->value,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
            'offline_mode_enabled' => false,
            'offline_grace_days' => 7,
            'verification_source' => 'cloud',
        ], $subscriptionOverrides));

        return $tenant;
    }
}
