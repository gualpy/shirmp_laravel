<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Production\Application\Services\HarvestProjectionService;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use App\Multitenancy\TenantContext;

final class CycleDetailViewService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly CostingService $costingService,
        private readonly HarvestProjectionService $projectionService,
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
        private readonly TenantContext $tenantContext,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm', 'stocking']);

        $tenant = $this->tenantContext->currentTenant();
        $readOnlyMode = false;

        if ($tenant !== null) {
            $subscriptionState = $this->licenseService->requireActiveOrGrace($tenant);
            $readOnlyMode = (bool) $subscriptionState['read_only_mode'];
        }

        $biomassSeries = $this->biomassSeries($cycle);
        $feedSeries = $this->feedWeeklySeries($cycle);
        $costs = $this->costingService->summarizeCycleCosts($cycle);
        $alerts = $this->alertsTimeline($cycle);
        $water = $this->latestWaterPanel($cycle);
        $projection = $this->projectionService->projectCycle($cycle);

        return [
            'header' => [
                'cycle_id' => $cycle->id,
                'farm_name' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'status' => (string) $cycle->status->value,
                'started_at' => $cycle->started_at?->format('Y-m-d'),
            ],
            'kpis' => [
                'biomass_kg' => round($this->metricsService->biomass_kg($cycle, 1.0) ?? 0, 2),
                'latest_pp_grams' => $this->metricsService->latest_pp_grams($cycle),
                'fcr' => round($this->metricsService->fcr($cycle), 3),
                'total_feed_kg' => round($this->metricsService->total_feed_kg($cycle), 2),
                'total_cost_usd' => round((float) $costs['totals']['total_cost'], 2),
                'open_alerts' => count(array_filter($alerts, fn (array $item): bool => ! $item['is_acknowledged'])),
            ],
            'charts' => [
                'biomass_vs_time' => $biomassSeries,
                'feed_weekly' => $feedSeries,
                'cost_distribution' => [
                    ['label' => 'Alimento', 'value' => (float) $costs['totals']['feed_cost']],
                    ['label' => 'Operación', 'value' => (float) $costs['totals']['operational_cost']],
                ],
                'alerts_timeline' => $alerts,
            ],
            'water_quality_latest' => $water,
            'projection' => $projection,
            'actions' => $this->resolvedActions($cycle, $readOnlyMode, $tenant),
            'read_only_mode' => $readOnlyMode,
            'feature_flags' => [
                'dashboard' => $tenant ? $this->saasService->checkFeature($tenant, 'dashboard') : true,
                'alerts' => $tenant ? $this->saasService->checkFeature($tenant, 'alerts') : true,
                'cost_engine' => $tenant ? $this->saasService->checkFeature($tenant, 'cost_engine') : true,
                'water_quality' => $tenant ? $this->saasService->checkFeature($tenant, 'water_quality') : true,
            ],
        ];
    }

    /**
     * @return array<int, array{date:string, biomass_kg:float, pp_grams:float}>
     */
    private function biomassSeries(Cycle $cycle): array
    {
        $plQty = (int) ($cycle->stocking?->pl_qty ?? 0);

        $rows = $cycle->samplings()
            ->orderBy('sampled_at')
            ->get(['sampled_at', 'pp_grams'])
            ->map(fn ($sampling): array => [
                'date' => $sampling->sampled_at->format('Y-m-d'),
                'biomass_kg' => round(($plQty * ((float) $sampling->pp_grams)) / 1000, 2),
                'pp_grams' => round((float) $sampling->pp_grams, 2),
            ])
            ->values()
            ->all();

        if ($rows === []) {
            return [[
                'date' => $cycle->started_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'biomass_kg' => 0.0,
                'pp_grams' => 0.0,
            ]];
        }

        return $rows;
    }

    /**
     * @return array<int, array{week:string, feed_kg:float}>
     */
    private function feedWeeklySeries(Cycle $cycle): array
    {
        return $cycle->feedEntries()
            ->selectRaw("strftime('%Y-W%W', fed_at) as yweek, SUM(amount_kg) as feed_kg")
            ->groupBy('yweek')
            ->orderBy('yweek')
            ->get()
            ->map(fn ($row): array => [
                'week' => (string) $row->yweek,
                'feed_kg' => round((float) $row->feed_kg, 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function alertsTimeline(Cycle $cycle): array
    {
        return AlertEvent::query()
            ->where('cycle_id', $cycle->id)
            ->orderByDesc('detected_at')
            ->limit(20)
            ->get()
            ->map(fn (AlertEvent $event): array => [
                'id' => $event->id,
                'severity' => is_string($event->severity) ? $event->severity : $event->severity->value,
                'title' => $event->title,
                'message' => $event->message,
                'detected_at' => $event->detected_at?->format('Y-m-d H:i'),
                'is_acknowledged' => (bool) $event->is_acknowledged,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{avg_do: ?float, avg_ph: ?float, avg_temp: ?float}
     */
    private function latestWaterPanel(Cycle $cycle): array
    {
        $latestRows = WaterQualityEntry::query()
            ->where('cycle_id', $cycle->id)
            ->where('measured_at', '>=', now()->subDays(3))
            ->orderByDesc('measured_at')
            ->limit(5)
            ->get();

        if ($latestRows->isEmpty()) {
            return ['avg_do' => null, 'avg_ph' => null, 'avg_temp' => null];
        }

        return [
            'avg_do' => round((float) $latestRows->whereNotNull('dissolved_oxygen_mg_l')->avg('dissolved_oxygen_mg_l'), 2),
            'avg_ph' => round((float) $latestRows->whereNotNull('ph')->avg('ph'), 2),
            'avg_temp' => round((float) $latestRows->whereNotNull('temp_c')->avg('temp_c'), 2),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolvedActions(Cycle $cycle, bool $readOnlyMode, $tenant): array
    {
        $waterEnabled = $tenant ? $this->saasService->checkFeature($tenant, 'water_quality') : true;

        $actions = [
            ['key' => 'sampling', 'label' => 'Record Sampling', 'href' => '/api/v1/cycles/'.$cycle->id.'/samplings', 'method' => 'POST', 'requires_feature' => 'water_quality', 'feature_enabled' => $waterEnabled],
            ['key' => 'feeding', 'label' => 'Record Feeding', 'href' => '/api/v1/cycles/'.$cycle->id.'/feed-entries', 'method' => 'POST', 'requires_feature' => null, 'feature_enabled' => true],
            ['key' => 'harvest', 'label' => 'Record Harvest', 'href' => '/api/v1/cycles/'.$cycle->id.'/harvests', 'method' => 'POST', 'requires_feature' => null, 'feature_enabled' => true],
        ];

        return collect($actions)->map(function (array $action) use ($readOnlyMode): array {
            $disabled = $readOnlyMode || ! $action['feature_enabled'];
            $reason = $readOnlyMode
                ? 'Disabled in read-only mode.'
                : (! $action['feature_enabled'] ? 'Not available in the current plan.' : '');

            $action['disabled'] = $disabled;
            $action['disabled_reason'] = $reason;

            return $action;
        })->values()->all();
    }
}
