<?php

namespace App\Modules\Dashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Application\Services\ExecutiveDashboardService;
use App\Modules\Dashboard\Presentation\Resources\TenantDashboardResource;

final class TenantDashboardController extends Controller
{
    public function __invoke(ExecutiveDashboardService $service): TenantDashboardResource
    {
        return new TenantDashboardResource($service->tenantSummary());
    }
}

