<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Alerts\Domain\Enums\AlertCode;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AlertEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_low_growth_alert(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.5, 120000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-10',
            'pp_grams' => 5.00,
        ]);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-17',
            'pp_grams' => 5.50,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-20')->assertSuccessful();

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/alerts?rule_code='.AlertCode::LOW_GROWTH->value)
            ->assertOk();

        $this->assertDatabaseHas('alert_events', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'rule_code' => AlertCode::LOW_GROWTH->value,
        ]);
    }

    public function test_generates_high_biomass_alert(): void
    {
        [$tenant, ] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 2.0, 500000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-20',
            'pp_grams' => 20.00,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-21')->assertSuccessful();

        $this->assertDatabaseHas('alert_events', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'rule_code' => AlertCode::HIGH_BIOMASS->value,
        ]);
    }

    public function test_dedupes_same_rule_same_day(): void
    {
        [$tenant, ] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 4.5, 120000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-10',
            'pp_grams' => 5.00,
        ]);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-17',
            'pp_grams' => 5.40,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-21')->assertSuccessful();
        $this->artisan('alerts:evaluate --date=2026-01-21')->assertSuccessful();

        $count = AlertEvent::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('cycle_id', $cycle->id)
            ->where('rule_code', AlertCode::LOW_GROWTH->value)
            ->whereDate('detected_at', '2026-01-21')
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_tenant_isolation_alerts(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $cycleA = $this->makeCycle($tenantA, '2026-01-01', 2.0, 500000);
        Sampling::query()->create([
            'tenant_id' => $tenantA->id,
            'cycle_id' => $cycleA->id,
            'sampled_at' => '2026-01-20',
            'pp_grams' => 20.00,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-21')->assertSuccessful();

        $responseA = $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/alerts')
            ->assertOk();

        $this->assertGreaterThanOrEqual(1, count((array) $responseA->json('data')));

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/alerts')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_acknowledge_marks_alert(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 2.0, 500000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-20',
            'pp_grams' => 20.00,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-21')->assertSuccessful();

        $alert = AlertEvent::withoutGlobalScopes()->firstOrFail();

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/alerts/'.$alert->id.'/acknowledge')
            ->assertOk()
            ->assertJsonPath('data.is_acknowledged', true);

        $this->assertDatabaseHas('alert_events', [
            'id' => $alert->id,
            'is_acknowledged' => 1,
        ]);
    }

    public function test_resolve_marks_alert_resolved(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', 2.0, 500000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-01-20',
            'pp_grams' => 20.00,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-21')->assertSuccessful();

        $alert = AlertEvent::withoutGlobalScopes()->firstOrFail();

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/alerts/'.$alert->id.'/resolve')
            ->assertOk()
            ->assertJsonPath('data.state', 'resolved');

        $this->assertNotNull($alert->fresh()->resolved_at);
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
            'code' => strtoupper(substr($tenant->slug, 0, 2)).'-AL',
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
