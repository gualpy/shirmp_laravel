<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Production\Application\Services\HarvestProjectionService;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Carbon\CarbonImmutable;

final class BackofficeCycleReportService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly CostingService $costingService,
        private readonly HarvestProjectionService $projectionService,
    ) {
    }

    /** @return array<string, mixed> */
    public function build(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm.tenant', 'stocking']);

        $costs = $this->costingService->summarizeCycleCosts($cycle);
        $projection = $this->projectionService->projectCycle($cycle);
        $branding = $this->resolveBranding($cycle);

        return [
            'header' => [
                'farm_name' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'cycle_id' => $cycle->id,
                'started_at' => $cycle->started_at?->format('Y-m-d'),
                'status' => (string) $cycle->status->value,
                'generated_at' => CarbonImmutable::now()->format('Y-m-d H:i'),
            ],
            'branding' => $branding,
            'kpis' => [
                'biomass_kg' => $this->metricsService->biomass_kg($cycle, 1.0),
                'latest_pp_grams' => $this->metricsService->latest_pp_grams($cycle),
                'fcr' => round($this->metricsService->fcr($cycle), 3),
                'total_feed_kg' => round($this->metricsService->total_feed_kg($cycle), 2),
                'total_cost' => round((float) $costs['totals']['total_cost'], 2),
                'total_mortality' => $this->metricsService->total_mortality($cycle),
                'estimated_survival_pct' => $this->metricsService->latest_survival_pct($cycle),
            ],
            'projection' => $projection,
            'recent_alerts' => AlertEvent::query()
                ->where('cycle_id', $cycle->id)
                ->orderByDesc('detected_at')
                ->limit(8)
                ->get()
                ->map(fn (AlertEvent $event): array => [
                    'severity' => is_string($event->severity) ? $event->severity : $event->severity->value,
                    'message' => $event->message,
                    'detected_at' => $event->detected_at?->format('Y-m-d H:i'),
                ])
                ->values()
                ->all(),
            'recent_water' => WaterQualityEntry::query()
                ->where('cycle_id', $cycle->id)
                ->orderByDesc('measured_at')
                ->limit(8)
                ->get()
                ->map(fn (WaterQualityEntry $entry): array => [
                    'measured_at' => $entry->measured_at?->format('Y-m-d H:i'),
                    'do' => $entry->dissolved_oxygen_mg_l,
                    'ph' => $entry->ph,
                    'temp' => $entry->temp_c,
                    'salinity' => $entry->salinity_ppt,
                ])
                ->values()
                ->all(),
            'footer' => [
                'generated_at' => CarbonImmutable::now()->format('Y-m-d H:i'),
                'footer_text' => $branding['footer_text'],
                'system_signature' => 'Generado por Shrimp SaaS',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveBranding(Cycle $cycle): array
    {
        $farm = $cycle->pond?->farm;
        $tenant = $farm?->tenant;

        $brandingName = $farm?->company_display_name
            ?: $tenant?->company_display_name
            ?: $tenant?->company_legal_name
            ?: $farm?->name
            ?: $tenant?->name
            ?: 'Shrimp SaaS';

        $contactInfo = array_values(array_filter([
            $tenant?->company_address,
            $tenant?->company_phone,
            $tenant?->company_email,
        ], fn (?string $value): bool => filled($value)));

        return [
            'branding_name' => $brandingName,
            'branding_legal_name' => $tenant?->company_legal_name,
            'branding_logo' => $farm?->logo_path ?: $tenant?->logo_path,
            'branding_contact_info' => $contactInfo,
            'footer_text' => $tenant?->report_footer_text,
        ];
    }
}
