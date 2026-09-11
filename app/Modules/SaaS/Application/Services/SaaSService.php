<?php

namespace App\Modules\SaaS\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Application\Mail\PlanChangedMail;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Enums\VerificationSource;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Domain\Models\PlanFeature;
use App\Modules\SaaS\Domain\Models\PlanLimit;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class SaaSService
{
    public function getTenantPlan(Tenant $tenant): ?Plan
    {
        $subscription = $this->currentSubscription($tenant);

        return $subscription?->plan;
    }

    public function checkLimit(Tenant $tenant, string $key): bool
    {
        $plan = $this->getTenantPlan($tenant);

        if ($plan === null) {
            return true;
        }

        $limit = $plan->limits()->where('key', $key)->value('value');

        if ($limit === null) {
            return true;
        }

        return $this->currentUsage($tenant, $key) < (int) $limit;
    }

    public function checkFeature(Tenant $tenant, string $featureKey): bool
    {
        $plan = $this->getTenantPlan($tenant);

        if ($plan === null) {
            return true;
        }

        $feature = $plan->features()->where('feature_key', $featureKey)->first();

        if ($feature === null) {
            return false;
        }

        return (bool) $feature->is_enabled;
    }

    public function enforceLimitOrFail(Tenant $tenant, string $key): void
    {
        if (! $this->checkLimit($tenant, $key)) {
            throw new HttpResponseException(new JsonResponse([
                'message' => sprintf('Plan limit reached for `%s`.', $key),
            ], 403));
        }
    }

    public function enforceFeatureOrFail(Tenant $tenant, string $featureKey): void
    {
        if (! $this->checkFeature($tenant, $featureKey)) {
            throw new HttpResponseException(new JsonResponse([
                'message' => sprintf('Feature `%s` is not enabled for the current plan.', $featureKey),
            ], 403));
        }
    }

    public function currentSubscription(Tenant $tenant): ?TenantSubscription
    {
        $subscription = TenantSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('starts_at')
            ->with('plan')
            ->first();

        if ($subscription === null) {
            return null;
        }

        if ($subscription->ends_at !== null && $subscription->ends_at->isPast() && $subscription->status !== SubscriptionStatus::EXPIRED) {
            $subscription->status = SubscriptionStatus::EXPIRED;
            $subscription->save();
            $subscription->refresh();
        }

        return $subscription;
    }

    private function currentUsage(Tenant $tenant, string $key): int
    {
        return match ($key) {
            'max_farms' => Farm::query()->where('tenant_id', $tenant->id)->count(),
            'max_ponds' => Pond::query()->where('tenant_id', $tenant->id)->count(),
            'max_cycles_active' => Cycle::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', CycleStatus::ACTIVE->value)
                ->count(),
            'max_users' => User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count(),
            default => 0,
        };
    }

    public function assertSingleActiveOrTrialSubscription(Tenant $tenant, ?int $ignoreSubscriptionId = null): void
    {
        $query = TenantSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value]);

        if ($ignoreSubscriptionId !== null) {
            $query->where('id', '!=', $ignoreSubscriptionId);
        }

        if ($query->exists()) {
            throw new HttpResponseException(new JsonResponse([
                'message' => 'Tenant already has an active or trial subscription.',
            ], 422));
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function assignPlan(Tenant $tenant, Plan $plan, array $payload): TenantSubscription
    {
        $status = (string) ($payload['status'] ?? SubscriptionStatus::ACTIVE->value);
        $isOnPrem = $plan->billing_type === PlanBillingType::ONPREM;

        if ($isOnPrem && empty($payload['license_key'])) {
            throw ValidationException::withMessages([
                'license_key' => [__('messages.saas.license_key_required')],
            ]);
        }

        if (in_array($status, [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value], true)) {
            $this->assertSingleActiveOrTrialSubscription($tenant);
        }

        return TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'starts_at' => (string) ($payload['starts_at'] ?? now()->toDateTimeString()),
            'ends_at' => $payload['ends_at'] ?? null,
            'license_key' => $payload['license_key'] ?? null,
            'last_verified_at' => $payload['last_verified_at'] ?? ($isOnPrem ? now() : null),
            'offline_grace_days' => (int) ($payload['offline_grace_days'] ?? 7),
            'offline_mode_enabled' => (bool) ($payload['offline_mode_enabled'] ?? $isOnPrem),
            'verification_source' => $payload['verification_source'] ?? ($isOnPrem ? VerificationSource::ONPREM->value : VerificationSource::CLOUD->value),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateSubscription(TenantSubscription $subscription, array $payload): TenantSubscription
    {
        $currentStatus = is_string($subscription->status) ? $subscription->status : $subscription->status->value;
        $nextStatus = (string) ($payload['status'] ?? $currentStatus);

        if (in_array($nextStatus, [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value], true)) {
            $this->assertSingleActiveOrTrialSubscription($subscription->tenant, $subscription->id);
        }

        $previousPlan = null;
        $planIsChanging = isset($payload['plan_id']) && (int) $payload['plan_id'] !== $subscription->plan_id;

        if ($planIsChanging) {
            $previousPlan = $subscription->plan()->first();
        }

        $subscription->fill($payload);

        if (isset($payload['plan_id'])) {
            $subscription->load('plan');
            if ($subscription->plan?->billing_type === PlanBillingType::ONPREM) {
                if (empty($subscription->license_key)) {
                    throw ValidationException::withMessages([
                        'license_key' => [__('messages.saas.license_key_required')],
                    ]);
                }
                $subscription->offline_mode_enabled = true;
                $subscription->verification_source = VerificationSource::ONPREM;
                $subscription->last_verified_at = $subscription->last_verified_at ?? now();
            }
        }

        $subscription->save();
        $subscription->refresh();

        if ($planIsChanging && $previousPlan !== null) {
            $this->sendPlanChangedEmail($subscription, $previousPlan);
        }

        return $subscription;
    }

    private function sendPlanChangedEmail(TenantSubscription $subscription, Plan $previousPlan): void
    {
        $tenant = $subscription->tenant()->first();
        $newPlan = $subscription->plan()->first();

        if ($tenant === null || $newPlan === null) {
            return;
        }

        $owner = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('role', UserRole::OWNER->value)
            ->first();

        if ($owner === null) {
            return;
        }

        Mail::to($owner->email)->send(new PlanChangedMail($tenant, $owner, $previousPlan, $newPlan));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createPlan(array $payload): Plan
    {
        return Plan::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updatePlan(Plan $plan, array $payload): Plan
    {
        $plan->fill($payload);
        $plan->save();

        return $plan->refresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertPlanLimit(Plan $plan, array $payload): PlanLimit
    {
        return PlanLimit::query()->updateOrCreate(
            ['plan_id' => $plan->id, 'key' => (string) $payload['key']],
            ['value' => $payload['value'] ?? null],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertPlanFeature(Plan $plan, array $payload): PlanFeature
    {
        return PlanFeature::query()->updateOrCreate(
            ['plan_id' => $plan->id, 'feature_key' => (string) $payload['feature_key']],
            ['is_enabled' => (bool) ($payload['is_enabled'] ?? false)],
        );
    }
}

