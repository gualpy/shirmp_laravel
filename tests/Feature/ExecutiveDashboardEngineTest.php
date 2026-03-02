<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ExecutiveDashboardEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_dashboard_returns_expected_structure(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$farm, $cycle] = $this->makeActiveCycle($tenant, 'FA-A1', 3.5, 120000, '2026-01-01');

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-10',
            'pp_grams' => 8.5,
        ]);

        $this->seedCostsAndAlerts($tenant, $cycle, $farm);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertOk()
            ->assertJsonStructure([
                'active_cycles',
                'total_active_area_ha',
                'total_biomass_kg',
                'total_projected_tons',
                'average_fcr',
                'total_feed_kg',
                'total_cost',
                'cost_per_lb_global',
                'critical_alerts',
                'warning_alerts',
                'farms_summary' => [['farm_id', 'farm_name', 'active_cycles', 'biomass_kg', 'alerts']],
            ]);
    }

    public function test_farm_dashboard_returns_expected_structure(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$farm, $cycle] = $this->makeActiveCycle($tenant, 'FA-A2', 4.2, 100000, '2026-01-01');

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-12',
            'pp_grams' => 9.1,
        ]);

        WaterQualityEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'measured_at' => '2026-03-01 07:00:00',
            'dissolved_oxygen_mg_l' => 4.2,
            'ph' => 8.1,
            'temp_c' => 29.5,
        ]);

        $this->seedCostsAndAlerts($tenant, $cycle, $farm);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/farms/'.$farm->id)
            ->assertOk()
            ->assertJsonStructure([
                'active_cycles',
                'total_area_ha',
                'biomass_kg',
                'biomass_kg_per_ha',
                'avg_growth_g_per_week',
                'avg_fcr',
                'total_feed_kg',
                'total_cost',
                'cost_per_lb',
                'water_quality_latest' => ['avg_do', 'avg_ph', 'avg_temp'],
                'alerts' => ['critical', 'warning', 'info'],
                'cycles' => [['cycle_id', 'pond_code', 'biomass_kg', 'latest_pp', 'fcr', 'alerts']],
            ]);
    }

    public function test_dashboard_respects_tenant_isolation(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$farmA, $cycleA] = $this->makeActiveCycle($tenantA, 'FA-A3', 2.5, 90000, '2026-01-01');
        Sampling::query()->create([
            'tenant_id' => $tenantA->id,
            'cycle_id' => $cycleA->id,
            'sampled_at' => '2026-01-10',
            'pp_grams' => 7.5,
        ]);

        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');
        $this->makeActiveCycle($tenantB, 'FB-B1', 3.1, 110000, '2026-01-01');

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertOk()
            ->assertJsonPath('active_cycles', 1);

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/dashboard/farms/'.$farmA->id)
            ->assertStatus(404);
    }

    public function test_dashboard_handles_no_active_cycles(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm Empty',
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertOk()
            ->assertJsonPath('active_cycles', 0)
            ->assertJsonPath('total_feed_kg', 0)
            ->assertJsonPath('total_biomass_kg', 0)
            ->assertJsonPath('average_fcr', null)
            ->assertJsonPath('cost_per_lb_global', null);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/farms/'.$farm->id)
            ->assertOk()
            ->assertJsonPath('active_cycles', 0)
            ->assertJsonPath('biomass_kg', 0)
            ->assertJsonPath('avg_growth_g_per_week', null)
            ->assertJsonPath('avg_fcr', null)
            ->assertJsonPath('cost_per_lb', null);
    }

    public function test_dashboard_aggregates_correct_totals(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$farm, $cycle] = $this->makeActiveCycle($tenant, 'FA-A4', 2.0, 100000, '2026-01-01');

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-10',
            'pp_grams' => 10.0,
        ]);

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed X',
            'cost_per_kg' => 2.0,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-11',
            'amount_kg' => 150,
        ]);

        OperationalCostEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'energy',
            'amount' => 200,
            'occurred_at' => '2026-01-11',
        ]);

        Harvest::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'harvested_at' => '2026-01-25',
            'type' => 'partial',
            'total_lbs' => 1000,
        ]);

        $tenantResponse = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/tenant')
            ->assertOk();

        $tenantResponse->assertJsonPath('active_cycles', 1);
        $this->assertEqualsWithDelta(2.0, (float) $tenantResponse->json('total_active_area_ha'), 0.0001);
        $this->assertEqualsWithDelta(150.0, (float) $tenantResponse->json('total_feed_kg'), 0.0001);
        $this->assertEqualsWithDelta(1000.0, (float) $tenantResponse->json('total_biomass_kg'), 0.0001);
        $this->assertEqualsWithDelta(1.0, (float) $tenantResponse->json('total_projected_tons'), 0.0001);
        $this->assertEqualsWithDelta(500.0, (float) $tenantResponse->json('total_cost'), 0.0001);
        $this->assertEqualsWithDelta(0.5, (float) $tenantResponse->json('cost_per_lb_global'), 0.0001);

        $farmResponse = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/dashboard/farms/'.$farm->id)
            ->assertOk();

        $farmResponse->assertJsonPath('active_cycles', 1);
        $this->assertEqualsWithDelta(2.0, (float) $farmResponse->json('total_area_ha'), 0.0001);
        $this->assertEqualsWithDelta(1000.0, (float) $farmResponse->json('biomass_kg'), 0.0001);
        $this->assertEqualsWithDelta(500.0, (float) $farmResponse->json('biomass_kg_per_ha'), 0.0001);
        $this->assertEqualsWithDelta(150.0, (float) $farmResponse->json('total_feed_kg'), 0.0001);
        $this->assertEqualsWithDelta(500.0, (float) $farmResponse->json('total_cost'), 0.0001);
        $this->assertEqualsWithDelta(0.5, (float) $farmResponse->json('cost_per_lb'), 0.0001);
    }

    private function seedCostsAndAlerts(Tenant $tenant, Cycle $cycle, Farm $farm): void
    {
        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed Base '.uniqid(),
            'cost_per_kg' => 1.5,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-11',
            'amount_kg' => 100,
        ]);

        OperationalCostEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'labor',
            'amount' => 80,
            'occurred_at' => '2026-01-12',
        ]);

        Harvest::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'harvested_at' => '2026-02-01',
            'type' => 'partial',
            'total_lbs' => 300,
        ]);

        AlertEvent::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'cycle_id' => $cycle->id,
            'rule_code' => 'HIGH_FCR',
            'severity' => 'warning',
            'title' => 'Warning',
            'message' => 'Warning',
            'detected_at' => '2026-01-20 10:00:00',
            'is_acknowledged' => false,
        ]);
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

    /** @return array{Farm, Cycle} */
    private function makeActiveCycle(Tenant $tenant, string $pondCode, float $areaHa, int $plQty, string $startedAt): array
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$pondCode,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => $pondCode,
            'area_ha' => $areaHa,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => $startedAt,
        ]);

        Stocking::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'stocked_at' => date('Y-m-d', strtotime($startedAt.' +1 day')),
            'pl_qty' => $plQty,
            'density_pl_ha' => round($plQty / $areaHa, 2),
            'density_pl_m2' => round($plQty / ($areaHa * 10000), 4),
        ]);

        return [$farm, $cycle];
    }
}
