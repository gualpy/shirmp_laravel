<?php

namespace App\Modules\SaaS\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePlanLimitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

