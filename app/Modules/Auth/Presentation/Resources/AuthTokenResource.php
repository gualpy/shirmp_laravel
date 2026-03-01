<?php

namespace App\Modules\Auth\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AuthTokenResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token_type' => (string) $this->resource['token_type'],
            'access_token' => (string) $this->resource['access_token'],
            'user' => UserResource::make($this->resource['user'])->resolve($request),
        ];
    }
}
