<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Support\Collection;

final class BackofficeTenantAdminService
{
    public function __construct(
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
    ) {
    }

    /** @return array<string, mixed> */
    public function list(array $filters = []): array
    {
        $tenants = Tenant::query()->orderBy('name')->get();

        $rows = $tenants->map(function (Tenant $tenant): array {
            $subscription = $this->saasService->currentSubscription($tenant);
            $readOnly = $this->readOnlyMode($tenant);

            return [
                'id' => $tenant->id,
                'name' => $tenant->company_display_name ?: $tenant->name,
                'slug' => $tenant->slug,
                'plan' => $subscription?->plan?->name,
                'plan_code' => $subscription?->plan?->code,
                'status' => $subscription?->status?->value,
                'billing_type' => $subscription?->plan?->billing_type?->value,
                'read_only_mode' => $readOnly,
                'last_verified_at' => $subscription?->last_verified_at?->format('Y-m-d H:i'),
                'ends_at' => $subscription?->ends_at?->format('Y-m-d H:i'),
            ];
        });

        $filtered = $this->applyFilters($rows, $filters);

        return [
            'rows' => $filtered->values()->all(),
            'filters' => $filters,
            'filter_options' => [
                'plans' => $rows->pluck('plan_code')->filter()->unique()->sort()->values()->all(),
                'statuses' => ['active', 'trial', 'suspended', 'expired'],
                'billing_types' => ['monthly', 'yearly', 'lifetime', 'onprem'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function detail(Tenant $tenant): array
    {
        $subscription = $this->saasService->currentSubscription($tenant)?->loadMissing(['plan.features', 'plan.limits']);
        $readOnly = $this->readOnlyMode($tenant);

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'branding' => $tenant->company_display_name,
                'logo_path' => $tenant->logo_path,
                'contact' => array_values(array_filter([
                    $tenant->company_address,
                    $tenant->company_phone,
                    $tenant->company_email,
                ])),
            ],
            'subscription' => [
                'plan' => $subscription?->plan?->name,
                'plan_code' => $subscription?->plan?->code,
                'status' => $subscription?->status?->value,
                'billing_type' => $subscription?->plan?->billing_type?->value,
                'starts_at' => $subscription?->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $subscription?->ends_at?->format('Y-m-d H:i'),
                'offline_grace_days' => $subscription?->offline_grace_days,
                'last_verified_at' => $subscription?->last_verified_at?->format('Y-m-d H:i'),
                'verification_source' => $subscription?->verification_source?->value,
                'license_key_masked' => $this->maskLicenseKey($subscription),
                'read_only_mode' => $readOnly,
            ],
            'admin_user' => $this->adminUser($tenant),
            'structure' => $this->structureSummary($tenant),
            'features' => $subscription?->plan?->features?->map(fn ($feature): array => [
                'feature_key' => $feature->feature_key,
                'is_enabled' => (bool) $feature->is_enabled,
            ])->values()->all() ?? [],
            'limits' => $subscription?->plan?->limits?->map(fn ($limit): array => [
                'key' => $limit->key,
                'value' => $limit->value,
            ])->values()->all() ?? [],
        ];
    }

    private function readOnlyMode(Tenant $tenant): bool
    {
        try {
            return (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'];
        } catch (\Throwable) {
            return false;
        }
    }

    /** @param Collection<int, array<string, mixed>> $rows */
    private function applyFilters(Collection $rows, array $filters): Collection
    {
        return $rows
            ->when(filled($filters['plan'] ?? null), fn (Collection $c) => $c->where('plan_code', $filters['plan']))
            ->when(filled($filters['status'] ?? null), fn (Collection $c) => $c->where('status', $filters['status']))
            ->when(filled($filters['billing_type'] ?? null), fn (Collection $c) => $c->where('billing_type', $filters['billing_type']))
            ->when(isset($filters['read_only']) && $filters['read_only'] !== '', function (Collection $c) use ($filters) {
                $value = filter_var($filters['read_only'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                return $c->where('read_only_mode', $value);
            });
    }

    private function maskLicenseKey(?TenantSubscription $subscription): ?string
    {
        $licenseKey = $subscription?->license_key;

        if (blank($licenseKey)) {
            return null;
        }

        $len = strlen($licenseKey);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        return substr($licenseKey, 0, 4).'••••'.substr($licenseKey, -4);
    }

    /** @return array<string, mixed>|null */
    private function adminUser(Tenant $tenant): ?array
    {
        $user = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('role', ['Owner', 'Admin'])
            ->orderBy('id')
            ->first();

        if ($user === null) {
            return null;
        }

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => is_string($user->role) ? $user->role : $user->role->value,
        ];
    }

    /** @return array<string, mixed> */
    private function structureSummary(Tenant $tenant): array
    {
        $farm = Farm::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->first();

        return [
            'farm_name' => $farm?->name,
            'pond_count' => Pond::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id)->count(),
        ];
    }
}
