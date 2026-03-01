<?php

namespace App\Modules\WaterQuality\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WaterQualityEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'cycle_id' => $this->cycle_id,
            'pond_id' => $this->pond_id,
            'measured_at' => $this->measured_at?->toISOString(),
            'dissolved_oxygen_mg_l' => $this->dissolved_oxygen_mg_l !== null ? (float) $this->dissolved_oxygen_mg_l : null,
            'ph' => $this->ph !== null ? (float) $this->ph : null,
            'temp_c' => $this->temp_c !== null ? (float) $this->temp_c : null,
            'salinity_ppt' => $this->salinity_ppt !== null ? (float) $this->salinity_ppt : null,
            'alkalinity_mg_l' => $this->alkalinity_mg_l !== null ? (float) $this->alkalinity_mg_l : null,
            'ammonia_mg_l' => $this->ammonia_mg_l !== null ? (float) $this->ammonia_mg_l : null,
            'nitrite_mg_l' => $this->nitrite_mg_l !== null ? (float) $this->nitrite_mg_l : null,
            'notes' => $this->notes,
            'measured_by_user_id' => $this->measured_by_user_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

