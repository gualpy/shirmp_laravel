<?php

namespace App\Modules\Inventory\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movement_type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'quantity' => ['required', 'numeric'],
            'occurred_at' => ['required', 'date'],
            'reference_type' => ['nullable', Rule::in(['feed_entry', 'water_treatment', 'manual', 'purchase', 'other'])],
            'reference_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
