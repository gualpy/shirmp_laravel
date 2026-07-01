<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Dashboard\Application\Services\ExecutiveDashboardService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;

final class BackofficeHomeService
{
    public function __construct(
        private readonly ExecutiveDashboardService $dashboardService,
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $tenant = $this->tenantContext->currentTenant();
        $dashboardEnabled = $tenant ? $this->saasService->checkFeature($tenant, 'dashboard') : true;

        $kpis = null;
        if ($dashboardEnabled) {
            $summary = $this->dashboardService->tenantSummary();
            $kpis = [
                'active_cycles' => (int) $summary['active_cycles'],
                'total_biomass_kg' => (float) $summary['total_biomass_kg'],
                'average_fcr' => $summary['average_fcr'],
                'critical_alerts' => (int) $summary['critical_alerts'],
            ];
        }

        $farmRows = Cycle::query()
            ->where('status', CycleStatus::ACTIVE->value)
            ->with(['pond.farm'])
            ->get()
            ->groupBy(fn (Cycle $cycle): int => (int) $cycle->pond->farm_id)
            ->map(function ($cycles, int $farmId): array {
                /** @var \Illuminate\Support\Collection<int, Cycle> $cycles */
                $first = $cycles->first();
                return [
                    'farm_id' => $farmId,
                    'farm_name' => (string) ($first?->pond?->farm?->name ?? 'Farm'),
                    'active_cycles' => $cycles->count(),
                    'first_cycle_id' => $first?->id,
                ];
            })
            ->sortBy('farm_name')
            ->values()
            ->all();

        return [
            'dashboard_enabled' => $dashboardEnabled,
            'kpis' => $kpis,
            'farms' => $farmRows,
            'cta' => [
                'label' => 'View active cycles',
                'href' => '/backoffice/cycles',
            ],
        ];
    }
}

