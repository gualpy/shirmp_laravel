<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;

final class BackofficeCycleListService
{
    public function __construct(private readonly MetricsService $metricsService)
    {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        $farmId = isset($filters['farm']) && $filters['farm'] !== '' ? (int) $filters['farm'] : null;
        $pondId = isset($filters['pond']) && $filters['pond'] !== '' ? (int) $filters['pond'] : null;

        $query = Cycle::query()
            ->where('status', CycleStatus::ACTIVE->value)
            ->with(['pond.farm', 'stocking'])
            ->withCount([
                'alerts as critical_alerts_count' => fn ($q) => $q->where('severity', 'critical')->where('is_acknowledged', false),
                'alerts as warning_alerts_count' => fn ($q) => $q->where('severity', 'warning')->where('is_acknowledged', false),
            ])
            ->when($farmId !== null, fn ($q) => $q->whereHas('pond', fn ($q2) => $q2->where('farm_id', $farmId)))
            ->when($pondId !== null, fn ($q) => $q->where('pond_id', $pondId))
            ->orderByDesc('started_at');

        $rows = $query->get()->map(function (Cycle $cycle): array {
            return [
                'cycle_id' => $cycle->id,
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'farm' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'started_at' => $cycle->started_at?->format('Y-m-d'),
                'biomass_kg' => round($this->metricsService->biomass_kg($cycle, 1.0) ?? 0, 2),
                'latest_pp' => $this->metricsService->latest_pp_grams($cycle),
                'alerts_critical' => (int) ($cycle->critical_alerts_count ?? 0),
                'alerts_warning' => (int) ($cycle->warning_alerts_count ?? 0),
                'detail_href' => '/backoffice/cycles/'.$cycle->id,
            ];
        })->values()->all();

        return [
            'filters' => [
                'farm' => $farmId,
                'pond' => $pondId,
            ],
            'options' => [
                'farms' => Farm::query()->orderBy('name')->get(['id', 'name'])->map(fn (Farm $farm): array => [
                    'id' => $farm->id,
                    'name' => $farm->name,
                ])->values()->all(),
                'ponds' => Pond::query()
                    ->when($farmId !== null, fn ($q) => $q->where('farm_id', $farmId))
                    ->orderBy('code')
                    ->get(['id', 'code'])
                    ->map(fn (Pond $pond): array => [
                        'id' => $pond->id,
                        'code' => $pond->code,
                    ])->values()->all(),
            ],
            'rows' => $rows,
        ];
    }
}

