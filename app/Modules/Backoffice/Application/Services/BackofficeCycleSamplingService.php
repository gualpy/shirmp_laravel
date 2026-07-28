<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;

final class BackofficeCycleSamplingService
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
        $cycle->loadMissing(['pond.farm', 'samplings']);

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
                'latest_pp_grams' => $this->metricsService->latest_pp_grams($cycle),
                'growth_g_per_week' => $this->metricsService->growth_g_per_week($cycle),
            ],
            'rows' => $cycle->samplings()
                ->orderByDesc('sampled_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($sampling): array => [
                    'id' => $sampling->id,
                    'sampled_at' => $sampling->sampled_at?->format('Y-m-d'),
                    'pp_grams' => (float) $sampling->pp_grams,
                    'notes' => $sampling->notes,
                ])
                ->values()
                ->all(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'defaults' => [
                'sampled_at' => now()->format('Y-m-d'),
            ],
        ];
    }
}
