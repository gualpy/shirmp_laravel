<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BackofficeWaterQualityInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_water_quality_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $entry = $this->makeWaterEntry($tenant, '2026-03-01 08:00:00');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/water')
            ->assertOk()
            ->assertSee('Registrar calidad de agua')
            ->assertSee($entry->pond->code);
    }

    public function test_water_quality_register_entry(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'WA-1');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/water', [
                'pond' => $cycle->pond_id,
                'measured_at' => '2026-03-05T07:45',
                'do' => 3.2,
                'ph' => 8.9,
                'temperature' => 29.1,
                'salinity' => 18.5,
                'ammonia' => 0.210,
                'nitrite' => 0.035,
                'notes' => 'Ronda AM',
            ])
            ->assertRedirect('/backoffice/water?cycle='.$cycle->id);

        $this->assertDatabaseHas('water_quality_entries', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'notes' => 'Ronda AM',
        ]);
    }

    public function test_water_quality_filters(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycleA = $this->makeCycle($tenant, 'WA-2');
        $cycleB = $this->makeCycle($tenant, 'WB-2');
        $this->seedWaterEntry($tenant, $cycleA, '2026-03-01 08:00:00', 3.1, 8.5);
        $entryB = $this->seedWaterEntry($tenant, $cycleB, '2026-03-03 08:00:00', 5.1, 7.8);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/water?pond='.$cycleB->pond_id.'&date_from=2026-03-02')
            ->assertOk()
            ->assertSee($entryB->pond->code)
            ->assertSee('2026-03-03 08:00')
            ->assertDontSee('2026-03-01 08:00');
    }

    public function test_tenant_isolation_for_water_quality(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);
        $entryA = $this->makeWaterEntry($tenantA, '2026-03-01 08:00:00');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/water')
            ->assertOk()
            ->assertDontSee('WQ-a');

        $cycleB = $this->makeCycle($tenantB, 'WB-3');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->post('/backoffice/water', [
                'pond' => $entryA->pond_id,
                'measured_at' => '2026-03-05T07:45',
                'do' => 4.2,
            ])
            ->assertNotFound();

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->post('/backoffice/water', [
                'pond' => $cycleB->pond_id,
                'measured_at' => '2026-03-05T07:45',
                'do' => 4.2,
            ])
            ->assertRedirect('/backoffice/water?cycle='.$cycleB->id);
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

    private function makeWaterEntry(Tenant $tenant, string $measuredAt): WaterQualityEntry
    {
        $cycle = $this->makeCycle($tenant, 'WQ-'.substr($tenant->slug, -1));

        return $this->seedWaterEntry($tenant, $cycle, $measuredAt, 3.4, 8.9);
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

    private function seedWaterEntry(Tenant $tenant, Cycle $cycle, string $measuredAt, float $do, float $ph): WaterQualityEntry
    {
        return WaterQualityEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'measured_at' => $measuredAt,
            'dissolved_oxygen_mg_l' => $do,
            'ph' => $ph,
            'temp_c' => 29.4,
            'salinity_ppt' => 18.5,
            'ammonia_mg_l' => 0.190,
            'nitrite_mg_l' => 0.022,
        ]);
    }
}
