<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Presentation\Requests\StorePlanLimitRequest;
use App\Modules\SaaS\Presentation\Resources\PlanLimitResource;
use Illuminate\Http\JsonResponse;

final class AdminPlanLimitController extends Controller
{
    public function store(Plan $plan, StorePlanLimitRequest $request, SaaSService $service): JsonResponse
    {
        $limit = $service->upsertPlanLimit($plan, $request->validated());

        return (new PlanLimitResource($limit))->response()->setStatusCode(201);
    }
}

