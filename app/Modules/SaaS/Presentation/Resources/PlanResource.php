<?php

namespace App\Modules\SaaS\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'billing_type' => is_string($this->billing_type) ? $this->billing_type : $this->billing_type->value,
            'price_usd' => $this->price_usd !== null ? (float) $this->price_usd : null,
            'is_active' => (bool) $this->is_active,
            'limits' => PlanLimitResource::collection($this->whenLoaded('limits')),
            'features' => PlanFeatureResource::collection($this->whenLoaded('features')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

