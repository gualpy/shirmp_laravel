<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
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
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ExtendedXlsxExportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cycle_costs_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-costs', 'owner-costs@a.local', UserRole::OWNER);
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'CX-1');

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed Export',
            'cost_per_kg' => 2.15,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-03-10',
            'amount_kg' => 80,
            'notes' => 'AM',
        ]);

        OperationalCostEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'energy',
            'amount' => 45,
            'occurred_at' => '2026-03-11',
            'notes' => 'Motor',
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/costs.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_tenant_billing_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-billing', 'owner-billing@a.local', UserRole::OWNER);
        $this->activeSubscription($tenant);

        app(BillingService::class)->createInvoiceForSubscription($tenant, $tenant->subscriptions()->first(), [
            'billing_period_start' => '2026-03-01',
            'billing_period_end' => '2026-03-31',
            'amount_usd' => 120,
            'due_at' => now()->addDays(5),
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/billing/export.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_inventory_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-inventory', 'owner-inventory@a.local', UserRole::OWNER);
        $this->activeSubscription($tenant);
        $this->warehouseItem($tenant, 10);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/inventory/export.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_inventory_movements_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-inv-mov', 'owner-inv-mov@a.local', UserRole::OWNER);
        $this->activeSubscription($tenant);
        [, $item] = $this->warehouseItem($tenant, 10);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/inventory/'.$item->id.'/movements', [
                'movement_type' => 'in',
                'quantity' => 5,
                'occurred_at' => now()->toDateTimeString(),
                'reference_type' => 'purchase',
                'notes' => 'Lote nuevo',
            ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/inventory/'.$item->id.'/movements.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_alerts_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-alerts', 'owner-alerts@a.local', UserRole::OWNER);
        $this->activeSubscription($tenant);
        $alert = $this->makeAlert($tenant, 'critical', 'LOW_GROWTH', 'Export alert');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/alerts/export.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_tenant_isolation_for_extended_exports(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-iso-a', 'owner-iso-a@a.local', UserRole::OWNER);
        [$tenantB, $userB] = $this->tenantUser('tenant-iso-b', 'owner-iso-b@a.local', UserRole::OWNER);
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $cycleA = $this->makeCycle($tenantA, 'ISO-1');
        [, $itemA] = $this->warehouseItem($tenantA, 8);

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/cycles/'.$cycleA->id.'/exports/costs.xlsx')
            ->assertNotFound();

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/inventory/'.$itemA->id.'/movements.xlsx')
            ->assertNotFound();
    }

    public function test_permission_blocks_sensitive_exports_when_role_forbidden(): void
    {
        [$tenantA, $productionUser] = $this->tenantUser('tenant-prod-role', 'prod-role@a.local', UserRole::PRODUCTION);
        [$tenantB, $financeUser] = $this->tenantUser('tenant-fin-role', 'fin-role@a.local', UserRole::FINANCE);
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);
        [, $itemB] = $this->warehouseItem($tenantB, 6);

        $this->actingAs($productionUser)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->get('/backoffice/billing/export.xlsx')
            ->assertStatus(403);

        $this->actingAs($financeUser)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/inventory/export.xlsx')
            ->assertStatus(403);

        $this->actingAs($financeUser)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/inventory/'.$itemB->id.'/movements.xlsx')
            ->assertStatus(403);
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
        $plan->features()->create(['feature_key' => 'cost_engine', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'water_quality', 'is_enabled' => true]);

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

    private function makeCycle(Tenant $tenant, string $pondCode): Cycle
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$pondCode,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => $pondCode,
            'area_ha' => 3.2,
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
            'density_pl_ha' => round(100000 / 3.2, 2),
            'density_pl_m2' => round(100000 / (3.2 * 10000), 4),
        ]);

        return $cycle;
    }

    private function makeAlert(Tenant $tenant, string $severity, string $ruleCode, string $message): AlertEvent
    {
        $farm = Farm::query()->create(['tenant_id' => $tenant->id, 'name' => 'Farm '.$tenant->slug]);
        $cycle = $this->makeCycle($tenant, strtoupper(substr($tenant->slug, 0, 3)).'-A');

        return AlertEvent::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'cycle_id' => $cycle->id,
            'rule_code' => $ruleCode,
            'severity' => $severity,
            'title' => $ruleCode,
            'message' => $message,
            'detected_at' => now()->subHour(),
            'context_json' => ['source' => 'test'],
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
