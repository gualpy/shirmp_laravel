<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;

final class BackofficeCycleMortalityService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly TenantContext $tenantContext,
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm', 'dailyMortalities.pond']);

        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');

        return [
            'header' => [
                'cycle_id' => $cycle->id,
                'farm_name' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'started_at' => $cycle->started_at?->format('Y-m-d'),
            ],
            'summary' => [
                'total_mortality' => $this->metricsService->total_mortality($cycle),
                'estimated_alive_count' => $this->metricsService->estimated_alive_count($cycle, 1.0),
                'derived_survival_pct' => $this->metricsService->derived_survival_pct($cycle),
            ],
            'rows' => $cycle->dailyMortalities()
                ->with('pond:id,code')
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($entry): array => [
                    'id' => $entry->id,
                    'recorded_at' => $entry->recorded_at?->format('Y-m-d'),
                    'pond' => (string) ($entry->pond?->code ?? 'N/A'),
                    'mortality_count' => (int) $entry->mortality_count,
                    'notes' => $entry->notes,
                ])
                ->values()
                ->all(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'defaults' => [
                'pond_id' => $cycle->pond_id,
                'recorded_at' => now()->format('Y-m-d'),
            ],
        ];
    }
}
