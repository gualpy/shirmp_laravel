<?php

namespace App\Modules\SaaS\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class TenantOnboardingService
{
    public function __construct(
        private readonly SaaSService $saasService,
        private readonly AuditLogService $auditLogService,
        private readonly BillingService $billingService,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function onboard(array $payload): Tenant
    {
        return DB::transaction(function () use ($payload): Tenant {
            $tenant = Tenant::query()->create([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'company_display_name' => $payload['company_display_name'] ?? null,
                'company_address' => $payload['company_address'] ?? null,
                'company_phone' => $payload['company_phone'] ?? null,
                'company_email' => $payload['company_email'] ?? null,
                'is_active' => true,
            ]);

            $this->auditLogService->record(
                actionKey: 'tenant.created',
                entityType: 'Tenant',
                entityId: $tenant->id,
                context: [
                    'slug' => $tenant->slug,
                    'name' => $tenant->name,
                ],
                tenant: $tenant,
            );

            $user = User::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $payload['admin_name'],
                'email' => $payload['admin_email'],
                'password' => Hash::make($payload['admin_password']),
                'role' => UserRole::OWNER->value,
            ]);

            $this->auditLogService->record(
                actionKey: 'tenant.admin_user_created',
                entityType: 'User',
                entityId: $user->id,
                context: [
                    'email' => $user->email,
                    'role' => UserRole::OWNER->value,
                ],
                tenant: $tenant,
                user: $user,
            );

            $plan = Plan::query()->findOrFail((int) $payload['plan_id']);
            $subscription = $this->saasService->assignPlan($tenant, $plan, [
                'status' => $payload['subscription_status'],
                'starts_at' => $payload['starts_at'],
                'ends_at' => $payload['ends_at'] ?? null,
                'offline_grace_days' => 7,
            ]);

            $this->auditLogService->record(
                actionKey: 'subscription.created',
                entityType: 'TenantSubscription',
                entityId: $subscription->id,
                context: [
                    'plan_id' => $plan->id,
                    'plan_code' => $plan->code,
                    'status' => $subscription->status->value,
                ],
                tenant: $tenant,
                user: $user,
            );

            $farm = null;
            if ((bool) ($payload['create_initial_farm'] ?? false)) {
                $farm = Farm::query()->create([
                    'tenant_id' => $tenant->id,
                    'name' => $payload['farm_name'],
                    'company_display_name' => $payload['farm_display_name'] ?? null,
                ]);

                $this->auditLogService->record(
                    actionKey: 'farm.created',
                    entityType: 'Farm',
                    entityId: $farm->id,
                    context: ['name' => $farm->name],
                    tenant: $tenant,
                    user: $user,
                );

                if ((bool) ($payload['create_ponds'] ?? false)) {
                    $pondCount = (int) ($payload['pond_count'] ?? 0);
                    for ($i = 1; $i <= $pondCount; $i++) {
                        $pond = Pond::query()->create([
                            'tenant_id' => $tenant->id,
                            'farm_id' => $farm->id,
                            'code' => sprintf('P-%02d', $i),
                            'name' => sprintf('Pond %02d', $i),
                            'area_ha' => 1.00,
                            'avg_depth_m' => 1.20,
                            'is_active' => true,
                        ]);

                        $this->auditLogService->record(
                            actionKey: 'pond.placeholder_created',
                            entityType: 'Pond',
                            entityId: $pond->id,
                            context: ['code' => $pond->code],
                            tenant: $tenant,
                            user: $user,
                        );
                    }
                }
            }

            return $tenant->refresh();
        });
    }

    /**
     * Self-service signup: creates tenant + owner user + subscription + invoice
     * all in a "pending payment" state. No farm/pond scaffolding here (unlike
     * the manual admin onboard()) — the owner sets that up once activated.
     *
     * @param  array<string, mixed>  $payload
     * @return array{tenant: Tenant, user: User, plan: Plan, invoice: \App\Modules\Billing\Domain\Models\BillingInvoice}
     */
    public function onboardPending(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $tenant = Tenant::query()->create([
                'name' => $payload['name'],
                'slug' => $payload['slug'],
                'company_email' => $payload['admin_email'],
                'is_active' => false,
            ]);

            $this->auditLogService->record(
                actionKey: 'tenant.signup_started',
                entityType: 'Tenant',
                entityId: $tenant->id,
                context: ['slug' => $tenant->slug, 'name' => $tenant->name],
                tenant: $tenant,
            );

            $user = User::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $payload['admin_name'],
                'email' => $payload['admin_email'],
                'password' => Hash::make($payload['admin_password']),
                'role' => UserRole::OWNER->value,
            ]);

            $this->auditLogService->record(
                actionKey: 'tenant.admin_user_created',
                entityType: 'User',
                entityId: $user->id,
                context: ['email' => $user->email, 'role' => UserRole::OWNER->value],
                tenant: $tenant,
                user: $user,
            );

            $plan = Plan::query()->where('is_active', true)->findOrFail((int) $payload['plan_id']);

            $subscription = $this->saasService->assignPlan($tenant, $plan, [
                'status' => SubscriptionStatus::PENDING_PAYMENT->value,
                'starts_at' => now(),
            ]);

            $this->auditLogService->record(
                actionKey: 'subscription.pending_created',
                entityType: 'TenantSubscription',
                entityId: $subscription->id,
                context: ['plan_id' => $plan->id, 'plan_code' => $plan->code],
                tenant: $tenant,
                user: $user,
            );

            $billingStart = CarbonImmutable::now()->startOfDay();
            $billingEnd = match ($plan->billing_type) {
                PlanBillingType::YEARLY => $billingStart->addYear(),
                PlanBillingType::LIFETIME => $billingStart->addYears(100),
                default => $billingStart->addMonthNoOverflow(),
            };

            $invoice = $this->billingService->createInvoiceForSubscription($tenant, $subscription, [
                'billing_period_start' => $billingStart->toDateString(),
                'billing_period_end' => $billingEnd->toDateString(),
                'amount_usd' => $plan->price_usd,
                'currency' => 'USD',
                'status' => BillingInvoiceStatus::PENDING->value,
                'issued_at' => now(),
                'due_at' => now(),
                'notes' => 'Factura generada automáticamente por signup self-service.',
            ]);

            return [
                'tenant' => $tenant->refresh(),
                'user' => $user,
                'plan' => $plan,
                'invoice' => $invoice,
            ];
        });
    }

    /** @return list<array{id: int, name: string, code: string, billing_type: string, price_usd: string, highlights: list<string>}> */
    public function signupPlans(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->where('billing_type', '!=', PlanBillingType::ONPREM->value)
            ->whereNotNull('price_usd')
            ->with(['limits', 'features'])
            ->orderBy('price_usd')
            ->get()
            ->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'billing_type' => $plan->billing_type->value,
                'price_usd' => number_format((float) $plan->price_usd, 2),
                'highlights' => $this->planHighlights($plan),
            ])
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function planHighlights(Plan $plan): array
    {
        $limitLabels = [
            'max_farms' => ['granja', 'granjas'],
            'max_ponds' => ['piscina', 'piscinas'],
            'max_cycles_active' => ['ciclo activo', 'ciclos activos'],
            'max_users' => ['usuario', 'usuarios'],
        ];

        $featureLabels = [
            'dashboard' => 'Dashboard operativo',
            'alerts' => 'Alertas automáticas',
            'cost_engine' => 'Motor de costos',
            'water_quality' => 'Calidad de agua',
            'advanced_reports' => 'Reportes avanzados',
            'api_access' => 'Acceso a API',
            'export_excel' => 'Exportación a Excel',
            'export_pdf' => 'Exportación a PDF',
        ];

        $highlights = [];

        foreach ($plan->limits as $limit) {
            if (! isset($limitLabels[$limit->key]) || $limit->value === null) {
                continue;
            }

            [$singular, $plural] = $limitLabels[$limit->key];
            $noun = ((int) $limit->value) === 1 ? $singular : $plural;
            $highlights[] = sprintf('Hasta %d %s', $limit->value, $noun);
        }

        foreach ($plan->features as $feature) {
            if ($feature->is_enabled && isset($featureLabels[$feature->feature_key])) {
                $highlights[] = $featureLabels[$feature->feature_key];
            }
        }

        return $highlights;
    }

    /** @return array<string, mixed> */
    public function formViewModel(): array
    {
        return [
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Plan $plan): array => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'code' => $plan->code,
                    'billing_type' => $plan->billing_type->value,
                    'price_usd' => number_format((float) $plan->price_usd, 2),
                ])
                ->values()
                ->all(),
            'defaults' => [
                'subscription_status' => 'active',
                'starts_at' => now()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addDays(14)->format('Y-m-d\TH:i'),
                'create_initial_farm' => false,
                'create_ponds' => false,
                'pond_count' => 0,
            ],
        ];
    }
}
