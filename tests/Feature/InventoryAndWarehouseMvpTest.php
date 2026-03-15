<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class InventoryAndWarehouseMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_can_be_created(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/warehouses', [
                'name' => 'Bodega Principal',
                'location' => 'Zona norte',
            ])
            ->assertRedirect('/backoffice/warehouses');

        $this->assertDatabaseHas('warehouses', [
            'tenant_id' => $tenant->id,
            'name' => 'Bodega Principal',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'warehouse.created',
        ]);
    }

    public function test_inventory_item_can_be_created(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $warehouse = Warehouse::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Bodega Principal',
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/inventory', [
                'warehouse_id' => $warehouse->id,
                'category' => 'feed',
                'name' => 'Alimento 35%',
                'unit' => 'kg',
                'current_stock' => 100,
                'min_stock' => 20,
                'cost_per_unit' => 1.25,
            ])
            ->assertRedirect('/backoffice/inventory');

        $this->assertDatabaseHas('inventory_items', [
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'name' => 'Alimento 35%',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'inventory_item.created',
        ]);
    }

    public function test_inventory_movement_increases_stock(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        [$warehouse, $item] = $this->warehouseItem($tenant, 10);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/inventory/'.$item->id.'/movements', [
                'movement_type' => 'in',
                'quantity' => 5,
                'occurred_at' => now()->toDateTimeString(),
                'reference_type' => 'purchase',
            ])
            ->assertRedirect('/backoffice/inventory/'.$item->id);

        $this->assertSame('15.00', $item->fresh()->current_stock);
    }

    public function test_inventory_movement_out_decreases_stock(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        [$warehouse, $item] = $this->warehouseItem($tenant, 10);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/inventory/'.$item->id.'/movements', [
                'movement_type' => 'out',
                'quantity' => 4,
                'occurred_at' => now()->toDateTimeString(),
                'reference_type' => 'manual',
            ])
            ->assertRedirect('/backoffice/inventory/'.$item->id);

        $this->assertSame('6.00', $item->fresh()->current_stock);
    }

    public function test_stock_cannot_go_negative(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        [$warehouse, $item] = $this->warehouseItem($tenant, 2);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->from('/backoffice/inventory/'.$item->id)
            ->post('/backoffice/inventory/'.$item->id.'/movements', [
                'movement_type' => 'out',
                'quantity' => 3,
                'occurred_at' => now()->toDateTimeString(),
                'reference_type' => 'manual',
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame('2.00', $item->fresh()->current_stock);
    }

    public function test_tenant_isolation_for_inventory(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);
        [, $itemA] = $this->warehouseItem($tenantA, 10);

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/inventory')
            ->assertOk()
            ->assertDontSee('Alimento 35%');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/inventory/'.$itemA->id)
            ->assertNotFound();
    }

    public function test_inventory_pages_load(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        [$warehouse, $item] = $this->warehouseItem($tenant, 10);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/warehouses')
            ->assertOk()
            ->assertSee('Bodegas');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/inventory')
            ->assertOk()
            ->assertSee('Inventario')
            ->assertSee('Alimento 35%');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/inventory/'.$item->id)
            ->assertOk()
            ->assertSee('Registrar movimiento');
    }

    /** @return array{Tenant, User} */
    private function tenantUser(string $slug, string $email): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug, 'name' => strtoupper($slug)]);
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => UserRole::OWNER->value,
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

    /** @return array{Warehouse, InventoryItem} */
    private function warehouseItem(Tenant $tenant, float $stock): array
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
            'current_stock' => $stock,
            'min_stock' => 3,
            'cost_per_unit' => 1.25,
            'is_active' => true,
        ]);

        return [$warehouse, $item];
    }
}
