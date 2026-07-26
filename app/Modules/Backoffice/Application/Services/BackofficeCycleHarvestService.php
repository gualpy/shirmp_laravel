<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;

final class BackofficeCycleHarvestService
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
        $cycle->loadMissing(['pond.farm', 'harvests']);

        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');

        return [
            'header' => [
                'cycle_id' => $cycle->id,
                'farm_name' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'started_at' => $cycle->started_at?->format('Y-m-d'),
                'status' => (string) $cycle->status->value,
            ],
            'summary' => [
                'total_harvest_lbs' => $this->metricsService->total_harvest_lbs($cycle),
                'total_harvest_kg' => $this->metricsService->total_harvest_kg($cycle),
            ],
            'rows' => $cycle->harvests()
                ->orderByDesc('harvested_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($harvest): array => [
                    'id' => $harvest->id,
                    'harvested_at' => $harvest->harvested_at?->format('Y-m-d'),
                    'type' => $harvest->type->value,
                    'total_lbs' => (float) $harvest->total_lbs,
                    'avg_pp_grams' => $harvest->avg_pp_grams !== null ? (float) $harvest->avg_pp_grams : null,
                    'guide_number' => $harvest->guide_number,
                    'notes' => $harvest->notes,
                ])
                ->values()
                ->all(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'defaults' => [
                'harvested_at' => now()->format('Y-m-d'),
            ],
        ];
    }
}
