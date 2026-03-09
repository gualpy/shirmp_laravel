<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CycleProjectionResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'current' => $this->resource['current'],
            'projection' => $this->resource['projection'],
            'assumptions' => $this->resource['assumptions'],
        ];
    }
}
