<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\User;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;

final class BackofficeShellService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(?User $user): array
    {
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

        return [
            'tenant_name' => $tenant?->name ?? 'Tenant',
            'tenant_slug' => $tenant?->slug ?? '',
            'user_name' => $user?->name ?? 'Usuario',
            'plan_code' => $subscription?->plan?->code,
            'read_only_mode' => $readOnlyMode,
            'write_block_tooltip' => 'Deshabilitado: tenant en modo solo lectura.',
            'menu_disabled_tooltip' => 'Disponible pronto en Backoffice web. Actualmente solo via API.',
            'features' => $features,
            'menu' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/backoffice', 'visible' => true, 'enabled' => true],
                ['key' => 'cycles', 'label' => 'Cycles', 'href' => '/backoffice/cycles', 'visible' => true, 'enabled' => true],
                ['key' => 'alerts', 'label' => 'Alerts', 'href' => '/backoffice/alerts', 'visible' => $features['alerts'], 'enabled' => true],
                ['key' => 'cost_engine', 'label' => 'Costs', 'href' => '#', 'visible' => $features['cost_engine'], 'enabled' => false],
                ['key' => 'water_quality', 'label' => 'Water', 'href' => '/backoffice/water', 'visible' => $features['water_quality'], 'enabled' => true],
            ],
        ];
    }
}
