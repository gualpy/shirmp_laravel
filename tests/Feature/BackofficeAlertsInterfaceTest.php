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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BackofficeAlertsInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerts_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $alert = $this->makeAlert($tenant, 'critical', 'LOW_GROWTH', 'Crecimiento por debajo del objetivo.');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/alerts')
            ->assertOk()
            ->assertSee('Operational Alerts')
            ->assertSee($alert->message);
    }

    public function test_alerts_filters_work(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $farmA = Farm::query()->create(['tenant_id' => $tenant->id, 'name' => 'Farm A']);
        $farmB = Farm::query()->create(['tenant_id' => $tenant->id, 'name' => 'Farm B']);
        $cycleA = $this->makeCycle($tenant, $farmA, 'A-1');
        $cycleB = $this->makeCycle($tenant, $farmB, 'B-1');

        $alertA = $this->seedAlert($tenant, $farmA, $cycleA, 'critical', 'HIGH_FCR', 'Alerta A');
        $this->seedAlert($tenant, $farmB, $cycleB, 'warning', 'LOW_GROWTH', 'Alerta B');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/alerts?farm='.$farmA->id.'&severity=critical')
            ->assertOk()
            ->assertSee($alertA->message)
            ->assertDontSee('Alerta B');
    }

    public function test_alerts_acknowledge_action(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $alert = $this->makeAlert($tenant, 'warning', 'LOW_GROWTH', 'Revisar crecimiento.');

        $token = $this->csrfTokenFor($user, $tenant, '/backoffice/alerts');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/alerts/'.$alert->id.'/acknowledge', ['_token' => $token])
            ->assertRedirect();

        $this->assertDatabaseHas('alert_events', [
            'id' => $alert->id,
            'is_acknowledged' => 1,
        ]);
    }

    public function test_alerts_resolve_action(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $alert = $this->makeAlert($tenant, 'critical', 'HIGH_BIOMASS', 'Resolver biomasa elevada.');

        $token = $this->csrfTokenFor($user, $tenant, '/backoffice/alerts');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->post('/backoffice/alerts/'.$alert->id.'/resolve', ['_token' => $token])
            ->assertRedirect();

        $this->assertDatabaseHas('alert_events', [
            'id' => $alert->id,
            'is_acknowledged' => 1,
        ]);
        $this->assertNotNull($alert->fresh()->resolved_at);
    }

    public function test_tenant_isolation_for_alerts(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $alertA = $this->makeAlert($tenantA, 'critical', 'LOW_GROWTH', 'Solo tenant A');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/alerts')
            ->assertOk()
            ->assertDontSee($alertA->message);

        $token = $this->csrfTokenFor($userB, $tenantB, '/backoffice/alerts');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->post('/backoffice/alerts/'.$alertA->id.'/acknowledge', ['_token' => $token])
            ->assertNotFound();
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

    private function makeAlert(Tenant $tenant, string $severity, string $ruleCode, string $message): AlertEvent
    {
        $farm = Farm::query()->create(['tenant_id' => $tenant->id, 'name' => 'Farm '.$tenant->slug]);
        $cycle = $this->makeCycle($tenant, $farm, strtoupper(substr($tenant->slug, 0, 2)).'-1');

        return $this->seedAlert($tenant, $farm, $cycle, $severity, $ruleCode, $message);
    }

    private function makeCycle(Tenant $tenant, Farm $farm, string $pondCode): Cycle
    {
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

    private function csrfTokenFor(User $user, Tenant $tenant, string $url): string
    {
        $response = $this->actingAs($user)
            ->withSession(['backoffice_tenant_slug' => $tenant->slug])
            ->get($url);

        preg_match('/name="_token" value="([^"]+)"/', $response->getContent(), $matches);

        return $matches[1] ?? Str::random(40);
    }

    private function seedAlert(Tenant $tenant, Farm $farm, Cycle $cycle, string $severity, string $ruleCode, string $message): AlertEvent
    {
        return AlertEvent::query()->create([
            'tenant_id' => $tenant->id,
            'farm_id' => $farm->id,
            'cycle_id' => $cycle->id,
            'rule_code' => $ruleCode,
            'severity' => $severity,
            'title' => $ruleCode,
            'message' => $message,
            'detected_at' => now()->subHour(),
            'context_json' => ['source' => 'test'],
        ]);
    }
}
