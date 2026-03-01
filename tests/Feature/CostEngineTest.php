<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CostEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_feed_cost_correctly(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.5, 100000);

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed 1',
            'cost_per_kg' => 1.2,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 10,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-11',
            'amount_kg' => 5,
        ]);

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/costs')
            ->assertOk();

        $this->assertEqualsWithDelta(18.0, (float) $response->json('totals.feed_cost'), 0.0001);
    }

    public function test_calculates_cost_per_lb_with_harvest(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.0, 100000);

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed 1',
            'cost_per_kg' => 2.0,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 100,
        ]);

        OperationalCostEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'energy',
            'amount' => 50,
            'occurred_at' => '2026-01-12',
        ]);

        Harvest::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'harvested_at' => '2026-02-01',
            'type' => 'partial',
            'total_lbs' => 100,
        ]);

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/costs')
            ->assertOk();

        $this->assertEqualsWithDelta(250.0, (float) $response->json('totals.total_cost'), 0.0001);
        $this->assertEqualsWithDelta(2.5, (float) $response->json('metrics.cost_per_lb'), 0.0001);
    }

    public function test_handles_zero_harvest_cost_per_lb(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.0, 100000);

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed 1',
            'cost_per_kg' => 2.0,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 100,
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/costs')
            ->assertOk()
            ->assertJsonPath('metrics.cost_per_lb', 0);
    }

    public function test_tenant_isolation_costs(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $cycleA = $this->makeCycle($tenantA, '2026-01-01', 4.0, 100000);

        $feedTypeA = FeedType::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Feed A',
            'cost_per_kg' => 1.5,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenantA->id,
            'cycle_id' => $cycleA->id,
            'feed_type_id' => $feedTypeA->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 10,
        ]);

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/costs')
            ->assertOk();

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/costs')
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
            'code' => strtoupper(substr($tenant->slug, 0, 2)).'-CO',
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
}
