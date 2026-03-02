<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Presentation\Requests\StorePlanFeatureRequest;
use App\Modules\SaaS\Presentation\Resources\PlanFeatureResource;
use Illuminate\Http\JsonResponse;

final class AdminPlanFeatureController extends Controller
{
    public function store(Plan $plan, StorePlanFeatureRequest $request, SaaSService $service): JsonResponse
    {
        $feature = $service->upsertPlanFeature($plan, $request->validated());

        return (new PlanFeatureResource($feature))->response()->setStatusCode(201);
    }
}

