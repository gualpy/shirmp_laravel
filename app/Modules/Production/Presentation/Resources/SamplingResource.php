<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SamplingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'cycle_id' => $this->cycle_id,
            'sampled_at' => $this->sampled_at?->format('Y-m-d'),
            'pp_grams' => (float) $this->pp_grams,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
