<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\CycleDataDTO;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateCycleAction extends BaseAction
{
    public function __construct(private readonly ProductionDomainService $domainService)
    {
    }

    public function execute(Pond $pond, CycleDataDTO $dto): Cycle
    {
        if ($dto->status === CycleStatus::ACTIVE) {
            $this->domainService->assertNoOtherActiveCycle($pond);
        }

        return $pond->cycles()->create($dto->toArray());
    }
}
