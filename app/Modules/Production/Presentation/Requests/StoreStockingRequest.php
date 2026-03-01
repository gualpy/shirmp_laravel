<?php

namespace App\Modules\Production\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreStockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stocked_at' => ['required', 'date'],
            'pl_qty' => ['required', 'integer', 'min:1'],
            'hatchery_code' => ['nullable', 'string', 'max:255'],
            'batch_code' => ['nullable', 'string', 'max:255'],
            'initial_pp_grams' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}
