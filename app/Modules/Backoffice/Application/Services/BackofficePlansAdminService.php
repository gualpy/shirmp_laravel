<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\SaaS\Domain\Models\Plan;

final class BackofficePlansAdminService
{
    /** @return array<int, array<string, mixed>> */
    public function list(): array
    {
        return Plan::query()
            ->with(['limits', 'features'])
            ->orderBy('name')
            ->get()
            ->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'billing_type' => $plan->billing_type->value,
                'price_usd' => $plan->price_usd,
                'is_active' => (bool) $plan->is_active,
                'limits' => $plan->limits->map(fn ($limit): string => $limit->key.': '.($limit->value ?? 'unlimited'))->values()->all(),
                'features' => $plan->features->map(fn ($feature): string => $feature->feature_key.': '.($feature->is_enabled ? 'on' : 'off'))->values()->all(),
            ])
            ->values()
            ->all();
    }
}
