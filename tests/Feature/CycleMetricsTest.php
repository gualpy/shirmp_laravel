<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class CycleMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_returns_correct_totals_for_feed_and_harvest(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$cycle, $feedType] = $this->cycleWithFeedType($tenant, '2026-01-01', 4.50, 435760);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 100.125,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-11',
            'amount_kg' => 50.375,
        ]);

        Harvest::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'harvested_at' => '2026-02-01',
            'type' => 'partial',
            'total_lbs' => 220.46,
        ]);

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/metrics');

        $response->assertOk();
        $this->assertEqualsWithDelta(150.5, (float) $response->json('feed.total_feed_kg'), 0.0001);
        $this->assertEqualsWithDelta(220.46, (float) $response->json('harvest.total_lbs'), 0.0001);
        $this->assertEqualsWithDelta(99.9986, (float) $response->json('harvest.total_kg'), 0.01);
        $this->assertEqualsWithDelta(1.5050, (float) $response->json('fcr'), 0.01);
    }

    public function test_fcr_handles_zero_harvest(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$cycle, $feedType] = $this->cycleWithFeedType($tenant, '2026-01-01', 4.50, 435760);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 100,
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/metrics')
            ->assertOk()
            ->assertJsonPath('fcr', 0);
    }

    public function test_growth_uses_samplings(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$cycle] = $this->cycleWithFeedType($tenant, '2026-01-01', 3.50, 150000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-10',
            'pp_grams' => 5,
        ]);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-17',
            'pp_grams' => 8,
        ]);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-24',
            'pp_grams' => 11,
        ]);

        $response = $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/metrics');

        $response->assertOk();
        $this->assertEqualsWithDelta(3.0, (float) $response->json('sampling.growth_g_per_week'), 0.05);
        $this->assertEqualsWithDelta(11.0, (float) $response->json('sampling.latest_pp_grams'), 0.001);
    }

    public function test_tenant_isolation_metrics(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        [$cycleA] = $this->cycleWithFeedType($tenantA, '2026-01-01', 3.50, 150000);

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/metrics')
            ->assertOk();

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/metrics')
            ->assertStatus(404);
    }

    /**
     * @return array{Tenant, string}
     */
    private function tenantToken(string $slug, string $email): array
    {
        $tenant = Tenant::factory()->create([
            'slug' => $slug,
            'name' => strtoupper($slug),
        ]);

        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => UserRole::OWNER->value,
        ]);

        return [$tenant, $user->createToken('test-device')->plainTextToken];
    }

    /**
     * @return array{Cycle, FeedType}
     */
    private function cycleWithFeedType(Tenant $tenant, string $startedAt, float $areaHa, int $plQty): array
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$tenant->slug,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => strtoupper(substr($tenant->slug, 0, 2)).'-M1',
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

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed '.$tenant->slug,
            'is_active' => true,
        ]);

        return [$cycle, $feedType];
    }
}
