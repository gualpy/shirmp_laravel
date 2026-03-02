<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\LicenseActivation;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Enums\VerificationSource;
use App\Modules\SaaS\Presentation\Requests\ActivateOnPremRequest;
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

    public function activateOnPrem(
        Tenant $tenant,
        ActivateOnPremRequest $request,
        SaaSService $service,
        LicenseService $licenseService,
    ): JsonResponse {
        $plan = Plan::query()->where('code', 'onprem')->where('is_active', true)->first();
        if ($plan === null || $plan->billing_type !== PlanBillingType::ONPREM) {
            return response()->json(['message' => 'Active onprem plan is not configured.'], 422);
        }

        $payload = $request->validated();
        $subscription = $service->currentSubscription($tenant);

        if ($subscription === null) {
            $subscription = $service->assignPlan($tenant, $plan, [
                'status' => SubscriptionStatus::ACTIVE->value,
                'starts_at' => now()->toDateTimeString(),
                'license_key' => $payload['license_key'],
                'offline_grace_days' => $payload['offline_grace_days'] ?? 7,
                'offline_mode_enabled' => true,
                'last_verified_at' => now(),
                'verification_source' => VerificationSource::ONPREM->value,
            ]);
        } else {
            $subscription = $service->updateSubscription($subscription, [
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::ACTIVE->value,
                'license_key' => $payload['license_key'],
                'offline_grace_days' => $payload['offline_grace_days'] ?? $subscription->offline_grace_days ?? 7,
                'offline_mode_enabled' => true,
                'last_verified_at' => now(),
                'verification_source' => VerificationSource::ONPREM->value,
            ]);
        }

        $licenseService->ensureOnPremLicenseRequirements($subscription->load('plan'));

        LicenseActivation::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'activated_at' => now(),
            'machine_fingerprint' => $payload['machine_fingerprint'] ?? null,
            'activated_by_user_id' => $request->user()?->id,
            'notes' => $payload['notes'] ?? null,
        ]);

        return (new TenantSubscriptionResource($subscription))->response()->setStatusCode(201);
    }

    public function verifyNow(
        Tenant $tenant,
        LicenseService $licenseService,
    ): TenantSubscriptionResource|JsonResponse {
        $subscription = $licenseService->verifySubscriptionNow($tenant, VerificationSource::MANUAL);

        if ($subscription === null) {
            return response()->json(['message' => 'Tenant subscription not found.'], 404);
        }

        return new TenantSubscriptionResource($subscription);
    }
}
