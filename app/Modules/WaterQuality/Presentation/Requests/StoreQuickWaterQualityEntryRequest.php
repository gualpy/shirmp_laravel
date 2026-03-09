<?php

namespace App\Modules\WaterQuality\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreQuickWaterQualityEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'pond' => ['required', 'integer'],
            'measured_at' => ['required', 'date'],
            'do' => ['nullable', 'numeric'],
            'ph' => ['nullable', 'numeric'],
            'temperature' => ['nullable', 'numeric'],
            'salinity' => ['nullable', 'numeric'],
            'alkalinity' => ['nullable', 'numeric'],
            'ammonia' => ['nullable', 'numeric'],
            'nitrite' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $metrics = [
                $this->input('do'),
                $this->input('ph'),
                $this->input('temperature'),
                $this->input('salinity'),
                $this->input('alkalinity'),
                $this->input('ammonia'),
                $this->input('nitrite'),
            ];

            $hasAnyMetric = collect($metrics)->contains(fn ($value) => $value !== null && $value !== '');

            if (! $hasAnyMetric) {
                $validator->errors()->add('metrics', 'At least one water quality metric is required.');
            }
        });
    }

    /** @return array<string, mixed> */
    public function normalized(): array
    {
        return [
            'pond_id' => (int) $this->integer('pond'),
            'measured_at' => (string) $this->input('measured_at'),
            'dissolved_oxygen_mg_l' => $this->filled('do') ? (float) $this->input('do') : null,
            'ph' => $this->filled('ph') ? (float) $this->input('ph') : null,
            'temp_c' => $this->filled('temperature') ? (float) $this->input('temperature') : null,
            'salinity_ppt' => $this->filled('salinity') ? (float) $this->input('salinity') : null,
            'alkalinity_mg_l' => $this->filled('alkalinity') ? (float) $this->input('alkalinity') : null,
            'ammonia_mg_l' => $this->filled('ammonia') ? (float) $this->input('ammonia') : null,
            'nitrite_mg_l' => $this->filled('nitrite') ? (float) $this->input('nitrite') : null,
            'notes' => $this->filled('notes') ? (string) $this->input('notes') : null,
        ];
    }
}
