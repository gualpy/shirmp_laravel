<?php

namespace App\Modules\Costing\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CycleCostSummaryResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'totals' => $this->resource['totals'],
            'production' => $this->resource['production'],
            'metrics' => $this->resource['metrics'],
            'missing_cost_inputs' => $this->resource['missing_cost_inputs'],
        ];
    }
}
