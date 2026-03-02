<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Presentation\Requests\StorePlanRequest;
use App\Modules\SaaS\Presentation\Resources\PlanResource;
use Illuminate\Http\JsonResponse;

final class AdminPlanController extends Controller
{
    public function store(StorePlanRequest $request, SaaSService $service): JsonResponse
    {
        $plan = $service->createPlan($request->validated());

        return (new PlanResource($plan))->response()->setStatusCode(201);
    }
}

