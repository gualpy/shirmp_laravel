<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Configuration\Domain\Models\TenantSetting;
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
use App\Modules\Production\Domain\Models\SurvivalEstimate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class HarvestProjectionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_projection_endpoint_returns_expected_structure(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.5, 100000);
        $feedType = $this->attachProjectionData($tenant, $cycle);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-18',
            'amount_kg' => 100,
        ]);

        Harvest::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'harvested_at' => '2026-01-25',
            'type' => 'partial',
            'total_lbs' => 100,
        ]);

        OperationalCostEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'energy',
            'amount' => 45,
            'occurred_at' => '2026-01-20',
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/projection')
            ->assertOk()
            ->assertJsonStructure([
                'current' => [
                    'latest_pp_grams',
                    'latest_survival_pct',
                    'current_biomass_kg',
                    'current_fcr',
                    'current_total_cost',
                ],
                'projection' => [
                    'target_pp_grams',
                    'projected_avg_pp_grams',
                    'projected_harvest_date',
                    'projected_biomass_kg',
                    'projected_total_lbs',
                    'projected_fcr',
                    'projected_revenue',
                    'projected_cost',
                    'projected_profit',
                ],
                'assumptions' => [
                    'sale_price_per_lb',
                    'feed_cost_factor_per_kg_gain',
                    'growth_g_per_week',
                ],
            ]);
    }

    public function test_projection_uses_latest_sampling_and_survival(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.5, 100000);
        $this->attachProjectionData($tenant, $cycle);

        TenantSetting::query()->create([
            'tenant_id' => $tenant->id,
            'default_target_pp_grams' => 20,
            'default_sale_price_per_lb' => 3.50,
            'default_feed_cost_factor_per_kg_gain' => 1.20,
        ]);

        Carbon::setTestNow('2026-03-08 12:00:00');

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/projection')
            ->assertOk();

        $this->assertEqualsWithDelta(11.8, (float) $response->json('current.latest_pp_grams'), 0.001);
        $this->assertEqualsWithDelta(70.0, (float) $response->json('current.latest_survival_pct'), 0.001);
        $this->assertEqualsWithDelta(826.0, (float) $response->json('current.current_biomass_kg'), 0.1);
        $this->assertEqualsWithDelta(1400.0, (float) $response->json('projection.projected_biomass_kg'), 0.1);
        $this->assertEquals('2026-03-24', $response->json('projection.projected_harvest_date'));

        Carbon::setTestNow();
    }

    public function test_projection_returns_nulls_when_data_missing(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.5, 100000);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/projection')
            ->assertOk()
            ->assertJsonPath('current.latest_pp_grams', null)
            ->assertJsonPath('current.latest_survival_pct', null)
            ->assertJsonPath('current.current_biomass_kg', null)
            ->assertJsonPath('projection.projected_harvest_date', null)
            ->assertJsonPath('projection.projected_biomass_kg', null)
            ->assertJsonPath('projection.projected_revenue', null)
            ->assertJsonPath('projection.projected_cost', null)
            ->assertJsonPath('projection.projected_profit', null);
    }

    public function test_projection_respects_tenant_isolation(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $cycle = $this->makeCycle($tenantA, '2026-01-01', 4.5, 100000);
        $this->attachProjectionData($tenantA, $cycle);

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/projection')
            ->assertOk();

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/projection')
            ->assertStatus(404);
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

    private function makeCycle(Tenant $tenant, string $startedAt, float $areaHa, int $plQty): Cycle
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$tenant->slug,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => strtoupper(substr($tenant->slug, 0, 2)).'-PR',
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

        return $cycle;
    }

    private function attachProjectionData(Tenant $tenant, Cycle $cycle): FeedType
    {
        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-02-10',
            'pp_grams' => 8.0,
        ]);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-02-17',
            'pp_grams' => 11.8,
        ]);

        SurvivalEstimate::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'estimated_at' => '2026-02-12',
            'survival_pct' => 60.0,
        ]);

        SurvivalEstimate::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'estimated_at' => '2026-02-18',
            'survival_pct' => 70.0,
        ]);

        return FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Projection Feed',
            'cost_per_kg' => 1.25,
            'is_active' => true,
        ]);
    }
}
