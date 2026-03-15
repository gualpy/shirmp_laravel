<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\DailyMortality;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BackofficeCycleExportsAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_samplings_csv_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-1');

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-03-10',
            'pp_grams' => 11.8,
            'notes' => 'Muestreo semanal',
        ]);

        $response = $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/samplings.csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('sampled_at,pp_grams,notes', $response->streamedContent());
        $this->assertStringContainsString('2026-03-10,11.80,"Muestreo semanal"', $response->streamedContent());
    }

    public function test_feed_csv_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-2');

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Balanceado 35',
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-03-11',
            'amount_kg' => 125.5,
            'notes' => 'Ración PM',
        ]);

        $response = $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/feed.csv');

        $response->assertOk();
        $this->assertStringContainsString('fed_at,feed_type,amount_kg,notes', $response->streamedContent());
        $this->assertStringContainsString('2026-03-11,"Balanceado 35",125.500,"Ración PM"', $response->streamedContent());
    }

    public function test_mortalities_csv_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-3');

        DailyMortality::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'recorded_at' => '2026-03-12',
            'mortality_count' => 320,
            'notes' => 'Normal',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/mortalities.csv');

        $response->assertOk();
        $this->assertStringContainsString('recorded_at,pond,mortality_count,notes', $response->streamedContent());
        $this->assertStringContainsString('2026-03-12,EX-3,320,Normal', $response->streamedContent());
    }

    public function test_water_csv_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-4');

        WaterQualityEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'measured_at' => '2026-03-13 06:00:00',
            'dissolved_oxygen_mg_l' => 4.2,
            'ph' => 7.8,
            'temp_c' => 29.4,
            'salinity_ppt' => 14.0,
            'alkalinity_mg_l' => 120,
            'ammonia_mg_l' => 0.120,
            'nitrite_mg_l' => 0.015,
            'notes' => 'AM check',
        ]);

        $response = $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/water.csv');

        $response->assertOk();
        $this->assertStringContainsString('measured_at,do,ph,temperature,salinity,alkalinity,ammonia,nitrite,notes', $response->streamedContent());
        $this->assertStringContainsString('"2026-03-13 06:00",4.20,7.80,29.40,14.00,120.00,0.120,0.015,"AM check"', $response->streamedContent());
    }

    public function test_executive_report_page_loads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-5');

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-03-10',
            'pp_grams' => 10.5,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/report')
            ->assertOk()
            ->assertSee('Reporte Ejecutivo')
            ->assertSee('Proyección de Cosecha')
            ->assertSee('Imprimir');
    }

    public function test_executive_report_includes_company_identity_when_available(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $tenant->update([
            'company_display_name' => 'Camaronera Delta',
            'company_legal_name' => 'Camaronera Delta S.A.',
            'logo_path' => '/assets/img/report-logo-placeholder.svg',
            'company_address' => 'Km 12 vía costera',
            'company_phone' => '+593999000111',
            'company_email' => 'info@delta.test',
            'report_footer_text' => 'Documento interno de uso gerencial.',
        ]);
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-6');

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/report')
            ->assertOk()
            ->assertSee('Camaronera Delta')
            ->assertSee('Camaronera Delta S.A.')
            ->assertSee('/assets/img/report-logo-placeholder.svg')
            ->assertSee('Km 12 vía costera')
            ->assertSee('Documento interno de uso gerencial.');
    }

    public function test_executive_report_falls_back_when_logo_missing(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $tenant->update([
            'company_display_name' => 'Grupo Costero',
            'logo_path' => null,
        ]);
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'EX-7');
        $farmId = Pond::withoutGlobalScopes()->whereKey($cycle->pond_id)->value('farm_id');
        $farm = Farm::withoutGlobalScopes()->findOrFail($farmId);
        $farm->update([
            'company_display_name' => 'Finca San Marcos',
            'logo_path' => null,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/report')
            ->assertOk()
            ->assertSee('Finca San Marcos')
            ->assertSee('Empresa')
            ->assertDontSee('/assets/img/report-logo-placeholder.svg');
    }

    public function test_tenant_isolation_for_exports(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $cycleA = $this->makeCycle($tenantA, 'EX-A');

        Sampling::query()->create([
            'tenant_id' => $tenantA->id,
            'cycle_id' => $cycleA->id,
            'sampled_at' => '2026-03-10',
            'pp_grams' => 9.8,
        ]);

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/cycles/'.$cycleA->id.'/exports/samplings.csv')
            ->assertNotFound();
    }

    public function test_samplings_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-xlsx-a', 'owner-xlsx@a.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'XLSX-1');

        Sampling::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'sampled_at' => '2026-03-10',
            'pp_grams' => 12.3,
            'notes' => 'Sheet row',
        ]);

        $response = $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/samplings.xlsx');

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $response->assertHeader('content-type');
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
    }

    public function test_feed_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-xlsx-b', 'owner-xlsx@b.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'XLSX-2');

        $feedType = FeedType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Balanceado XLSX',
            'is_active' => true,
        ]);

        FeedEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'feed_type_id' => $feedType->id,
            'fed_at' => '2026-03-11',
            'amount_kg' => 90,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/feed.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_mortalities_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-xlsx-c', 'owner-xlsx@c.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'XLSX-3');

        DailyMortality::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'recorded_at' => '2026-03-12',
            'mortality_count' => 22,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/mortalities.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_water_xlsx_export_downloads(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-xlsx-d', 'owner-xlsx@d.local');
        $this->activeSubscription($tenant);
        $cycle = $this->makeCycle($tenant, 'XLSX-4');

        WaterQualityEntry::query()->create([
            'tenant_id' => $tenant->id,
            'cycle_id' => $cycle->id,
            'pond_id' => $cycle->pond_id,
            'measured_at' => '2026-03-13 06:00:00',
            'dissolved_oxygen_mg_l' => 4.5,
            'ph' => 8.0,
            'temp_c' => 28.1,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/cycles/'.$cycle->id.'/exports/water.xlsx')
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_tenant_isolation_for_report_branding(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $tenantA->update(['company_display_name' => 'Brand A']);
        $tenantB->update(['company_display_name' => 'Brand B']);
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $cycleA = $this->makeCycle($tenantA, 'EX-BRAND');

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/cycles/'.$cycleA->id.'/report')
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
        $plan->features()->create(['feature_key' => 'cost_engine', 'is_enabled' => true]);
        $plan->features()->create(['feature_key' => 'water_quality', 'is_enabled' => true]);

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

    private function makeCycle(Tenant $tenant, string $pondCode): Cycle
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

        return $cycle;
    }
}
