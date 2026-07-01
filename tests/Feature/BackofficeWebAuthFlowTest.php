<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BackofficeWebAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_backoffice_redirects_to_login_route(): void
    {
        $this->get('/backoffice')
            ->assertRedirect(route('login'));
    }

    public function test_login_page_renders_even_for_authenticated_user_to_avoid_redirect_loops(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/login')
            ->assertOk()
            ->assertSee('Ingreso Backoffice');
    }

    public function test_web_login_authenticates_in_tenant_scope(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);

        $response = $this->get('/login');
        preg_match('/name=\"_token\" value=\"([^\"]+)\"/', $response->getContent(), $matches);

        $this->post('/login', [
            '_token' => $matches[1] ?? Str::random(40),
            'tenant' => $tenant->slug,
            'email' => $user->email,
            'password' => 'password123',
        ])
            ->assertRedirect('/backoffice');

        $this->assertAuthenticatedAs($user);
        $this->assertSame($tenant->slug, session('backoffice_tenant_slug'));

        $this->get('/backoffice')
            ->assertOk()
            ->assertSee('Executive Summary');
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
        $plan = \App\Modules\SaaS\Domain\Models\Plan::query()->create([
            'code' => 'pro-'.$tenant->slug,
            'name' => 'PRO '.strtoupper($tenant->slug),
            'billing_type' => \App\Modules\SaaS\Domain\Enums\PlanBillingType::MONTHLY->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);
        $plan->features()->create(['feature_key' => 'dashboard', 'is_enabled' => true]);

        \App\Modules\SaaS\Domain\Models\TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => \App\Modules\SaaS\Domain\Enums\SubscriptionStatus::ACTIVE->value,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
            'offline_mode_enabled' => false,
            'offline_grace_days' => 7,
            'verification_source' => 'cloud',
        ]);
    }
}
