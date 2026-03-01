<?php

namespace App\Modules\Auth\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'tenant_id' => $this->resource->tenant_id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'role' => is_string($this->resource->role) ? $this->resource->role : $this->resource->role->value,
        ];
    }
}
