<?php

namespace App\Modules\Production\Application\Services;

use App\Modules\Configuration\Application\Services\SettingsResolverService;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Services\BaseService;
use Carbon\CarbonImmutable;

final class HarvestProjectionService extends BaseService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly CostingService $costingService,
        private readonly SettingsResolverService $settingsResolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function projectCycle(Cycle $cycle): array
    {
        $inputs = $this->resolveProjectionInputs($cycle);

        $targetPp = (float) $inputs['target_pp_grams'];
        $latestPp = $inputs['latest_pp_grams'];
        $latestSurvivalPct = $inputs['latest_survival_pct'];
        $currentBiomassKg = $inputs['current_biomass_kg'];
        $growthPerWeek = $inputs['growth_g_per_week'];
        $currentFcr = $inputs['current_fcr'];
        $currentTotalCost = (float) $inputs['current_total_cost'];
        $salePricePerLb = (float) $inputs['sale_price_per_lb'];
        $feedCostFactor = (float) $inputs['feed_cost_factor_per_kg_gain'];

        $projectedHarvestDate = $this->projectedHarvestDate($latestPp, $targetPp, $growthPerWeek);
        $projectedBiomassKg = $this->projectedBiomassKg($cycle, $latestSurvivalPct, $targetPp);
        $projectedTotalLbs = $projectedBiomassKg !== null
            ? round($projectedBiomassKg / 0.45359237, 2)
            : null;
        $projectedFcr = $currentFcr;
        $projectedRevenue = $projectedTotalLbs !== null
            ? round($projectedTotalLbs * $salePricePerLb, 2)
            : null;
        $projectedCost = $this->projectedCost($currentTotalCost, $currentBiomassKg, $projectedBiomassKg, $feedCostFactor);
        $projectedProfit = ($projectedRevenue !== null && $projectedCost !== null)
            ? round($projectedRevenue - $projectedCost, 2)
            : null;

        return [
            'current' => [
                'latest_pp_grams' => $latestPp,
                'latest_survival_pct' => $latestSurvivalPct,
                'current_biomass_kg' => $currentBiomassKg,
                'current_fcr' => $currentFcr,
                'current_total_cost' => round($currentTotalCost, 2),
            ],
            'projection' => [
                'target_pp_grams' => round($targetPp, 2),
                'projected_avg_pp_grams' => round($targetPp, 2),
                'projected_harvest_date' => $projectedHarvestDate,
                'projected_biomass_kg' => $projectedBiomassKg,
                'projected_total_lbs' => $projectedTotalLbs,
                'projected_fcr' => $projectedFcr,
                'projected_revenue' => $projectedRevenue,
                'projected_cost' => $projectedCost,
                'projected_profit' => $projectedProfit,
            ],
            'assumptions' => [
                'sale_price_per_lb' => round($salePricePerLb, 2),
                'feed_cost_factor_per_kg_gain' => round($feedCostFactor, 2),
                'growth_g_per_week' => $growthPerWeek,
            ],
        ];
    }

    /**
     * @return array<string, float|null>
     */
    public function resolveProjectionInputs(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm', 'stocking']);

        $settings = $this->settingsResolver->resolveFarmSettings($cycle->pond->farm);
        $latestSurvivalPct = $this->metricsService->latest_survival_pct($cycle);
        $latestSurvivalRatio = $latestSurvivalPct !== null ? $latestSurvivalPct / 100 : null;
        $currentHarvestKg = $this->metricsService->total_harvest_kg($cycle);
        $currentFcr = $currentHarvestKg > 0 ? round($this->metricsService->fcr($cycle), 4) : null;

        return [
            'latest_pp_grams' => $this->metricsService->latest_pp_grams($cycle),
            'latest_survival_pct' => $latestSurvivalPct,
            'current_biomass_kg' => $this->metricsService->biomass_kg($cycle, $latestSurvivalRatio),
            'growth_g_per_week' => $this->metricsService->growth_g_per_week($cycle),
            'current_fcr' => $currentFcr,
            'current_total_cost' => round($this->costingService->totalCost($cycle), 2),
            'target_pp_grams' => (float) $settings['default_target_pp_grams'],
            'sale_price_per_lb' => (float) $settings['default_sale_price_per_lb'],
            'feed_cost_factor_per_kg_gain' => (float) $settings['default_feed_cost_factor_per_kg_gain'],
        ];
    }

    private function projectedHarvestDate(?float $latestPp, float $targetPp, ?float $growthPerWeek): ?string
    {
        if ($latestPp === null || $growthPerWeek === null || $growthPerWeek <= 0) {
            return null;
        }

        if ($targetPp <= $latestPp) {
            return CarbonImmutable::today()->toDateString();
        }

        $weeksNeeded = ($targetPp - $latestPp) / $growthPerWeek;
        $daysNeeded = max(1, (int) ceil($weeksNeeded * 7));

        return CarbonImmutable::today()->addDays($daysNeeded)->toDateString();
    }

    private function projectedBiomassKg(Cycle $cycle, ?float $latestSurvivalPct, float $targetPp): ?float
    {
        if ($latestSurvivalPct === null || $cycle->stocking === null) {
            return null;
        }

        $estimatedAliveCount = $this->metricsService->estimated_alive_count($cycle, $latestSurvivalPct / 100);

        if ($estimatedAliveCount === null) {
            return null;
        }

        return round($estimatedAliveCount * ($targetPp / 1000), 2);
    }

    private function projectedCost(
        float $currentTotalCost,
        ?float $currentBiomassKg,
        ?float $projectedBiomassKg,
        float $feedCostFactor,
    ): ?float {
        if ($currentBiomassKg === null || $projectedBiomassKg === null) {
            return null;
        }

        $kgGain = max(0, $projectedBiomassKg - $currentBiomassKg);

        return round($currentTotalCost + ($kgGain * $feedCostFactor), 2);
    }
}
