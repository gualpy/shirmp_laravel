<?php

namespace App\Modules\WaterQuality\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListWaterQualityEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

