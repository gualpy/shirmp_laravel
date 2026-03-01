<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Alerts\Domain\Enums\AlertCode;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class WaterQualityModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_water_quality_requires_at_least_one_metric(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', CycleStatus::ACTIVE);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/water-quality', [
                'measured_at' => '2026-01-10 08:00:00',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['metrics']);
    }

    public function test_water_quality_rejects_outside_cycle_dates(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-05', CycleStatus::HARVESTED, '2026-01-20');

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/water-quality', [
                'measured_at' => '2026-01-04 10:00:00',
                'ph' => 8.1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['measured_at']);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/water-quality', [
                'measured_at' => '2026-01-21 10:00:00',
                'ph' => 8.1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['measured_at']);
    }

    public function test_tenant_isolation_water_quality(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $cycleA = $this->makeCycle($tenantA, '2026-01-01', CycleStatus::ACTIVE);

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->postJson('/api/v1/cycles/'.$cycleA->id.'/water-quality', [
                'measured_at' => '2026-01-10 06:00:00',
                'dissolved_oxygen_mg_l' => 4.5,
            ])
            ->assertCreated();

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/water-quality')
            ->assertStatus(404);
    }

    public function test_generates_do_low_alert(): void
    {
        [$tenant, ] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, '2026-01-01', CycleStatus::ACTIVE);

        WaterQualityEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'measured_at' => '2026-01-12 05:00:00',
            'dissolved_oxygen_mg_l' => 2.9,
        ]);

        $this->artisan('alerts:evaluate --date=2026-01-12')->assertSuccessful();

        $this->assertDatabaseHas('alert_events', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'rule_code' => AlertCode::DO_LOW->value,
            'severity' => 'critical',
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

    private function makeCycle(Tenant $tenant, string $startedAt, CycleStatus $status, ?string $endedAt = null): Cycle
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$tenant->slug,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => strtoupper(substr($tenant->slug, 0, 2)).'-WQ',
            'area_ha' => 3.5,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => $status->value,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ]);

        Stocking::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'stocked_at' => date('Y-m-d', strtotime($startedAt.' +1 day')),
            'pl_qty' => 100000,
            'density_pl_ha' => round(100000 / 3.5, 2),
            'density_pl_m2' => round(100000 / (3.5 * 10000), 4),
        ]);

        return $cycle;
    }
}

