<?php

namespace App\Modules\Production\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSamplingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sampled_at' => ['required', 'date'],
            'pp_grams' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
