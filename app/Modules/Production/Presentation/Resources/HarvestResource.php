<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class HarvestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'cycle_id' => $this->cycle_id,
            'harvested_at' => $this->harvested_at?->format('Y-m-d'),
            'type' => is_string($this->type) ? $this->type : $this->type->value,
            'total_lbs' => (float) $this->total_lbs,
            'avg_pp_grams' => $this->avg_pp_grams !== null ? (float) $this->avg_pp_grams : null,
            'guide_number' => $this->guide_number,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
