<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Presentation\Requests\StorePlanRequest;
use App\Modules\SaaS\Presentation\Requests\UpdatePlanRequest;
use App\Modules\SaaS\Presentation\Resources\PlanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminPlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PlanResource::collection(
            Plan::query()->with(['limits', 'features'])->orderBy('name')->get()
        );
    }

    public function store(StorePlanRequest $request, SaaSService $service): JsonResponse
    {
        $plan = $service->createPlan($request->validated());

        return (new PlanResource($plan))->response()->setStatusCode(201);
    }

    public function update(Plan $plan, UpdatePlanRequest $request, SaaSService $service): PlanResource
    {
        return new PlanResource($service->updatePlan($plan, $request->validated())->load(['limits', 'features']));
    }
}
