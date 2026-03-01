<?php

namespace App\Modules\Configuration\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFeedingTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['nullable', 'integer', 'exists:farms,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
