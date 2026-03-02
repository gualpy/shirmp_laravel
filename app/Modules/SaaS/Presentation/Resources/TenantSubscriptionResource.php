<?php

namespace App\Modules\SaaS\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TenantSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'plan_id' => $this->plan_id,
            'status' => is_string($this->status) ? $this->status : $this->status->value,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'license_key' => $this->license_key,
            'last_verified_at' => $this->last_verified_at?->toISOString(),
            'offline_grace_days' => $this->offline_grace_days,
            'offline_mode_enabled' => (bool) $this->offline_mode_enabled,
            'verification_source' => is_string($this->verification_source) ? $this->verification_source : $this->verification_source?->value,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
