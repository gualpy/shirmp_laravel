<?php

namespace App\Modules\Production\Application\Services;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Production\Domain\Models\SurvivalEstimate;
use App\Modules\Shared\Application\Services\BaseService;

final class MetricsService extends BaseService
{
    public function density_pl_m2(Cycle $cycle): ?float
    {
        $stocking = $cycle->stocking;

        if ($stocking === null) {
            return null;
        }

        if ($stocking->density_pl_m2 !== null) {
            return (float) $stocking->density_pl_m2;
        }

        $areaHa = (float) $cycle->pond->area_ha;

        if ($areaHa <= 0) {
            return null;
        }

        return round(((int) $stocking->pl_qty) / ($areaHa * 10000), 4);
    }

    public function density_pl_ha(Cycle $cycle): ?float
    {
        $stocking = $cycle->stocking;

        if ($stocking === null) {
            return null;
        }

        if ($stocking->density_pl_ha !== null) {
            return (float) $stocking->density_pl_ha;
        }

        $areaHa = (float) $cycle->pond->area_ha;

        if ($areaHa <= 0) {
            return null;
        }

        return round(((int) $stocking->pl_qty) / $areaHa, 2);
    }

    public function latest_pp_grams(Cycle $cycle): ?float
    {
        $sampling = $cycle->samplings()->latest('sampled_at')->first();

        return $sampling !== null ? (float) $sampling->pp_grams : null;
    }

    public function total_feed_kg(Cycle $cycle): float
    {
        return (float) $cycle->feedEntries()->sum('amount_kg');
    }

    public function total_harvest_lbs(Cycle $cycle): float
    {
        return (float) $cycle->harvests()->sum('total_lbs');
    }

    public function total_harvest_kg(Cycle $cycle): float
    {
        return $this->total_harvest_lbs($cycle) * 0.45359237;
    }

    public function fcr(Cycle $cycle): float
    {
        $harvestKg = $this->total_harvest_kg($cycle);

        if ($harvestKg <= 0) {
            return 0.0;
        }

        return round($this->total_feed_kg($cycle) / $harvestKg, 4);
    }

    public function growth_g_per_week(Cycle $cycle): ?float
    {
        $samplings = $cycle->samplings()
            ->orderBy('sampled_at')
            ->get(['sampled_at', 'pp_grams']);

        $count = $samplings->count();

        if ($count < 2) {
            return null;
        }

        if ($count === 2) {
            $days = max(1, (int) $samplings[0]->sampled_at->diffInDays($samplings[1]->sampled_at));
            $delta = (float) $samplings[1]->pp_grams - (float) $samplings[0]->pp_grams;

            return round(($delta / $days) * 7, 4);
        }

        $x = [];
        $y = [];
        $firstDate = $samplings[0]->sampled_at;

        foreach ($samplings as $sampling) {
            $x[] = (float) $firstDate->diffInDays($sampling->sampled_at);
            $y[] = (float) $sampling->pp_grams;
        }

        $xMean = array_sum($x) / $count;
        $yMean = array_sum($y) / $count;

        $num = 0.0;
        $den = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $dx = $x[$i] - $xMean;
            $num += $dx * ($y[$i] - $yMean);
            $den += $dx * $dx;
        }

        if ($den <= 0) {
            return null;
        }

        $slopePerDay = $num / $den;

        return round($slopePerDay * 7, 4);
    }

    public function estimated_alive_count(Cycle $cycle, ?float $survivalEstimate = null): ?float
    {
        $stocking = $cycle->stocking;

        if ($stocking === null) {
            return null;
        }

        $stockedPl = (int) $stocking->pl_qty;
        $totalMortality = $this->total_mortality($cycle);
        $harvestedCount = $this->total_harvest_count($cycle);

        if ($survivalEstimate === null && $totalMortality <= 0 && $harvestedCount <= 0) {
            return null;
        }

        $baseAlive = $survivalEstimate !== null
            ? $stockedPl * $survivalEstimate
            : $stockedPl;

        return round(max(0, $baseAlive - $totalMortality - $harvestedCount), 2);
    }

    public function latest_survival_pct(Cycle $cycle): ?float
    {
        $estimate = $cycle->survivalEstimates()
            ->latest('estimated_at')
            ->latest('id')
            ->first();

        if ($estimate instanceof SurvivalEstimate) {
            return (float) $estimate->survival_pct;
        }

        return $this->derived_survival_pct($cycle);
    }

    public function biomass_kg(Cycle $cycle, ?float $survivalEstimate = null): ?float
    {
        $aliveCount = $this->estimated_alive_count($cycle, $survivalEstimate);
        $latestPpGrams = $this->latest_pp_grams($cycle);

        if ($aliveCount === null || $latestPpGrams === null) {
            return null;
        }

        return round($aliveCount * ($latestPpGrams / 1000), 3);
    }

    public function total_mortality(Cycle $cycle): int
    {
        return (int) $cycle->dailyMortalities()->sum('mortality_count');
    }

    public function total_harvest_count(Cycle $cycle): int
    {
        $count = $cycle->harvests()
            ->get(['total_lbs', 'avg_pp_grams'])
            ->sum(function (Harvest $harvest): float {
                $avgPp = $harvest->avg_pp_grams !== null ? (float) $harvest->avg_pp_grams : 0.0;

                if ($avgPp <= 0) {
                    return 0.0;
                }

                $harvestKg = ((float) $harvest->total_lbs) * 0.45359237;

                return ($harvestKg * 1000) / $avgPp;
            });

        return (int) round($count);
    }

    public function derived_survival_pct(Cycle $cycle): ?float
    {
        $stocking = $cycle->stocking;

        if ($stocking === null) {
            return null;
        }

        $stockedPl = (int) $stocking->pl_qty;

        if ($stockedPl <= 0) {
            return null;
        }

        $totalMortality = $this->total_mortality($cycle);
        $harvestedCount = $this->total_harvest_count($cycle);

        if ($totalMortality <= 0 && $harvestedCount <= 0) {
            return null;
        }

        $aliveCount = max(0, $stockedPl - $totalMortality - $harvestedCount);

        return round(($aliveCount / $stockedPl) * 100, 2);
    }
}
