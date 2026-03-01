<?php

namespace App\Modules\Alerts\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AlertEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'farm_id' => $this->farm_id,
            'cycle_id' => $this->cycle_id,
            'rule_code' => $this->rule_code,
            'severity' => is_string($this->severity) ? $this->severity : $this->severity->value,
            'title' => $this->title,
            'message' => $this->message,
            'detected_at' => $this->detected_at?->toISOString(),
            'context_json' => $this->context_json,
            'is_acknowledged' => (bool) $this->is_acknowledged,
            'acknowledged_by_user_id' => $this->acknowledged_by_user_id,
            'acknowledged_at' => $this->acknowledged_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
