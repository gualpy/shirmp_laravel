<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'pond_id' => $this->pond_id,
            'status' => is_string($this->status) ? $this->status : $this->status->value,
            'started_at' => $this->started_at?->format('Y-m-d'),
            'ended_at' => $this->ended_at?->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
