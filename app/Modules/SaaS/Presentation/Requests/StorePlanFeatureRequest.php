<?php

namespace App\Modules\SaaS\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePlanFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feature_key' => ['required', 'string', 'max:100'],
            'is_enabled' => ['required', 'boolean'],
        ];
    }
}

