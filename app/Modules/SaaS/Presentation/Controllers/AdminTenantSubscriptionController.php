<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Modules\SaaS\Presentation\Requests\AssignPlanToTenantRequest;
use App\Modules\SaaS\Presentation\Requests\UpdateSubscriptionRequest;
use App\Modules\SaaS\Presentation\Resources\TenantSubscriptionResource;
use Illuminate\Http\JsonResponse;

final class AdminTenantSubscriptionController extends Controller
{
    public function assignPlan(Tenant $tenant, AssignPlanToTenantRequest $request, SaaSService $service): JsonResponse
    {
        $payload = $request->validated();
        $plan = Plan::query()->findOrFail((int) $payload['plan_id']);

        if ($plan->billing_type->value === 'onprem' && empty($payload['license_key'])) {
            return response()->json(['message' => 'license_key is required for onprem plans.'], 422);
        }

        $subscription = $service->assignPlan($tenant, $plan, $payload);

        return (new TenantSubscriptionResource($subscription))->response()->setStatusCode(201);
    }

    public function update(
        TenantSubscription $subscription,
        UpdateSubscriptionRequest $request,
        SaaSService $service,
    ): TenantSubscriptionResource {
        $payload = $request->validated();

        if (isset($payload['plan_id'])) {
            $plan = Plan::query()->findOrFail((int) $payload['plan_id']);
            if ($plan->billing_type->value === 'onprem' && empty($payload['license_key']) && empty($subscription->license_key)) {
                abort(422, 'license_key is required for onprem plans.');
            }
        }

        return new TenantSubscriptionResource($service->updateSubscription($subscription, $payload));
    }
}

