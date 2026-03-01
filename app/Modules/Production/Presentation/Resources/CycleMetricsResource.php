<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CycleMetricsResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'densities' => [
                'pl_m2' => $this->resource['densities']['pl_m2'],
                'pl_ha' => $this->resource['densities']['pl_ha'],
            ],
            'sampling' => [
                'latest_pp_grams' => $this->resource['sampling']['latest_pp_grams'],
                'growth_g_per_week' => $this->resource['sampling']['growth_g_per_week'],
            ],
            'feed' => [
                'total_feed_kg' => $this->resource['feed']['total_feed_kg'],
            ],
            'harvest' => [
                'total_lbs' => $this->resource['harvest']['total_lbs'],
                'total_kg' => $this->resource['harvest']['total_kg'],
            ],
            'fcr' => $this->resource['fcr'],
            'biomass_kg' => $this->resource['biomass_kg'],
        ];
    }
}
