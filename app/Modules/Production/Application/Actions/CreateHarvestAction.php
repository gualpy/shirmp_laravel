<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\HarvestDataDTO;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Enums\HarvestType;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Support\Facades\DB;

final class CreateHarvestAction extends BaseAction
{
    public function __construct(private readonly ProductionDomainService $domainService)
    {
    }

    public function execute(Cycle $cycle, HarvestDataDTO $dto): Harvest
    {
        $this->domainService->assertCycleActive($cycle);
        $stocking = $this->domainService->assertStockingExists($cycle);

        if ($dto->type === HarvestType::FINAL) {
            $this->domainService->assertSingleFinalHarvest($cycle);
        }

        $this->domainService->assertDateOnOrAfter(
            field: 'harvested_at',
            value: $dto->harvestedAt,
            baseline: $stocking->stocked_at->format('Y-m-d'),
            message: __('messages.harvest.harvested_at_after_stocking'),
        );

        return DB::transaction(function () use ($cycle, $dto): Harvest {
            $harvest = $cycle->harvests()->create(array_merge($dto->toArray(), [
                'avg_pp_grams' => $this->domainService->resolveHarvestAvgPpGrams($cycle, $dto->avgPpGrams),
            ]));

            if ($dto->type === HarvestType::FINAL) {
                $cycle->update([
                    'status' => CycleStatus::HARVESTED->value,
                    'ended_at' => $dto->harvestedAt,
                ]);
            }

            return $harvest;
        });
    }
}
