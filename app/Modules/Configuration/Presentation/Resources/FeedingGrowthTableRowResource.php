<?php

namespace App\Modules\Configuration\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeedingGrowthTableRowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'table_id' => $this->table_id,
            'day_from' => $this->day_from,
            'day_to' => $this->day_to,
            'pp_from_grams' => $this->pp_from_grams !== null ? (float) $this->pp_from_grams : null,
            'pp_to_grams' => $this->pp_to_grams !== null ? (float) $this->pp_to_grams : null,
            'feed_pct' => (float) $this->feed_pct,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
