<?php

namespace App\Modules\Costing\Presentation\Requests;

use App\Modules\Costing\Domain\Enums\OperationalCostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOperationalCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cost_type' => ['required', Rule::enum(OperationalCostType::class)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
