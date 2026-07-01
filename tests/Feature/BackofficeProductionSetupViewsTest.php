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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BackofficeProductionSetupViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_farms_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->activeSubscription($tenant);

        $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->get('/backoffice/farms')
            ->assertOk()
            ->assertSee('Farms')
            ->assertSee('New farm');
    }

    public function test_ponds_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->activeSubscription($tenant);
        $farm = Farm::withoutGlobalScopes()->create(['tenant_id' => $tenant->id, 'name' => 'Demo Farm']);

        $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->get('/backoffice/ponds?farm='.$farm->id)
            ->assertOk()
            ->assertSee('Ponds')
            ->assertSee('New pond');
    }

    public function test_stocking_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->activeSubscription($tenant);
        $farm = Farm::withoutGlobalScopes()->create(['tenant_id' => $tenant->id, 'name' => 'Demo Farm']);
        Pond::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P01',
            'name' => 'North Pond',
            'area_ha' => 4.5,
            'avg_depth_m' => 1.4,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->get('/backoffice/stocking/create')
            ->assertOk()
            ->assertSee('New Stocking')
            ->assertSee('Open cycle and stock');
    }

    public function test_farm_can_be_created_from_backoffice(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->activeSubscription($tenant);
        $token = $this->csrfTokenFor($user, $tenant, '/backoffice/farms');

        $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->post('/backoffice/farms', [
                '_token' => $token,
                'name' => 'East Farm',
                'location' => 'El Oro',
                'notes' => 'Created from backoffice.',
            ])
            ->assertRedirect('/backoffice/farms');

        $this->assertDatabaseHas('farms', [
            'tenant_id' => $tenant->id,
            'name' => 'East Farm',
            'location' => 'El Oro',
        ]);
    }

    public function test_pond_and_stocking_flow_can_be_created_from_backoffice(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $this->activeSubscription($tenant);
        $farm = Farm::withoutGlobalScopes()->create(['tenant_id' => $tenant->id, 'name' => 'Demo Farm']);
        $pondToken = $this->csrfTokenFor($user, $tenant, '/backoffice/ponds');

        $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->post('/backoffice/ponds', [
                '_token' => $pondToken,
                'farm_id' => $farm->id,
                'code' => 'P09',
                'name' => 'Pond Nine',
                'area_ha' => 3.8,
                'avg_depth_m' => 1.4,
                'is_active' => '1',
            ])
            ->assertRedirect('/backoffice/ponds?farm='.$farm->id);

        $pond = Pond::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('code', 'P09')->firstOrFail();
        $stockingToken = $this->csrfTokenFor($user, $tenant, '/backoffice/stocking/create');

        $response = $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->post('/backoffice/stocking', [
                '_token' => $stockingToken,
                'pond_id' => $pond->id,
                'started_at' => '2026-06-02',
                'stocked_at' => '2026-06-02',
                'pl_qty' => 380000,
                'hatchery_code' => 'BC',
                'batch_code' => 'P09-001',
                'initial_pp_grams' => '0.05',
                'cycle_notes' => 'Cycle opened from new stocking.',
            ]);

        $cycle = Cycle::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('pond_id', $pond->id)->firstOrFail();
        $response->assertRedirect('/backoffice/cycles/'.$cycle->id);

        $this->assertDatabaseHas('cycles', [
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
        ]);
        $this->assertDatabaseHas('stockings', [
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pl_qty' => 380000,
            'batch_code' => 'P09-001',
        ]);
    }

    /** @return array{Tenant, User} */
    private function tenantUser(): array
    {
        $tenant = Tenant::factory()->create(['slug' => 'tenant-a', 'name' => 'Tenant A']);
        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@a.local',
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
            'ends_at' => now()->addDays(30),
            'offline_mode_enabled' => false,
            'offline_grace_days' => 7,
            'verification_source' => 'cloud',
        ]);
    }

    private function csrfTokenFor(User $user, Tenant $tenant, string $url): string
    {
        $response = $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->get($url);

        preg_match('/name=\"_token\" value=\"([^\"]+)\"/', $response->getContent(), $matches);

        return $matches[1] ?? Str::random(40);
    }
}
