<?php

namespace App\Modules\WaterQuality\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\WaterQuality\Application\DTO\WaterQualityEntryDTO;
use App\Modules\WaterQuality\Application\Services\WaterQualityDomainService;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;

final class CreateWaterQualityEntryAction extends BaseAction
{
    public function __construct(private readonly WaterQualityDomainService $domainService)
    {
    }

    public function execute(Cycle $cycle, WaterQualityEntryDTO $dto): WaterQualityEntry
    {
        return $this->domainService->createEntry($cycle, $dto);
    }
}

