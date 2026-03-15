<?php

namespace App\Modules\Inventory\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'category' => ['required', Rule::in(['feed', 'probiotic', 'chemical', 'fuel', 'spare_part', 'other'])],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(['kg', 'lb', 'liter', 'gallon', 'unit', 'sack', 'other'])],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
