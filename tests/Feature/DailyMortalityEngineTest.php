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
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DailyMortalityEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_mortality_record_created(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, 'MO-1', 100000);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/mortalities', [
                'pond_id' => $cycle->pond_id,
                'recorded_at' => '2026-03-10',
                'mortality_count' => 450,
                'notes' => 'mortalidad normal diaria',
            ])
            ->assertCreated()
            ->assertJsonPath('data.mortality_count', 450);

        $this->assertDatabaseHas('daily_mortalities', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'recorded_at' => '2026-03-10 00:00:00',
            'mortality_count' => 450,
        ]);
    }

    public function test_mortality_visible_in_backoffice(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'MO-2', 100000);

        \App\Modules\Production\Domain\Models\DailyMortality::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'recorded_at' => '2026-03-10',
            'mortality_count' => 450,
            'notes' => 'mortalidad normal diaria',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/mortalities')
            ->assertOk()
            ->assertSee('Mortalidad diaria')
            ->assertSee('450')
            ->assertSee('mortalidad normal diaria');
    }

    public function test_mortality_updates_metrics(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, 'MO-3', 100000);

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-03-10',
            'pp_grams' => 10.0,
        ]);

        \App\Modules\Production\Domain\Models\DailyMortality::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'recorded_at' => '2026-03-10',
            'mortality_count' => 450,
        ]);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/metrics?survival_estimate=1')
            ->assertOk()
            ->assertJsonPath('mortality.total_mortality', 450)
            ->assertJsonPath('estimated_alive_count', 99550)
            ->assertJsonPath('biomass_kg', 995.5);
    }

    public function test_mortality_triggers_alert(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, 'MO-4', 100000);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/mortalities', [
                'pond_id' => $cycle->pond_id,
                'recorded_at' => '2026-03-10',
                'mortality_count' => 2501,
                'notes' => 'evento crítico',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('alert_events', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'rule_code' => AlertCode::HIGH_DAILY_MORTALITY->value,
        ]);
    }

    public function test_tenant_isolation_for_mortality(): void
    {
        [$tenantA, $tokenA] = $this->tenantToken('tenant-a', 'owner@a.local');
        [$tenantB, $tokenB] = $this->tenantToken('tenant-b', 'owner@b.local');

        $cycleA = $this->makeCycle($tenantA, 'MO-A', 100000);

        $this->withToken($tokenA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->postJson('/api/v1/mortalities', [
                'pond_id' => $cycleA->pond_id,
                'recorded_at' => '2026-03-10',
                'mortality_count' => 450,
            ])
            ->assertCreated();

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/mortalities')
            ->assertStatus(404);

        $this->withToken($tokenB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->postJson('/api/v1/mortalities', [
                'pond_id' => $cycleA->pond_id,
                'recorded_at' => '2026-03-10',
                'mortality_count' => 300,
            ])
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

    private function makeCycle(Tenant $tenant, string $pondCode, int $plQty): Cycle
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
            'started_at' => '2026-03-01',
        ]);

        Stocking::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'stocked_at' => '2026-03-02',
            'pl_qty' => $plQty,
            'density_pl_ha' => round($plQty / 3.2, 2),
            'density_pl_m2' => round($plQty / (3.2 * 10000), 4),
        ]);

        return $cycle;
    }
}
