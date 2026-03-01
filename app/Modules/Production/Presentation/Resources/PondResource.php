<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PondResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'farm_id' => $this->farm_id,
            'code' => $this->code,
            'name' => $this->name,
            'area_ha' => (float) $this->area_ha,
            'avg_depth_m' => $this->avg_depth_m !== null ? (float) $this->avg_depth_m : null,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
