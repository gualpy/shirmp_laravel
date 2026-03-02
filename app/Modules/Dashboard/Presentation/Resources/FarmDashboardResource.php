<?php

namespace App\Modules\Dashboard\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FarmDashboardResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'active_cycles' => $this->resource['active_cycles'],
            'total_area_ha' => $this->resource['total_area_ha'],
            'biomass_kg' => $this->resource['biomass_kg'],
            'biomass_kg_per_ha' => $this->resource['biomass_kg_per_ha'],
            'avg_growth_g_per_week' => $this->resource['avg_growth_g_per_week'],
            'avg_fcr' => $this->resource['avg_fcr'],
            'total_feed_kg' => $this->resource['total_feed_kg'],
            'total_cost' => $this->resource['total_cost'],
            'cost_per_lb' => $this->resource['cost_per_lb'],
            'water_quality_latest' => $this->resource['water_quality_latest'],
            'alerts' => $this->resource['alerts'],
            'cycles' => $this->resource['cycles'],
        ];
    }
}

