<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Multitenancy\TenantScopeBypass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class RolePermissionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_role_can_access_production_modules(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'prod@a.local', UserRole::PRODUCTION);
        $this->activeSubscription($tenant);
        $cycle = $this->seedCycle($tenant);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles')
            ->assertOk();

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id)
            ->assertOk();

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/water')
            ->assertOk();
    }

    public function test_production_role_cannot_access_billing(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'prod@a.local', UserRole::PRODUCTION);
        $this->activeSubscription($tenant);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/billing')
            ->assertStatus(403);
    }

    public function test_inventory_role_can_access_inventory(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'inventory@a.local', UserRole::INVENTORY);
        $this->activeSubscription($tenant);
        [, $item] = $this->seedInventory($tenant);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/warehouses')
            ->assertOk();

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/inventory')
            ->assertOk();

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/inventory/'.$item->id)
            ->assertOk();
    }

    public function test_finance_role_can_access_billing_and_costs(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'finance@a.local', UserRole::FINANCE);
        $this->activeSubscription($tenant);
        $cycle = $this->seedCycle($tenant);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/billing')
            ->assertOk();

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/costs')
            ->assertOk();
    }

    public function test_readonly_cannot_post_mutations(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'readonly@a.local', UserRole::READ_ONLY);
        $this->activeSubscription($tenant);
        $cycle = $this->seedCycle($tenant);
        [, $item] = $this->seedInventory($tenant);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/water', [
                'pond' => $cycle->pond_id,
                'measured_at' => now()->toDateTimeString(),
                'do' => 5.5,
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/cycles/'.$cycle->id.'/costs', [
                'cost_type' => 'labor',
                'amount' => 40,
                'occurred_at' => now()->format('Y-m-d'),
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/cycles/'.$cycle->id.'/mortalities', [
                'pond_id' => $cycle->pond_id,
                'recorded_at' => now()->format('Y-m-d'),
                'mortality_count' => 25,
            ])
            ->assertStatus(403);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/inventory/'.$item->id.'/movements', [
                'movement_type' => 'out',
                'quantity' => 2,
                'occurred_at' => now()->toDateTimeString(),
                'reference_type' => 'manual',
            ])
            ->assertStatus(403);
    }

    public function test_non_superadmin_cannot_access_admin(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local', UserRole::OWNER);
        $this->activeSubscription($tenant);

        $this->actingAs($user)
            ->get('/backoffice/admin')
            ->assertStatus(403);
    }

    public function test_menu_visibility_changes_by_role(): void
    {
        [$tenantA, $productionUser] = $this->tenantUser('tenant-a', 'prod@a.local', UserRole::PRODUCTION);
        [$tenantB, $financeUser] = $this->tenantUser('tenant-b', 'finance@b.local', UserRole::FINANCE);
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $this->actingAs($productionUser)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->get('/backoffice')
            ->assertOk()
            ->assertSee('Cycles')
            ->assertSee('Alerts')
            ->assertSee('Water')
            ->assertDontSee('Billing')
            ->assertDontSee('Inventory')
            ->assertDontSee('Audit');

        $this->actingAs($financeUser)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice')
            ->assertOk()
            ->assertSee('Billing')
            ->assertDontSee('Alerts')
            ->assertDontSee('Inventory');
    }

    /** @return array{Tenant, User} */
    private function tenantUser(string $slug, string $email, UserRole $role): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug, 'name' => strtoupper($slug)]);
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => $role->value,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role->value,
        ]);

        return [$tenant, $user];
    }

    private function activeSubscription(Tenant $tenant): void
    {
        $plan = Plan::query()->create([
            'code' => 'pro-'.$tenant->slug,
            'name' => 'PRO '.strtoupper($tenant->slug),
            'billing_type' => PlanBillingType::MONTHLY->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);
        $plan->features()->create(['feature_key' => 'dashboard', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'alerts', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'water_quality', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'cost_engine', 'is_enabled' => true]);

        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
            'offline_mode_enabled' => false,
            'offline_grace_days' => 7,
            'verification_source' => 'cloud',
        ]);
    }

    private function seedCycle(Tenant $tenant): Cycle
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$tenant->slug,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-01',
            'area_ha' => 2.5,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => '2026-02-01',
        ]);

        Stocking::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'stocked_at' => '2026-02-02',
            'pl_qty' => 100000,
            'density_pl_ha' => 40000,
            'density_pl_m2' => 4,
        ]);

        return $cycle;
    }

    /** @return array{Warehouse, InventoryItem} */
    private function seedInventory(Tenant $tenant): array
    {
        $warehouse = Warehouse::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Bodega Principal',
        ]);

        $item = InventoryItem::query()->create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'category' => 'feed',
            'name' => 'Alimento 35%',
            'unit' => 'kg',
            'current_stock' => 20,
            'min_stock' => 5,
            'cost_per_unit' => 1.2,
            'is_active' => true,
        ]);

        return [$warehouse, $item];
    }
}
