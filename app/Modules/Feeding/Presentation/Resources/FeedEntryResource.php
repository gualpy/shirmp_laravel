<?php

namespace App\Modules\Feeding\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeedEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'cycle_id' => $this->cycle_id,
            'feed_type_id' => $this->feed_type_id,
            'fed_at' => $this->fed_at?->format('Y-m-d'),
            'amount_kg' => (float) $this->amount_kg,
            'notes' => $this->notes,
            'feed_type' => $this->relationLoaded('feedType') ? [
                'id' => $this->feedType->id,
                'name' => $this->feedType->name,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
