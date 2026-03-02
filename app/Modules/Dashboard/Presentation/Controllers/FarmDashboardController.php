<?php

namespace App\Modules\Dashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Application\Services\ExecutiveDashboardService;
use App\Modules\Dashboard\Presentation\Resources\FarmDashboardResource;
use App\Modules\Production\Domain\Models\Farm;

final class FarmDashboardController extends Controller
{
    public function __invoke(Farm $farm, ExecutiveDashboardService $service): FarmDashboardResource
    {
        return new FarmDashboardResource($service->farmSummary($farm));
    }
}

