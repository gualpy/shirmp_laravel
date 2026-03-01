<?php

namespace App\Modules\WaterQuality\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;

final class GetLatestCycleWaterQualityEntryAction extends BaseAction
{
    public function execute(Cycle $cycle): ?WaterQualityEntry
    {
        return $cycle->waterQualityEntries()->latest('measured_at')->first();
    }
}

