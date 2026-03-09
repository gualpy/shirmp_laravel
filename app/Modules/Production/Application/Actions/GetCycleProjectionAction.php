<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\Services\HarvestProjectionService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetCycleProjectionAction extends BaseAction
{
    public function __construct(private readonly HarvestProjectionService $projectionService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Cycle $cycle): array
    {
        return $this->projectionService->projectCycle($cycle);
    }
}
