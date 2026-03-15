<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\User;
use App\Modules\Auth\Application\Services\RolePermissionService;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;

final class BackofficeShellService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
        private readonly RolePermissionService $permissionService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(?User $user): array
    {
        $isSuperAdmin = $user?->role === UserRole::SUPER_ADMIN;
        $tenant = $this->tenantContext->currentTenant();
        $subscription = $tenant ? $this->saasService->currentSubscription($tenant) : null;
        $readOnlyMode = $tenant
            ? (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode']
            : false;

        $features = [
            'dashboard' => $tenant ? $this->saasService->checkFeature($tenant, 'dashboard') : true,
            'alerts' => $tenant ? $this->saasService->checkFeature($tenant, 'alerts') : true,
            'cost_engine' => $tenant ? $this->saasService->checkFeature($tenant, 'cost_engine') : true,
            'water_quality' => $tenant ? $this->saasService->checkFeature($tenant, 'water_quality') : true,
        ];
        $permissions = $this->permissionService->permissionMap($user);

        return [
            'tenant_name' => $tenant?->name ?? ($isSuperAdmin ? 'Global' : 'Tenant'),
            'tenant_slug' => $tenant?->slug ?? '',
            'user_name' => $user?->name ?? 'Usuario',
            'plan_code' => $subscription?->plan?->code,
            'is_superadmin' => $isSuperAdmin,
            'read_only_mode' => $readOnlyMode,
            'permissions' => $permissions,
            'write_block_tooltip' => 'Deshabilitado: tenant en modo solo lectura.',
            'permission_block_tooltip' => 'No autorizado para tu rol actual.',
            'menu_disabled_tooltip' => 'Disponible pronto en Backoffice web. Actualmente solo via API.',
            'features' => $features,
            'menu' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/backoffice', 'visible' => $permissions['dashboard.view'], 'enabled' => true],
                ['key' => 'cycles', 'label' => 'Cycles', 'href' => '/backoffice/cycles', 'visible' => $permissions['production.view'], 'enabled' => true],
                ['key' => 'alerts', 'label' => 'Alerts', 'href' => '/backoffice/alerts', 'visible' => $features['alerts'] && $permissions['alerts.view'], 'enabled' => true],
                ['key' => 'audit', 'label' => 'Audit', 'href' => '/backoffice/audit', 'visible' => $permissions['audit.view'], 'enabled' => true],
                ['key' => 'billing', 'label' => 'Billing', 'href' => '/backoffice/billing', 'visible' => $permissions['billing.view'], 'enabled' => true],
                ['key' => 'inventory', 'label' => 'Inventory', 'href' => '/backoffice/inventory', 'visible' => $permissions['inventory.view'], 'enabled' => true],
                ['key' => 'cost_engine', 'label' => 'Costs', 'href' => '#', 'visible' => $features['cost_engine'] && $permissions['costs.view'], 'enabled' => false],
                ['key' => 'water_quality', 'label' => 'Water', 'href' => '/backoffice/water', 'visible' => $features['water_quality'] && $permissions['water.view'], 'enabled' => true],
                ['key' => 'admin', 'label' => 'Admin', 'href' => '/backoffice/admin', 'visible' => $isSuperAdmin, 'enabled' => true],
            ],
        ];
    }
}
