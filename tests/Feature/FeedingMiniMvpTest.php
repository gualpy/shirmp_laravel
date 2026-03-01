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
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class FeedingMiniMvpTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_feed_entry_when_cycle_not_active(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $cycle = $this->makeCycle($tenant, CycleStatus::HARVESTED, '2026-01-01');
        $this->makeStocking($tenant->id, $cycle->id, '2026-01-02');

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Starter 35',
            'is_active' => true,
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/feed-entries', [
                'fed_at' => '2026-01-10',
                'feed_type_id' => $feedType->id,
                'amount_kg' => 45.5,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.cycle.0', 'Only active cycles allow this operation.');
    }

    public function test_blocks_feed_entry_without_stocking(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $cycle = $this->makeCycle($tenant, CycleStatus::ACTIVE, '2026-01-01');
        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Grower 32',
            'is_active' => true,
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/cycles/'.$cycle->id.'/feed-entries', [
                'fed_at' => '2026-01-10',
                'feed_type_id' => $feedType->id,
                'amount_kg' => 30,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.stocking.0', 'Cycle requires stocking before this operation.');
    }

    public function test_tenant_isolation_for_feeding(): void
    {
        [$tenantA, , $tokenA] = $this->tenantUserToken('tenant-a', 'owner@a.local');
        [$tenantB, , $tokenB] = $this->tenantUserToken('tenant-b', 'owner@b.local');

        $cycleA = $this->makeCycle($tenantA, CycleStatus::ACTIVE, '2026-01-01');
        $this->makeStocking($tenantA->id, $cycleA->id, '2026-01-02');

        $feedTypeA = FeedType::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Type A',
            'is_active' => true,
        ]);

        $entryA = FeedEntry::query()->create([
            'tenant_id' => $tenantA->id,
            'cycle_id' => $cycleA->id,
            'feed_type_id' => $feedTypeA->id,
            'fed_at' => '2026-01-05',
            'amount_kg' => 22.750,
        ]);

        $this->withToken($tokenA)->withHeader('X-Tenant', $tenantA->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/feed-entries')
            ->assertOk()
            ->assertJsonPath('data.0.id', $entryA->id);

        $this->withToken($tokenB)->withHeader('X-Tenant', $tenantB->slug)
            ->patchJson('/api/v1/feed-types/'.$feedTypeA->id, ['name' => 'Hacked'])
            ->assertStatus(404);

        $this->withToken($tokenB)->withHeader('X-Tenant', $tenantB->slug)
            ->getJson('/api/v1/cycles/'.$cycleA->id.'/feed-entries')
            ->assertStatus(404);
    }

    public function test_list_feed_entries_with_date_filters(): void
    {
        [$tenant, , $token] = $this->tenantUserToken('tenant-a', 'owner@a.local');

        $cycle = $this->makeCycle($tenant, CycleStatus::ACTIVE, '2026-01-01');
        $this->makeStocking($tenant->id, $cycle->id, '2026-01-02');

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Range Feed',
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-03',
            'amount_kg' => 10,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-10',
            'amount_kg' => 11,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-01-20',
            'amount_kg' => 12,
        ]);

        $this->withToken($token)->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/cycles/'.$cycle->id.'/feed-entries?date_from=2026-01-05&date_to=2026-01-15')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fed_at', '2026-01-10');
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

    private function makeCycle(Tenant $tenant, CycleStatus $status, string $startedAt): Cycle
    {
        $farm = Farm::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Farm '.$tenant->slug,
        ]);

        $pond = Pond::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'code' => strtoupper(substr($tenant->slug, 0, 2)).'-P1',
            'area_ha' => 3.5,
            'is_active' => true,
        ]);

        return Cycle::query()->create([
            'tenant_id' => $tenant->id,
            'pond_id' => $pond->id,
            'status' => $status->value,
            'started_at' => $startedAt,
            'ended_at' => $status === CycleStatus::ACTIVE ? null : '2026-03-01',
        ]);
    }

    private function makeStocking(int $tenantId, int $cycleId, string $stockedAt): Stocking
    {
        return Stocking::query()->create([
            'tenant_id' => $tenantId,
            'cycle_id' => $cycleId,
            'stocked_at' => $stockedAt,
            'pl_qty' => 100000,
            'density_pl_ha' => 28571.43,
            'density_pl_m2' => 2.8571,
        ]);
    }
}
