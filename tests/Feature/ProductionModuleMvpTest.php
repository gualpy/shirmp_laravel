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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ProductionModuleMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_isolation_for_production_resources(): void
    {
        [$tenantA, $userA, $tokenA] = $this->tenantUserToken('tenant-a', 'a@demo.local');
        [$tenantB, $userB, $tokenB] = $this->tenantUserToken('tenant-b', 'b@demo.local');

        $farm = Farm::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Farm A',
            'location' => 'A',
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenantA->id,
            'farm_id' => $farm->id,
            'code' => 'PA1',
            'name' => 'Pond A',
            'area_ha' => 2.5,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenantA->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => '2026-01-01',
        ]);

        $this->withToken($tokenA)->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/farms/'.$farm->id)
            ->assertOk();

        $this->withToken($tokenB)->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/farms/'.$farm->id)
            ->assertStatus(404);

        $this->withToken($tokenB)->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id)
            ->assertStatus(404);

        $this->assertNotNull($userA);
        $this->assertNotNull($userB);
    }

    public function test_prevents_second_active_cycle_in_same_pond(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm A',
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-01',
            'area_ha' => 4.5,
            'is_active' => true,
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/ponds/'.$pond->id.'/cycles', [
                'started_at' => '2026-01-01',
                'status' => 'active',
            ])
            ->assertCreated();

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/ponds/'.$pond->id.'/cycles', [
                'started_at' => '2026-02-01',
                'status' => 'active',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.status.0', 'This pond already has an active cycle.');
    }

    public function test_stocking_calculates_density_correctly(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm A',
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-01',
            'area_ha' => 4.50,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => '2026-01-01',
        ]);

        $response = $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/stocking', [
                'stocked_at' => '2026-01-02',
                'pl_qty' => 435760,
            ])
            ->assertCreated();

        $densityM2 = (float) $response->json('density_pl_m2');
        $densityHa = (float) $response->json('density_pl_ha');

        $this->assertEqualsWithDelta(9.6836, $densityM2, 0.0002);
        $this->assertEqualsWithDelta(96835.56, $densityHa, 0.02);
    }

    public function test_blocks_harvest_without_stocking(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm A',
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-01',
            'area_ha' => 4.50,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => '2026-01-01',
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/harvests', [
                'harvested_at' => '2026-01-10',
                'type' => 'partial',
                'total_lbs' => 1200,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.stocking.0', 'Cycle requires stocking before this operation.');
    }

    public function test_final_harvest_closes_cycle_and_blocks_new_entries(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm A',
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => 'P-01',
            'area_ha' => 4.50,
            'is_active' => true,
        ]);

        $cycle = Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => '2026-01-01',
        ]);

        Stocking::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'stocked_at' => '2026-01-02',
            'pl_qty' => 435760,
            'density_pl_ha' => 96835.56,
            'density_pl_m2' => 9.6836,
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/harvests', [
                'harvested_at' => '2026-02-20',
                'type' => 'final',
                'total_lbs' => 32000,
            ])
            ->assertCreated();

        $cycle = Cycle::withoutGlobalScopes()->findOrFail($cycle->id);
        $this->assertSame(CycleStatus::HARVESTED, $cycle->status);
        $this->assertSame('2026-02-20', $cycle->ended_at?->format('Y-m-d'));

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/samplings', [
                'sampled_at' => '2026-02-21',
                'pp_grams' => 24.5,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.cycle.0', 'Only active cycles allow this operation.');

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/harvests', [
                'harvested_at' => '2026-02-22',
                'type' => 'partial',
                'total_lbs' => 1000,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.cycle.0', 'Only active cycles allow this operation.');
    }

    /**
     * @return array{Tenant, User, string}
     */
    private function tenantUserToken(string $slug, string $email): array
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

        $token = $user->createToken('test-device')->plainTextToken;

        return [$tenant, $user, $token];
    }
}
