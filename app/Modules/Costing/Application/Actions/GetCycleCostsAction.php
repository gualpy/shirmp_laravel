<?php

namespace App\Modules\Costing\Application\Actions;

use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetCycleCostsAction extends BaseAction
{
    public function __construct(private readonly CostingService $costingService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Cycle $cycle): array
    {
        return $this->costingService->summarizeCycleCosts($cycle);
    }
}
