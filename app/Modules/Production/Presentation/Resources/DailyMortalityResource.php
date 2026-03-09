<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DailyMortalityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'cycle_id' => $this->cycle_id,
            'pond_id' => $this->pond_id,
            'recorded_at' => $this->recorded_at?->format('Y-m-d'),
            'mortality_count' => (int) $this->mortality_count,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'pond' => $this->whenLoaded('pond', fn (): array => [
                'id' => $this->pond->id,
                'code' => $this->pond->code,
            ]),
        ];
    }
}
