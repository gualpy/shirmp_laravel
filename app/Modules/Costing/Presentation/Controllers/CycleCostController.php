<?php

namespace App\Modules\Costing\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Costing\Application\Actions\GetCycleCostsAction;
use App\Modules\Costing\Presentation\Resources\CycleCostSummaryResource;
use App\Modules\Production\Domain\Models\Cycle;

final class CycleCostController extends Controller
{
    public function __invoke(Cycle $cycle, GetCycleCostsAction $action): CycleCostSummaryResource
    {
        return new CycleCostSummaryResource($action->execute($cycle));
    }
}
