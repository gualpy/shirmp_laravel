<?php

namespace App\Modules\Production\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{status: string} $resource
 */
final class HealthResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{status: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => (string) ($this->resource['status'] ?? 'ok'),
        ];
    }
}
