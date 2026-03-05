<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
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

final class BackofficeNavigationAndDemoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_home_renders_authenticated(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $this->makeActiveCycle($tenant, 'FA-1');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice')
            ->assertOk()
            ->assertSee('Resumen Ejecutivo')
            ->assertSee('Ver ciclos activos');
    }

    public function test_cycles_list_renders_and_links_to_detail(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeActiveCycle($tenant, 'FA-2');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles')
            ->assertOk()
            ->assertSee('Ciclos Activos')
            ->assertSee((string) $cycle->pond->code)
            ->assertSee('/backoffice/cycles/'.$cycle->id, false);
    }

    public function test_read_only_shows_banner(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');

        $plan = Plan::query()->create([
            'code' => 'pro-ro',
            'name' => 'PRO RO',
            'billing_type' => PlanBillingType::MONTHLY->value,
            'price_usd' => 100,
            'is_active' => true,
        ]);
        $plan->features()->create(['feature_key' => 'dashboard', 'is_enabled' => true]);

        TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::EXPIRED->value,
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->subDays(2),
            'offline_mode_enabled' => true,
            'offline_grace_days' => 7,
            'last_verified_at' => now()->subDays(1),
            'verification_source' => 'cloud',
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice')
            ->assertOk()
            ->assertHeader('X-Read-Only-Mode', '1')
            ->assertSee('Modo solo lectura activo');
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

    private function makeActiveCycle(Tenant $tenant, string $pondCode): Cycle
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

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-02-20',
            'pp_grams' => 8.2,
        ]);

        return $cycle;
    }
}

