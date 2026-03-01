<?php

namespace App\Modules\Configuration\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ResolvedSettingResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'feeding_strategy' => $this->resource['feeding_strategy'],
            'feeding_pct_small' => (float) $this->resource['feeding_pct_small'],
            'feeding_pct_medium' => (float) $this->resource['feeding_pct_medium'],
            'feeding_pct_large' => (float) $this->resource['feeding_pct_large'],
            'allow_post_close_adjustments' => (bool) $this->resource['allow_post_close_adjustments'],
            'unit_system' => $this->resource['unit_system'],
            'decimals_precision' => (int) $this->resource['decimals_precision'],
        ];
    }
}
