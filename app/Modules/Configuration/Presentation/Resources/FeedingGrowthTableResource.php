<?php

namespace App\Modules\Configuration\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeedingGrowthTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'farm_id' => $this->farm_id,
            'name' => $this->name,
            'is_active' => (bool) $this->is_active,
            'rows' => FeedingGrowthTableRowResource::collection($this->whenLoaded('rows')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
