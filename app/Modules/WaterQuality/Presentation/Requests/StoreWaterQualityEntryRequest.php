<?php

namespace App\Modules\WaterQuality\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreWaterQualityEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'measured_at' => ['required', 'date'],
            'dissolved_oxygen_mg_l' => ['nullable', 'numeric'],
            'ph' => ['nullable', 'numeric'],
            'temp_c' => ['nullable', 'numeric'],
            'salinity_ppt' => ['nullable', 'numeric'],
            'alkalinity_mg_l' => ['nullable', 'numeric'],
            'ammonia_mg_l' => ['nullable', 'numeric'],
            'nitrite_mg_l' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $metrics = [
                $this->input('dissolved_oxygen_mg_l'),
                $this->input('ph'),
                $this->input('temp_c'),
                $this->input('salinity_ppt'),
                $this->input('alkalinity_mg_l'),
                $this->input('ammonia_mg_l'),
                $this->input('nitrite_mg_l'),
            ];

            $hasAnyMetric = collect($metrics)->contains(fn ($value) => $value !== null && $value !== '');

            if (! $hasAnyMetric) {
                $validator->errors()->add('metrics', 'At least one water quality metric is required.');
            }
        });
    }
}

