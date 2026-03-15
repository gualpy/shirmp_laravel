<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Application\Services\TenantOnboardingService;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Multitenancy\TenantScopeBypass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class TenantOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_open_tenant_onboarding_form(): void
    {
        $superAdmin = $this->superAdmin();
        $this->seedPlan('starter');

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/tenants/create')
            ->assertOk()
            ->assertSee('Onboarding de Tenant')
            ->assertSee('Admin user');
    }

    public function test_superadmin_can_create_tenant_with_admin_and_subscription(): void
    {
        $superAdmin = $this->superAdmin();
        $plan = $this->seedPlan('starter');

        $this->actingAs($superAdmin)
            ->post('/backoffice/admin/tenants', [
                'name' => 'Cliente Uno',
                'slug' => 'cliente-uno',
                'company_display_name' => 'Acuicola Cliente Uno',
                'admin_name' => 'Owner Cliente Uno',
                'admin_email' => 'owner@clienteuno.test',
                'admin_password' => 'password123',
                'plan_id' => $plan->id,
                'subscription_status' => 'active',
                'starts_at' => '2026-03-14 10:00:00',
            ])
            ->assertRedirect();

        $tenant = Tenant::query()->where('slug', 'cliente-uno')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'owner@clienteuno.test',
            'role' => UserRole::OWNER->value,
        ]);
        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'tenant.created',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'tenant.admin_user_created',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'subscription.created',
        ]);
    }

    public function test_onboarding_can_create_initial_farm_and_ponds(): void
    {
        $superAdmin = $this->superAdmin();
        $plan = $this->seedPlan('pro');

        $this->actingAs($superAdmin)
            ->post('/backoffice/admin/tenants', [
                'name' => 'Cliente Dos',
                'slug' => 'cliente-dos',
                'admin_name' => 'Owner Cliente Dos',
                'admin_email' => 'owner@clientedos.test',
                'admin_password' => 'password123',
                'plan_id' => $plan->id,
                'subscription_status' => 'trial',
                'starts_at' => '2026-03-14 10:00:00',
                'ends_at' => '2026-03-28 10:00:00',
                'create_initial_farm' => '1',
                'farm_name' => 'Farm Inicial',
                'create_ponds' => '1',
                'pond_count' => 3,
            ])
            ->assertRedirect();

        $tenant = Tenant::query()->where('slug', 'cliente-dos')->firstOrFail();
        $farm = Farm::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();

        $this->assertNotNull($farm);
        $this->assertSame('Farm Inicial', $farm->name);
        $this->assertSame(3, Pond::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'farm.created',
        ]);
        $this->assertDatabaseCount('audit_logs', 7);
    }

    public function test_onboarding_rolls_back_if_creation_fails(): void
    {
        $plan = $this->seedPlan('onprem', PlanBillingType::ONPREM);

        try {
            app(TenantOnboardingService::class)->onboard([
                'name' => 'Cliente Fallido',
                'slug' => 'cliente-fallido',
                'admin_name' => 'Owner Fallido',
                'admin_email' => 'owner@fallido.test',
                'admin_password' => 'password123',
                'plan_id' => $plan->id,
                'subscription_status' => 'active',
                'starts_at' => '2026-03-14 10:00:00',
            ]);
            $this->fail('Expected onboarding to fail due to missing onprem license key.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertDatabaseMissing('tenants', ['slug' => 'cliente-fallido']);
        }
    }

    public function test_non_superadmin_cannot_access_onboarding(): void
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
            ->get('/backoffice/admin/tenants/create')
            ->assertStatus(403);
    }

    public function test_tenant_slug_must_be_unique(): void
    {
        $superAdmin = $this->superAdmin();
        $plan = $this->seedPlan('starter');
        Tenant::factory()->create(['slug' => 'tenant-dup', 'name' => 'Existing']);

        $this->actingAs($superAdmin)
            ->from('/backoffice/admin/tenants/create')
            ->post('/backoffice/admin/tenants', [
                'name' => 'Cliente Duplicado',
                'slug' => 'tenant-dup',
                'admin_name' => 'Owner Duplicado',
                'admin_email' => 'owner@dup.test',
                'admin_password' => 'password123',
                'plan_id' => $plan->id,
                'subscription_status' => 'active',
                'starts_at' => '2026-03-14 10:00:00',
            ])
            ->assertRedirect('/backoffice/admin/tenants/create')
            ->assertSessionHasErrors('slug');
    }

    private function seedPlan(string $code, PlanBillingType $billingType = PlanBillingType::MONTHLY): Plan
    {
        return Plan::query()->create([
            'code' => $code,
            'name' => strtoupper($code),
            'billing_type' => $billingType->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);
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
}
