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
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BackofficeCycleCostsInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cycle_costs_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant, true);
        $cycle = $this->makeCycle($tenant, 'CO-1');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/costs')
            ->assertOk()
            ->assertSee('Cycle Costs #'.$cycle->id);
    }

    public function test_cycle_costs_page_shows_summary(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant, true);
        $cycle = $this->makeCycle($tenant, 'CO-2');

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Feed Premium',
            'cost_per_kg' => 2.0,
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-03-10',
            'amount_kg' => 100,
        ]);

        OperationalCostEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'energy',
            'amount' => 50,
            'occurred_at' => '2026-03-11',
        ]);

        Harvest::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'harvested_at' => '2026-03-20',
            'type' => 'partial',
            'total_lbs' => 100,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/costs')
            ->assertOk()
            ->assertSee('$200.00', false)
            ->assertSee('$50.00', false)
            ->assertSee('$250.00', false);
    }

    public function test_operational_cost_can_be_created_when_not_read_only(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant, true);
        $cycle = $this->makeCycle($tenant, 'CO-3');

        $token = $this->csrfTokenFor($user, $tenant, '/backoffice/cycles/'.$cycle->id.'/costs');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/cycles/'.$cycle->id.'/costs', [
                '_token' => $token,
                'cost_type' => 'fuel',
                'amount' => 42.25,
                'occurred_at' => '2026-03-12',
                'notes' => 'Traslado bomba',
            ])
            ->assertRedirect('/backoffice/cycles/'.$cycle->id.'/costs');

        $this->assertDatabaseHas('operational_cost_entries', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'cost_type' => 'fuel',
            'amount' => 42.25,
        ]);
    }

    public function test_read_only_blocks_operational_cost_creation(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeReadOnlySubscription($tenant, true);
        $cycle = $this->makeCycle($tenant, 'CO-4');

        $token = $this->csrfTokenFor($user, $tenant, '/backoffice/cycles/'.$cycle->id.'/costs');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/cycles/'.$cycle->id.'/costs', [
                '_token' => $token,
                'cost_type' => 'labor',
                'amount' => 35,
                'occurred_at' => '2026-03-12',
            ])
            ->assertStatus(403);
    }

    public function test_tenant_isolation_for_cycle_costs(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA, true);
        $this->activeSubscription($tenantB, true);

        $cycleA = $this->makeCycle($tenantA, 'CO-A');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/cycles/'.$cycleA->id.'/costs')
            ->assertNotFound();
    }

    public function test_feature_flag_blocks_costs_page(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant, false);
        $cycle = $this->makeCycle($tenant, 'CO-5');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/costs')
            ->assertStatus(403);
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

    private function activeSubscription(Tenant $tenant, bool $costEnabled): void
    {
        $plan = Plan::query()->create([
            'code' => 'pro-'.$tenant->slug,
            'name' => 'PRO '.strtoupper($tenant->slug),
            'billing_type' => PlanBillingType::MONTHLY->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);
        $plan->features()->create(['feature_key' => 'dashboard', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'cost_engine', 'is_enabled' => $costEnabled]);

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

    private function activeReadOnlySubscription(Tenant $tenant, bool $costEnabled): void
    {
        $plan = Plan::query()->create([
            'code' => 'ro-'.$tenant->slug,
            'name' => 'RO '.strtoupper($tenant->slug),
            'billing_type' => PlanBillingType::MONTHLY->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);
        $plan->features()->create(['feature_key' => 'dashboard', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'cost_engine', 'is_enabled' => $costEnabled]);

        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::EXPIRED->value,
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->subDays(2),
            'offline_mode_enabled' => true,
            'offline_grace_days' => 7,
            'last_verified_at' => now()->subDay(),
            'verification_source' => 'cloud',
        ]);
    }

    private function csrfTokenFor(User $user, Tenant $tenant, string $url): string
    {
        $response = $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->get($url);

        preg_match('/name="_token" value="([^"]+)"/', $response->getContent(), $matches);

        return $matches[1] ?? Str::random(40);
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
}
