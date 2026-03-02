<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\JsonResponse;

final class SubscriptionStatusController extends Controller
{
    public function __invoke(
        TenantContext $tenantContext,
        SaaSService $saasService,
        LicenseService $licenseService,
    ): JsonResponse {
        $tenant = $tenantContext->currentTenant();
        if ($tenant === null) {
            return response()->json(['message' => 'Tenant context is not available.'], 400);
        }

        $subscription = $saasService->currentSubscription($tenant);
        if ($subscription === null) {
            return response()->json([
                'status' => null,
                'plan' => null,
                'ends_at' => null,
                'last_verified_at' => null,
                'offline_grace_days' => null,
                'read_only_mode' => false,
            ]);
        }

        $readOnly = false;
        if (! in_array($subscription->status->value, ['active', 'trial'], true)) {
            $readOnly = $subscription->offline_mode_enabled && $licenseService->isWithinOfflineGrace($tenant);
        }

        return response()->json([
            'status' => $subscription->status->value,
            'plan' => [
                'id' => $subscription->plan?->id,
                'code' => $subscription->plan?->code,
                'name' => $subscription->plan?->name,
                'billing_type' => $subscription->plan?->billing_type?->value,
            ],
            'ends_at' => $subscription->ends_at?->toISOString(),
            'last_verified_at' => $subscription->last_verified_at?->toISOString(),
            'offline_grace_days' => $subscription->offline_grace_days,
            'read_only_mode' => $readOnly,
        ]);
    }
}

