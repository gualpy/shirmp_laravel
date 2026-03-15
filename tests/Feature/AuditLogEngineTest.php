<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Alerts\Domain\Models\AlertEvent;
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
use App\Multitenancy\TenantScopeBypass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuditLogEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_created_for_mortality(): void
    {
        [$tenant, $token] = $this->tenantToken('tenant-a', 'owner@a.local');
        $cycle = $this->makeCycle($tenant, 'AU-1', 100000);

        $this->withToken($token)
            ->withHeader('X-Tenant', $tenant->slug)
            ->postJson('/api/v1/mortalities', [
                'pond_id' => $cycle->pond_id,
                'recorded_at' => '2026-03-10',
                'mortality_count' => 450,
                'notes' => 'normal',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'mortality.created',
            'entity_type' => 'DailyMortality',
        ]);
    }

    public function test_audit_log_created_for_alert_acknowledge(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'AU-2', 100000);
        $farmId = Pond::withoutGlobalScopes()->whereKey($cycle->pond_id)->value('farm_id');

        $alert = AlertEvent::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farmId,
            'cycle_id' => $cycle->id,
            'rule_code' => 'LOW_GROWTH',
            'severity' => 'warning',
            'title' => 'Low growth',
            'message' => 'Growth below threshold',
            'detected_at' => now(),
            'is_acknowledged' => false,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/alerts/'.$alert->id.'/acknowledge')
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'action_key' => 'alert.acknowledged',
            'entity_type' => 'AlertEvent',
            'entity_id' => $alert->id,
        ]);
    }

    public function test_tenant_audit_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $this->seedAuditLog($tenant, $user, 'mortality.created', 'DailyMortality', 1);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/audit')
            ->assertOk()
            ->assertSee('Audit Log')
            ->assertSee('mortality.created');
    }

    public function test_superadmin_audit_page_loads(): void
    {
        $superAdmin = $this->superAdmin();
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->seedAuditLog($tenant, $user, 'subscription.verify_now', 'TenantSubscription', 2);

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/audit')
            ->assertOk()
            ->assertSee('Audit Global')
            ->assertSee('subscription.verify_now')
            ->assertSee('TENANT-A');
    }

    public function test_tenant_isolation_for_audit_logs(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $this->seedAuditLog($tenantA, $userA, 'mortality.created', 'DailyMortality', 1);
        $this->seedAuditLog($tenantB, $userB, 'cost.created', 'OperationalCostEntry', 2);

        $this->actingAs($userA)
            ->withHeader('X-Tenant', $tenantA->slug)
            ->get('/backoffice/audit')
            ->assertOk()
            ->assertSee('mortality.created')
            ->assertDontSee('cost.created');
    }

    public function test_non_superadmin_cannot_access_admin_audit(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');

        $this->actingAs($user)
            ->get('/backoffice/admin/audit')
            ->assertStatus(403);
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

    private function superAdmin(): User
    {
        return app(TenantScopeBypass::class)->run(fn (): User => User::withoutGlobalScopes()->create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@example.test',
            'password' => Hash::make('password123'),
            'role' => UserRole::SUPER_ADMIN->value,
        ]));
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
        $plan->features()->create(['feature_key' => 'alerts', 'is_enabled' => true]);

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

    private function seedAuditLog(Tenant $tenant, User $user, string $actionKey, string $entityType, int $entityId): void
    {
        \App\Modules\Audit\Domain\Models\AuditLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action_key' => $actionKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context_json' => ['sample' => 'yes'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);
    }
}
