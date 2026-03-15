<?php

namespace App\Modules\SaaS\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class TenantOnboardingService
{
    public function __construct(
        private readonly SaaSService $saasService,
        private readonly AuditLogService $auditLogService,
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
