<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StockingResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'cycle_id' => $this->cycle_id,
            'stocked_at' => $this->stocked_at?->format('Y-m-d'),
            'pl_qty' => $this->pl_qty,
            'hatchery_code' => $this->hatchery_code,
            'batch_code' => $this->batch_code,
            'initial_pp_grams' => $this->initial_pp_grams !== null ? (float) $this->initial_pp_grams : null,
            'density_pl_m2' => (float) $this->density_pl_m2,
            'density_pl_ha' => (float) $this->density_pl_ha,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
