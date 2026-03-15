<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Multitenancy\TenantScopeBypass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_healthz_returns_ok(): void
    {
        $this->getJson('/healthz')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
            ]);
    }

    public function test_readyz_returns_success_in_test_env(): void
    {
        $this->getJson('/readyz')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => [
                    'app_key' => true,
                    'db' => true,
                    'cache' => true,
                ],
            ]);
    }

    public function test_superadmin_ops_page_loads(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/backoffice/admin/ops')
            ->assertOk()
            ->assertSee('Operaciones')
            ->assertSee('Readiness')
            ->assertSee('Production Env');
    }

    private function superAdmin(): User
    {
        return app(TenantScopeBypass::class)->run(fn (): User => User::withoutGlobalScopes()->create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin-ops@example.test',
            'password' => Hash::make('password123'),
            'role' => UserRole::SUPER_ADMIN->value,
        ]));
    }
}
