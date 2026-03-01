<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthMultiTenantSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_in_current_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'slug' => 'tenant-a',
            'name' => 'Tenant A',
        ]);

        $response = $this
            ->withHeader('X-Tenant', 'tenant-a')
            ->postJson('/api/v1/auth/register', [
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => UserRole::ADMIN->value,
                'device_name' => 'test-device',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'alice@example.com')
            ->assertJsonPath('user.tenant_id', $tenant->id)
            ->assertJsonPath('user.role', UserRole::ADMIN->value)
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'user' => ['id', 'tenant_id', 'name', 'email', 'role'],
            ]);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'alice@example.com',
            'role' => UserRole::ADMIN->value,
        ]);
    }

    public function test_login_requires_correct_tenant(): void
    {
        $tenantA = Tenant::factory()->create([
            'slug' => 'tenant-a',
            'name' => 'Tenant A',
        ]);

        Tenant::factory()->create([
            'slug' => 'tenant-b',
            'name' => 'Tenant B',
        ]);

        User::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this
            ->withHeader('X-Tenant', 'tenant-b')
            ->postJson('/api/v1/auth/login', [
                'email' => 'alice@example.com',
                'password' => 'password123',
            ]);

        $response
            ->assertStatus(401)
            ->assertExactJson([
                'message' => 'Invalid credentials for current tenant.',
            ]);
    }

    public function test_me_requires_auth(): void
    {
        $tenant = Tenant::factory()->create([
            'slug' => 'tenant-a',
            'name' => 'Tenant A',
        ]);

        $response = $this
            ->withHeader('X-Tenant', $tenant->slug)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_invalid_login_returns_401(): void
    {
        $tenant = Tenant::factory()->create([
            'slug' => 'tenant-a',
            'name' => 'Tenant A',
        ]);

        User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::ADMIN->value,
        ]);

        $response = $this
            ->withHeader('X-Tenant', 'tenant-a')
            ->postJson('/api/v1/auth/login', [
                'email' => 'alice@example.com',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertStatus(401)
            ->assertExactJson([
                'message' => 'Invalid credentials for current tenant.',
            ]);
    }
}
