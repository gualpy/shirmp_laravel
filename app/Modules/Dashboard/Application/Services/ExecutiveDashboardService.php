<?php

namespace App\Modules\Dashboard\Application\Services;

use App\Modules\Alerts\Application\Services\AlertEngineService;
use App\Modules\Alerts\Domain\Enums\AlertSeverity;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class ExecutiveDashboardService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly CostingService $costingService,
        private readonly AlertEngineService $alertEngineService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function tenantSummary(): array
    {
        /** @var Collection<int, Cycle> $activeCycles */
        $activeCycles = Cycle::query()
            ->where('status', CycleStatus::ACTIVE->value)
            ->with(['pond:id,farm_id,code,area_ha', 'pond.farm:id,name', 'stocking:id,cycle_id,pl_qty'])
            ->get();

        $activeCycleIds = $activeCycles->pluck('id')->all();
        $activeCycleCount = $activeCycles->count();

        $totalActiveAreaHa = round((float) $activeCycles->sum(fn (Cycle $cycle) => (float) ($cycle->pond?->area_ha ?? 0)), 2);
        $totalFeedKg = $this->sumFeedKgByCycleIds($activeCycleIds);
        $totalBiomassKg = round($this->sumBiomassForCycles($activeCycles), 3);
        $totalProjectedTons = round($totalBiomassKg / 1000, 3);

        $totalCost = $this->totalCostAllCycles();
        $totalHarvestLbs = (float) Harvest::query()->sum('total_lbs');
        $costPerLbGlobal = $totalHarvestLbs > 0 ? round($totalCost / $totalHarvestLbs, 4) : null;
        $averageFcr = $this->averageFcrAcrossCycles();

        $criticalAlerts = AlertEvent::query()
            ->where('severity', AlertSeverity::CRITICAL->value)
            ->where('is_acknowledged', false)
            ->count();

        $warningAlerts = AlertEvent::query()
            ->where('severity', AlertSeverity::WARNING->value)
            ->where('is_acknowledged', false)
            ->count();

        $farmSummaries = $activeCycles
            ->groupBy(fn (Cycle $cycle) => (int) $cycle->pond->farm_id)
            ->map(function ($cycles, int $farmId): array {
                /** @var Collection<int, Cycle> $cycles */
                $biomass = round($this->sumBiomassForCycles($cycles), 3);
                $alerts = AlertEvent::query()
                    ->where('farm_id', $farmId)
                    ->where('is_acknowledged', false)
                    ->count();

                $firstCycle = $cycles->first();

                return [
                    'farm_id' => $farmId,
                    'farm_name' => (string) ($firstCycle?->pond?->farm?->name ?? ''),
                    'active_cycles' => $cycles->count(),
                    'biomass_kg' => $biomass,
                    'alerts' => $alerts,
                ];
            })
            ->sortBy('farm_name')
            ->values()
            ->all();

        return [
            'active_cycles' => $activeCycleCount,
            'total_active_area_ha' => $totalActiveAreaHa,
            'total_biomass_kg' => $totalBiomassKg,
            'total_projected_tons' => $totalProjectedTons,
            'average_fcr' => $averageFcr,
            'total_feed_kg' => round($totalFeedKg, 3),
            'total_cost' => round($totalCost, 4),
            'cost_per_lb_global' => $costPerLbGlobal,
            'critical_alerts' => $criticalAlerts,
            'warning_alerts' => $warningAlerts,
            'farms_summary' => $farmSummaries,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function farmSummary(Farm $farm): array
    {
        /** @var Collection<int, Cycle> $activeCycles */
        $activeCycles = Cycle::query()
            ->where('status', CycleStatus::ACTIVE->value)
            ->whereHas('pond', fn ($query) => $query->where('farm_id', $farm->id))
            ->with(['pond:id,farm_id,code,area_ha', 'stocking:id,cycle_id,pl_qty'])
            ->get();

        $activeCycleIds = $activeCycles->pluck('id')->all();
        $activeCycleCount = $activeCycles->count();
        $totalAreaHa = round((float) $activeCycles->sum(fn (Cycle $cycle) => (float) ($cycle->pond?->area_ha ?? 0)), 2);
        $biomassKg = round($this->sumBiomassForCycles($activeCycles), 3);
        $biomassKgPerHa = $totalAreaHa > 0 ? round($biomassKg / $totalAreaHa, 3) : 0.0;
        $avgGrowth = $this->averageGrowthForCycleIds($activeCycleIds);
        $avgFcr = $this->averageFcrForCycleIds($activeCycleIds);
        $totalFeedKg = $this->sumFeedKgByCycleIds($activeCycleIds);
        $totalCost = round($this->totalCostForCycleIds($activeCycleIds), 4);
        $totalHarvestLbs = (float) Harvest::query()->whereIn('cycle_id', $activeCycleIds)->sum('total_lbs');
        $costPerLb = $totalHarvestLbs > 0 ? round($totalCost / $totalHarvestLbs, 4) : null;

        $waterQualityLatest = $this->farmLatestWaterQualityAverages($activeCycleIds);

        $alertsBySeverity = AlertEvent::query()
            ->where('farm_id', $farm->id)
            ->where('is_acknowledged', false)
            ->selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $cycles = $activeCycles->map(function (Cycle $cycle): array {
            $biomass = $this->metricsService->biomass_kg($cycle, 1.0);
            $latestPp = $this->metricsService->latest_pp_grams($cycle);
            $fcr = $this->metricsService->fcr($cycle);
            $alerts = AlertEvent::query()
                ->where('cycle_id', $cycle->id)
                ->where('is_acknowledged', false)
                ->count();

            return [
                'cycle_id' => $cycle->id,
                'pond_code' => (string) ($cycle->pond?->code ?? ''),
                'biomass_kg' => $biomass !== null ? round($biomass, 3) : 0.0,
                'latest_pp' => $latestPp !== null ? round($latestPp, 2) : null,
                'fcr' => $fcr > 0 ? round($fcr, 4) : null,
                'alerts' => $alerts,
            ];
        })->values()->all();

        return [
            'active_cycles' => $activeCycleCount,
            'total_area_ha' => $totalAreaHa,
            'biomass_kg' => $biomassKg,
            'biomass_kg_per_ha' => $biomassKgPerHa,
            'avg_growth_g_per_week' => $avgGrowth,
            'avg_fcr' => $avgFcr,
            'total_feed_kg' => round($totalFeedKg, 3),
            'total_cost' => $totalCost,
            'cost_per_lb' => $costPerLb,
            'water_quality_latest' => $waterQualityLatest,
            'alerts' => [
                'critical' => (int) ($alertsBySeverity[AlertSeverity::CRITICAL->value] ?? 0),
                'warning' => (int) ($alertsBySeverity[AlertSeverity::WARNING->value] ?? 0),
                'info' => (int) ($alertsBySeverity[AlertSeverity::INFO->value] ?? 0),
            ],
            'cycles' => $cycles,
        ];
    }

    /**
     * @param  array<int>  $cycleIds
     */
    private function sumFeedKgByCycleIds(array $cycleIds): float
    {
        if ($cycleIds === []) {
            return 0.0;
        }

        return (float) FeedEntry::query()
            ->whereIn('cycle_id', $cycleIds)
            ->sum('amount_kg');
    }

    /**
     * @param  Collection<int, Cycle>  $cycles
     */
    private function sumBiomassForCycles(Collection $cycles): float
    {
        $sum = 0.0;

        foreach ($cycles as $cycle) {
            $sum += $this->metricsService->biomass_kg($cycle, 1.0) ?? 0.0;
        }

        return $sum;
    }

    private function totalCostAllCycles(): float
    {
        $feedCost = (float) FeedEntry::query()
            ->join('feed_types', 'feed_entries.feed_type_id', '=', 'feed_types.id')
            ->whereNotNull('feed_types.cost_per_kg')
            ->selectRaw('COALESCE(SUM(feed_entries.amount_kg * feed_types.cost_per_kg), 0) as total')
            ->value('total');

        $operational = (float) OperationalCostEntry::query()->sum('amount');

        return $feedCost + $operational;
    }

    /**
     * @param  array<int>  $cycleIds
     */
    private function totalCostForCycleIds(array $cycleIds): float
    {
        if ($cycleIds === []) {
            return 0.0;
        }

        $feedCost = (float) FeedEntry::query()
            ->join('feed_types', 'feed_entries.feed_type_id', '=', 'feed_types.id')
            ->whereIn('feed_entries.cycle_id', $cycleIds)
            ->whereNotNull('feed_types.cost_per_kg')
            ->selectRaw('COALESCE(SUM(feed_entries.amount_kg * feed_types.cost_per_kg), 0) as total')
            ->value('total');

        $operational = (float) OperationalCostEntry::query()
            ->whereIn('cycle_id', $cycleIds)
            ->sum('amount');

        return $feedCost + $operational;
    }

    private function averageFcrAcrossCycles(): ?float
    {
        $cycles = Cycle::query()
            ->withSum('feedEntries as feed_kg', 'amount_kg')
            ->withSum('harvests as harvest_lbs', 'total_lbs')
            ->get(['id']);

        $fcrValues = $cycles
            ->map(function (Cycle $cycle): ?float {
                $harvestLbs = (float) ($cycle->harvest_lbs ?? 0);
                if ($harvestLbs <= 0) {
                    return null;
                }

                $harvestKg = $harvestLbs * 0.45359237;
                if ($harvestKg <= 0) {
                    return null;
                }

                return ((float) ($cycle->feed_kg ?? 0)) / $harvestKg;
            })
            ->filter(fn (?float $value) => $value !== null)
            ->values();

        if ($fcrValues->isEmpty()) {
            return null;
        }

        return round((float) $fcrValues->avg(), 4);
    }

    /**
     * @param  array<int>  $cycleIds
     */
    private function averageFcrForCycleIds(array $cycleIds): ?float
    {
        if ($cycleIds === []) {
            return null;
        }

        $cycles = Cycle::query()
            ->whereIn('id', $cycleIds)
            ->withSum('feedEntries as feed_kg', 'amount_kg')
            ->withSum('harvests as harvest_lbs', 'total_lbs')
            ->get(['id']);

        $values = $cycles
            ->map(function (Cycle $cycle): ?float {
                $harvestLbs = (float) ($cycle->harvest_lbs ?? 0);
                if ($harvestLbs <= 0) {
                    return null;
                }

                $harvestKg = $harvestLbs * 0.45359237;
                if ($harvestKg <= 0) {
                    return null;
                }

                return ((float) ($cycle->feed_kg ?? 0)) / $harvestKg;
            })
            ->filter(fn (?float $value) => $value !== null)
            ->values();

        if ($values->isEmpty()) {
            return null;
        }

        return round((float) $values->avg(), 4);
    }

    /**
     * @param  array<int>  $cycleIds
     */
    private function averageGrowthForCycleIds(array $cycleIds): ?float
    {
        if ($cycleIds === []) {
            return null;
        }

        $values = Cycle::query()
            ->whereIn('id', $cycleIds)
            ->get()
            ->map(fn (Cycle $cycle): ?float => $this->metricsService->growth_g_per_week($cycle))
            ->filter(fn (?float $value): bool => $value !== null)
            ->values();

        if ($values->isEmpty()) {
            return null;
        }

        return round((float) $values->avg(), 4);
    }

    /**
     * @param  array<int>  $cycleIds
     * @return array{avg_do: ?float, avg_ph: ?float, avg_temp: ?float}
     */
    private function farmLatestWaterQualityAverages(array $cycleIds): array
    {
        if ($cycleIds === []) {
            return [
                'avg_do' => null,
                'avg_ph' => null,
                'avg_temp' => null,
            ];
        }

        $entries = WaterQualityEntry::query()
            ->whereIn('cycle_id', $cycleIds)
            ->where('measured_at', '>=', Carbon::now()->subDays(3))
            ->orderByDesc('measured_at')
            ->get();

        $latestByCycle = $entries->unique('cycle_id')->values();

        $avgDo = $this->averageNullableDecimal($latestByCycle->pluck('dissolved_oxygen_mg_l')->all(), 2);
        $avgPh = $this->averageNullableDecimal($latestByCycle->pluck('ph')->all(), 2);
        $avgTemp = $this->averageNullableDecimal($latestByCycle->pluck('temp_c')->all(), 2);

        return [
            'avg_do' => $avgDo,
            'avg_ph' => $avgPh,
            'avg_temp' => $avgTemp,
        ];
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function averageNullableDecimal(array $values, int $precision): ?float
    {
        $numeric = collect($values)
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value)
            ->values();

        if ($numeric->isEmpty()) {
            return null;
        }

        return round((float) $numeric->avg(), $precision);
    }
}

