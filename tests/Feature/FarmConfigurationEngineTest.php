<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTableRow;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class FarmConfigurationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_settings_default_applied(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/tenant/settings')
            ->assertOk()
            ->assertJsonPath('feeding_strategy', 'biomass_percentage')
            ->assertJsonPath('feeding_pct_small', 3)
            ->assertJsonPath('feeding_pct_medium', 2.5)
            ->assertJsonPath('feeding_pct_large', 2)
            ->assertJsonPath('unit_system', 'metric')
            ->assertJsonPath('decimals_precision', 2);
    }

    public function test_farm_settings_override_tenant(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $farm = $this->makeFarm($tenant, 'Farm A', 'FA-01');

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->patchJson('/api/v1/tenant/settings', [
                'feeding_pct_small' => 3.4,
                'feeding_pct_medium' => 2.6,
                'feeding_pct_large' => 2.2,
                'decimals_precision' => 3,
            ])
            ->assertOk();

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->patchJson('/api/v1/farms/'.$farm->id.'/settings', [
                'feeding_pct_small' => 4.1,
            ])
            ->assertOk()
            ->assertJsonPath('feeding_pct_small', 4.1)
            ->assertJsonPath('feeding_pct_medium', 2.6)
            ->assertJsonPath('decimals_precision', 3);
    }

    public function test_feeding_recommendation_biomass_strategy(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$cycle] = $this->cycleFixture($tenant, now()->subDays(20)->format('Y-m-d'), 100000, 4.5);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => now()->subDays(1)->format('Y-m-d'),
            'pp_grams' => 8.0,
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->patchJson('/api/v1/tenant/settings', [
                'feeding_strategy' => 'biomass_percentage',
                'feeding_pct_small' => 3.0,
            ])
            ->assertOk();

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/metrics')
            ->assertOk();

        $this->assertSame('biomass_percentage', $response->json('feeding_strategy_used'));
        $this->assertEqualsWithDelta(24.0, (float) $response->json('recommended_feed_kg_per_day'), 0.01);
    }

    public function test_feeding_recommendation_growth_table_strategy(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$cycle, $farm] = $this->cycleFixture($tenant, now()->subDays(20)->format('Y-m-d'), 100000, 4.5);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => now()->subDays(1)->format('Y-m-d'),
            'pp_grams' => 12.0,
        ]);

        $table = FeedingGrowthTable::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'name' => 'Farm A Table',
            'is_active' => true,
        ]);

        FeedingGrowthTableRow::query()->create([
            'tenant_id' => $tenant->id,
            'table_id' => $table->id,
            'day_from' => 1,
            'day_to' => 120,
            'feed_pct' => 2.5,
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->patchJson('/api/v1/tenant/settings', [
                'feeding_strategy' => 'growth_table',
            ])
            ->assertOk();

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/metrics')
            ->assertOk();

        $this->assertSame('growth_table', $response->json('feeding_strategy_used'));
        $this->assertEqualsWithDelta(30.0, (float) $response->json('recommended_feed_kg_per_day'), 0.01);
    }

    public function test_tenant_isolation_settings(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $farmA = $this->makeFarm($tenantA, 'Farm A', 'FA-01');

        $tableA = FeedingGrowthTable::query()->create([
            'tenant_id' => $tenantA->id,
            'farm_id' => $farmA->id,
            'name' => 'A table',
            'is_active' => true,
        ]);

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/farms/'.$farmA->id.'/settings')
            ->assertOk();

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/farms/'.$farmA->id.'/settings')
            ->assertStatus(404);

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->patchJson('/api/v1/feeding-tables/'.$tableA->id, ['name' => 'hacked'])
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

    /** @return array{Cycle, Farm} */
    private function cycleFixture(Tenant $tenant, string $startedAt, int $plQty, float $areaHa): array
    {
        $farm = $this->makeFarm($tenant, 'Farm '.$tenant->slug, strtoupper(substr($tenant->slug, 0, 2)).'-01', $areaHa);

        $pond = Pond::withoutGlobalScopes()->where('farm_id', $farm->id)->firstOrFail();

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

        return [$cycle, $farm];
    }

    private function makeFarm(Tenant $tenant, string $name, string $code, float $areaHa = 4.5): Farm
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
        ]);

        Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => $code,
            'area_ha' => $areaHa,
            'is_active' => true,
        ]);

        return $farm;
    }
}
