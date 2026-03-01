<?php

namespace App\Modules\Production\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateStockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stocked_at' => ['sometimes', 'required', 'date'],
            'pl_qty' => ['sometimes', 'required', 'integer', 'min:1'],
            'hatchery_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'batch_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'initial_pp_grams' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
        ];
    }
}
