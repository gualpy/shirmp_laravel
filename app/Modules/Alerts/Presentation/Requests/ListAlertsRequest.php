<?php

namespace App\Modules\Alerts\Presentation\Requests;

use App\Modules\Alerts\Domain\Enums\AlertSeverity;
use App\Modules\Alerts\Domain\Enums\AlertCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListAlertsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'farm_id' => ['nullable', 'integer'],
            'cycle_id' => ['nullable', 'integer'],
            'rule_code' => ['nullable', Rule::enum(AlertCode::class)],
            'severity' => ['nullable', Rule::enum(AlertSeverity::class)],
            'is_acknowledged' => ['nullable', 'boolean'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
