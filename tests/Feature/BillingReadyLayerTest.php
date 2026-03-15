<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Domain\Models\AuditLog;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Multitenancy\TenantScopeBypass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class BillingReadyLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_can_be_created_for_tenant(): void
    {
        $tenant = $this->tenantWithSubscription('tenant-a', 'Tenant A');

        $invoice = app(BillingService::class)->createInvoiceForSubscription($tenant, $tenant->subscriptions()->first(), [
            'billing_period_start' => '2026-03-01',
            'billing_period_end' => '2026-03-31',
            'amount_usd' => 120.50,
            'due_at' => now()->addDays(10),
        ]);

        $this->assertDatabaseHas('billing_invoices', [
            'id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'invoice.created',
            'entity_type' => 'BillingInvoice',
            'entity_id' => $invoice->id,
        ]);
    }

    public function test_manual_payment_can_be_registered(): void
    {
        $tenant = $this->tenantWithSubscription('tenant-a', 'Tenant A');
        $invoice = app(BillingService::class)->createInvoiceForSubscription($tenant, $tenant->subscriptions()->first(), [
            'billing_period_start' => '2026-03-01',
            'billing_period_end' => '2026-03-31',
            'amount_usd' => 120,
        ]);

        $payment = app(BillingService::class)->registerManualPayment($invoice, [
            'amount_usd' => 120,
            'provider_reference' => 'MAN-001',
        ]);

        $this->assertDatabaseHas('billing_payments', [
            'id' => $payment->id,
            'invoice_id' => $invoice->id,
            'status' => 'completed',
        ]);
        $this->assertSame('paid', $invoice->fresh()->status->value);
        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'action_key' => 'payment.manual_registered',
            'entity_type' => 'BillingPayment',
            'entity_id' => $payment->id,
        ]);
    }

    public function test_superadmin_billing_pages_load(): void
    {
        $superAdmin = $this->superAdmin();
        $tenant = $this->tenantWithSubscription('tenant-a', 'Tenant A');
        $invoice = app(BillingService::class)->createInvoiceForSubscription($tenant, $tenant->subscriptions()->first(), [
            'billing_period_start' => '2026-03-01',
            'billing_period_end' => '2026-03-31',
            'amount_usd' => 120,
        ]);

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/billing')
            ->assertOk()
            ->assertSee('Billing global')
            ->assertSee($invoice->invoice_number)
            ->assertSee('Tenant A');

        $this->actingAs($superAdmin)
            ->get('/backoffice/admin/tenants/'.$tenant->id.'/billing')
            ->assertOk()
            ->assertSee('Billing · Tenant A')
            ->assertSee($invoice->invoice_number);
    }

    public function test_tenant_can_view_own_billing(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);
        $invoice = app(BillingService::class)->createInvoiceForSubscription($tenant, $tenant->subscriptions()->first(), [
            'billing_period_start' => '2026-03-01',
            'billing_period_end' => '2026-03-31',
            'amount_usd' => 120,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Tenant', $tenant->slug)
            ->get('/backoffice/billing')
            ->assertOk()
            ->assertSee('Facturación')
            ->assertSee($invoice->invoice_number);
    }

    public function test_tenant_isolation_for_billing(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a', 'owner@a.local');
        [$tenantB, $userB] = $this->tenantUser('tenant-b', 'owner@b.local');
        $this->activeSubscription($tenantA);
        $this->activeSubscription($tenantB);

        $invoiceA = app(BillingService::class)->createInvoiceForSubscription($tenantA, $tenantA->subscriptions()->first(), [
            'billing_period_start' => '2026-03-01',
            'billing_period_end' => '2026-03-31',
            'amount_usd' => 120,
        ]);

        $this->actingAs($userB)
            ->withHeader('X-Tenant', $tenantB->slug)
            ->get('/backoffice/billing')
            ->assertOk()
            ->assertDontSee($invoiceA->invoice_number);
    }

    public function test_non_superadmin_cannot_access_admin_billing(): void
    {
        [$tenant, $user] = $this->tenantUser('tenant-a', 'owner@a.local');
        $this->activeSubscription($tenant);

        $this->actingAs($user)
            ->get('/backoffice/admin/billing')
            ->assertStatus(403);
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

    private function tenantWithSubscription(string $slug, string $name): Tenant
    {
        $tenant = Tenant::factory()->create(['slug' => $slug, 'name' => $name]);
        $plan = Plan::query()->create([
            'code' => 'pro-'.$slug,
            'name' => 'PRO '.strtoupper($slug),
            'billing_type' => PlanBillingType::MONTHLY->value,
            'price_usd' => 120,
            'is_active' => true,
        ]);

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

        return $tenant;
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
            'ends_at' => now()->addDays(25),
            'offline_mode_enabled' => false,
            'offline_grace_days' => 7,
            'verification_source' => 'cloud',
        ]);
    }
}
