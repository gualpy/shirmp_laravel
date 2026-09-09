<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;

final class BackofficeCycleFeedingService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly CostingService $costingService,
        private readonly TenantContext $tenantContext,
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm', 'feedEntries.feedType']);

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
                'total_feed_kg' => $this->metricsService->total_feed_kg($cycle),
                'total_feed_cost' => $this->costingService->totalFeedCost($cycle),
                'avg_cost_per_kg' => $this->averageCostPerKg($cycle),
            ],
            'rows' => $cycle->feedEntries()
                ->with('feedType:id,name')
                ->orderByDesc('fed_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($entry): array => [
                    'id' => $entry->id,
                    'fed_at_display' => $this->formatFedAt($entry->fed_at),
                    'feed_type' => (string) ($entry->feedType?->name ?? 'N/A'),
                    'amount_kg' => (float) $entry->amount_kg,
                    'notes' => $entry->notes,
                ])
                ->values()
                ->all(),
            'feed_types' => FeedType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (FeedType $type): array => ['id' => $type->id, 'name' => $type->name])
                ->values()
                ->all(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'defaults' => [
                'fed_at' => now()->format('Y-m-d\TH:i'),
            ],
        ];
    }

    /**
     * Feed cost ÷ feed kg, both from the same source CostingService/MetricsService
     * already use elsewhere (Costos del Ciclo). Purely a display ratio of two
     * already-authoritative numbers — not a new cost formula.
     */
    private function averageCostPerKg(Cycle $cycle): ?float
    {
        $totalKg = $this->metricsService->total_feed_kg($cycle);

        if ($totalKg <= 0) {
            return null;
        }

        return round($this->costingService->totalFeedCost($cycle) / $totalKg, 4);
    }

    /**
     * Historical feed entries were saved without a real time-of-day (before
     * time tracking was added), so they read as 00:00. Showing that as a
     * literal time would misrepresent it as "fed at midnight" — show the
     * date only in that case. A genuine non-midnight timestamp is shown in
     * full, matching the rest of the backoffice's date+time style.
     */
    private function formatFedAt(?\Illuminate\Support\Carbon $fedAt): ?string
    {
        if ($fedAt === null) {
            return null;
        }

        if ($fedAt->format('H:i:s') === '00:00:00') {
            return $fedAt->format('d/m/Y');
        }

        $meridiem = $fedAt->format('a') === 'am' ? 'a.m.' : 'p.m.';

        return $fedAt->format('d/m/Y').' · '.$fedAt->format('g:i').' '.$meridiem;
    }
}
