<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MultitenantCoreSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_tenant_by_header(): void
    {
        Tenant::factory()->create([
            'slug' => 'tenant-a',
            'name' => 'Tenant A',
        ]);

        Tenant::factory()->create([
            'slug' => 'tenant-b',
            'name' => 'Tenant B',
        ]);

        $response = $this
            ->withHeader('X-Tenant', 'tenant-a')
            ->getJson('/api/v1/tenant/current');

        $response
            ->assertOk()
            ->assertJsonPath('tenant.slug', 'tenant-a')
            ->assertJsonPath('tenant.name', 'Tenant A');
    }

    public function test_isolation_by_scope(): void
    {
        $tenantA = Tenant::factory()->create([
            'slug' => 'tenant-a',
            'name' => 'Tenant A',
        ]);

        $tenantB = Tenant::factory()->create([
            'slug' => 'tenant-b',
            'name' => 'Tenant B',
        ]);

        TenantNote::query()->create([
            'tenant_id' => $tenantA->id,
            'note' => 'note-a-1',
        ]);

        TenantNote::query()->create([
            'tenant_id' => $tenantB->id,
            'note' => 'note-b-1',
        ]);

        $response = $this
            ->withHeader('X-Tenant', 'tenant-a')
            ->getJson('/api/v1/tenant-notes');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.note', 'note-a-1')
            ->assertJsonPath('data.0.tenant_id', $tenantA->id);
    }

    public function test_missing_tenant_returns_400(): void
    {
        $response = $this->getJson('/api/v1/tenant/current');

        $response
            ->assertStatus(400)
            ->assertExactJson([
                'message' => 'Tenant could not be resolved. Use subdomain {tenant}.localhost or header X-Tenant.',
            ]);
    }
}
