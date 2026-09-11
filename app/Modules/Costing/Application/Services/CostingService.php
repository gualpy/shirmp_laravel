<?php

namespace App\Modules\Costing\Application\Services;

use App\Modules\Costing\Application\DTO\OperationalCostEntryDTO;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Services\BaseService;

final class CostingService extends BaseService
{
    public function totalFeedCost(Cycle $cycle): float
    {
        return (float) $cycle->feedEntries()
            ->join('feed_types', 'feed_entries.feed_type_id', '=', 'feed_types.id')
            ->whereNotNull('feed_types.cost_per_kg')
            ->selectRaw('COALESCE(SUM(feed_entries.amount_kg * feed_types.cost_per_kg), 0) as total')
            ->value('total');
    }

    public function totalOperationalCost(Cycle $cycle): float
    {
        return (float) $cycle->operationalCosts()->sum('amount');
    }

    public function totalCost(Cycle $cycle): float
    {
        return $this->totalFeedCost($cycle) + $this->totalOperationalCost($cycle);
    }

    public function totalHarvestLbs(Cycle $cycle): float
    {
        return (float) $cycle->harvests()->sum('total_lbs');
    }

    public function costPerLb(Cycle $cycle): ?float
    {
        $totalLbs = $this->totalHarvestLbs($cycle);

        if ($totalLbs <= 0) {
            return null;
        }

        return round($this->totalCost($cycle) / $totalLbs, 4);
    }

    public function costPerHa(Cycle $cycle): float
    {
        $areaHa = (float) $cycle->pond->area_ha;

        if ($areaHa <= 0) {
            return 0.0;
        }

        return round($this->totalCost($cycle) / $areaHa, 4);
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function missingCostInputs(Cycle $cycle): array
    {
        return $cycle->feedEntries()
            ->join('feed_types', 'feed_entries.feed_type_id', '=', 'feed_types.id')
            ->whereNull('feed_types.cost_per_kg')
            ->selectRaw('feed_types.id as id, feed_types.name as name')
            ->distinct()
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function summarizeCycleCosts(Cycle $cycle): array
    {
        $feedCost = round($this->totalFeedCost($cycle), 4);
        $operationalCost = round($this->totalOperationalCost($cycle), 2);
        $totalCost = round($feedCost + $operationalCost, 4);
        $totalHarvestLbs = round($this->totalHarvestLbs($cycle), 2);

        return [
            'totals' => [
                'feed_cost' => $feedCost,
                'operational_cost' => $operationalCost,
                'total_cost' => $totalCost,
            ],
            'production' => [
                'total_harvest_lbs' => $totalHarvestLbs,
            ],
            'metrics' => [
                'cost_per_lb' => $this->costPerLb($cycle),
                'cost_per_ha' => round($this->costPerHa($cycle), 4),
            ],
            'missing_cost_inputs' => $this->missingCostInputs($cycle),
        ];
    }

    public function createOperationalCost(Cycle $cycle, OperationalCostEntryDTO $dto): OperationalCostEntry
    {
        return $cycle->operationalCosts()->create($dto->toArray());
    }
}
