<?php

namespace App\Modules\Production\Application\Services;

use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Enums\HarvestType;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\Shared\Application\Services\BaseService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class ProductionDomainService extends BaseService
{
    public function assertNoOtherActiveCycle(Pond $pond, ?int $ignoreCycleId = null): void
    {
        $query = $pond->cycles()
            ->where('status', CycleStatus::ACTIVE->value);

        if ($ignoreCycleId !== null) {
            $query->where('id', '!=', $ignoreCycleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'status' => ['This pond already has an active cycle.'],
            ]);
        }
    }

    public function assertCycleActive(Cycle $cycle): void
    {
        if ($cycle->status !== CycleStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'cycle' => ['Only active cycles allow this operation.'],
            ]);
        }
    }

    public function assertDateOnOrAfter(string $field, string $value, string $baseline, string $message): void
    {
        if (Carbon::parse($value)->lt(Carbon::parse($baseline))) {
            throw ValidationException::withMessages([
                $field => [$message],
            ]);
        }
    }

    /**
     * @return array{density_pl_ha: float, density_pl_m2: float}
     */
    public function calculateStockingDensity(Pond $pond, int $plQty): array
    {
        $areaHa = (float) $pond->area_ha;

        if ($areaHa <= 0) {
            throw ValidationException::withMessages([
                'area_ha' => ['Pond area_ha must be greater than zero.'],
            ]);
        }

        return [
            'density_pl_ha' => round($plQty / $areaHa, 2),
            'density_pl_m2' => round($plQty / ($areaHa * 10000), 4),
        ];
    }

    public function assertStockingExists(Cycle $cycle): Stocking
    {
        $stocking = $cycle->stocking;

        if ($stocking === null) {
            throw ValidationException::withMessages([
                'stocking' => ['Cycle requires stocking before this operation.'],
            ]);
        }

        return $stocking;
    }

    public function assertSingleFinalHarvest(Cycle $cycle): void
    {
        if ($cycle->harvests()->where('type', HarvestType::FINAL->value)->exists()) {
            throw ValidationException::withMessages([
                'type' => ['A final harvest already exists for this cycle.'],
            ]);
        }
    }

    public function resolveHarvestAvgPpGrams(Cycle $cycle, ?float $avgPpGrams): ?float
    {
        if ($avgPpGrams !== null) {
            return $avgPpGrams;
        }

        $latestSampling = $cycle->samplings()->latest('sampled_at')->first();

        return $latestSampling !== null ? (float) $latestSampling->pp_grams : null;
    }
}
