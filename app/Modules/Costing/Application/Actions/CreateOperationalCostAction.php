<?php

namespace App\Modules\Costing\Application\Actions;

use App\Modules\Costing\Application\DTO\OperationalCostEntryDTO;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateOperationalCostAction extends BaseAction
{
    public function __construct(private readonly CostingService $costingService)
    {
    }

    public function execute(Cycle $cycle, OperationalCostEntryDTO $dto): OperationalCostEntry
    {
        return $this->costingService->createOperationalCost($cycle, $dto);
    }
}
