<?php

namespace App\Modules\Dashboard\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TenantDashboardResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'active_cycles' => $this->resource['active_cycles'],
            'total_active_area_ha' => $this->resource['total_active_area_ha'],
            'total_biomass_kg' => $this->resource['total_biomass_kg'],
            'total_projected_tons' => $this->resource['total_projected_tons'],
            'average_fcr' => $this->resource['average_fcr'],
            'total_feed_kg' => $this->resource['total_feed_kg'],
            'total_cost' => $this->resource['total_cost'],
            'cost_per_lb_global' => $this->resource['cost_per_lb_global'],
            'critical_alerts' => $this->resource['critical_alerts'],
            'warning_alerts' => $this->resource['warning_alerts'],
            'farms_summary' => $this->resource['farms_summary'],
        ];
    }
}

