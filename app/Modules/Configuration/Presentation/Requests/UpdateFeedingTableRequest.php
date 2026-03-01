<?php

namespace App\Modules\Configuration\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFeedingTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['sometimes', 'nullable', 'integer', 'exists:farms,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
