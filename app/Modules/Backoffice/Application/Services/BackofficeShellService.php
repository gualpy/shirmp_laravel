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
            'user_name' => $user?->name ?? 'User',
            'plan_code' => $subscription?->plan?->code,
            'is_superadmin' => $isSuperAdmin,
            'read_only_mode' => $readOnlyMode,
            'permissions' => $permissions,
            'write_block_tooltip' => __('layout.write_block_tooltip'),
            'permission_block_tooltip' => __('layout.permission_block_tooltip'),
            'menu_disabled_tooltip' => __('layout.menu_disabled_tooltip'),
            'features' => $features,
            'menu' => [
                ['key' => 'dashboard', 'label' => __('layout.menu_dashboard'), 'href' => '/backoffice', 'visible' => $permissions['dashboard.view'], 'enabled' => true],
                [
                    'key' => 'monitoring',
                    'label' => __('layout.menu_group_monitoring'),
                    'href' => '/backoffice/cycles',
                    'visible' => $permissions['production.view'] || $permissions['water.view'] || $permissions['alerts.view'],
                    'enabled' => true,
                    'children' => [
                        ['key' => 'cycles', 'label' => __('layout.menu_cycles'), 'href' => '/backoffice/cycles', 'visible' => $permissions['production.view'], 'enabled' => true],
                        ['key' => 'water_quality', 'label' => __('layout.menu_water'), 'href' => '/backoffice/water', 'visible' => $features['water_quality'] && $permissions['water.view'], 'enabled' => true],
                        ['key' => 'alerts', 'label' => __('layout.menu_alerts'), 'href' => '/backoffice/alerts', 'visible' => $features['alerts'] && $permissions['alerts.view'], 'enabled' => true],
                    ],
                ],
                [
                    'key' => 'infrastructure',
                    'label' => __('layout.menu_group_infrastructure'),
                    'href' => '/backoffice/farms',
                    'visible' => $permissions['production.view'],
                    'enabled' => true,
                    'children' => [
                        ['key' => 'farms', 'label' => __('layout.menu_farms'), 'href' => '/backoffice/farms', 'visible' => $permissions['production.view'], 'enabled' => true],
                        ['key' => 'ponds', 'label' => __('layout.menu_ponds'), 'href' => '/backoffice/ponds', 'visible' => $permissions['production.view'], 'enabled' => true],
                        ['key' => 'stocking', 'label' => __('layout.menu_stocking'), 'href' => '/backoffice/stocking/create', 'visible' => $permissions['production.view'], 'enabled' => true],
                    ],
                ],
                [
                    'key' => 'resources',
                    'label' => __('layout.menu_group_resources'),
                    'href' => '/backoffice/inventory',
                    'visible' => $permissions['inventory.view'] || $permissions['costs.view'],
                    'enabled' => true,
                    'children' => [
                        ['key' => 'inventory', 'label' => __('layout.menu_inventory'), 'href' => '/backoffice/inventory', 'visible' => $permissions['inventory.view'], 'enabled' => true],
                        ['key' => 'warehouses', 'label' => __('layout.menu_warehouses'), 'href' => '/backoffice/warehouses', 'visible' => $permissions['inventory.view'], 'enabled' => true],
                        ['key' => 'cost_engine', 'label' => __('layout.menu_costs'), 'href' => '#', 'visible' => $features['cost_engine'] && $permissions['costs.view'], 'enabled' => false],
                    ],
                ],
                [
                    'key' => 'administration',
                    'label' => __('layout.menu_group_admin'),
                    'href' => '/backoffice/billing',
                    'visible' => $permissions['billing.view'] || $permissions['audit.view'] || $permissions['settings.view'],
                    'enabled' => true,
                    'children' => [
                        ['key' => 'billing', 'label' => __('layout.menu_billing'), 'href' => '/backoffice/billing', 'visible' => $permissions['billing.view'], 'enabled' => true],
                        ['key' => 'audit', 'label' => __('layout.menu_audit'), 'href' => '/backoffice/audit', 'visible' => $permissions['audit.view'], 'enabled' => true],
                        ['key' => 'settings', 'label' => __('settings.menu_label'), 'href' => '/backoffice/settings', 'visible' => $permissions['settings.view'], 'enabled' => true],
                    ],
                ],
                [
                    'key' => 'admin',
                    'label' => __('layout.menu_admin'),
                    'href' => '/backoffice/admin',
                    'visible' => $isSuperAdmin,
                    'enabled' => true,
                    'children' => [
                        ['key' => 'admin_dashboard', 'label' => __('layout.menu_admin_dashboard'), 'href' => '/backoffice/admin', 'visible' => $isSuperAdmin, 'enabled' => true],
                        ['key' => 'admin_tenants', 'label' => __('layout.menu_admin_tenants'), 'href' => '/backoffice/admin/tenants', 'visible' => $isSuperAdmin, 'enabled' => true],
                        ['key' => 'admin_plans', 'label' => __('layout.menu_admin_plans'), 'href' => '/backoffice/admin/plans', 'visible' => $isSuperAdmin, 'enabled' => true],
                        ['key' => 'admin_billing', 'label' => __('layout.menu_admin_billing'), 'href' => '/backoffice/admin/billing', 'visible' => $isSuperAdmin, 'enabled' => true],
                        ['key' => 'admin_audit', 'label' => __('layout.menu_admin_audit'), 'href' => '/backoffice/admin/audit', 'visible' => $isSuperAdmin, 'enabled' => true],
                        ['key' => 'admin_ops', 'label' => __('layout.menu_admin_ops'), 'href' => '/backoffice/admin/ops', 'visible' => $isSuperAdmin, 'enabled' => true],
                    ],
                ],
            ],
        ];
    }
}
