<?php

namespace App\Modules\Production\Presentation\Requests;

use App\Modules\Production\Domain\Enums\HarvestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreHarvestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'harvested_at' => ['required', 'date'],
            'type' => ['nullable', Rule::enum(HarvestType::class)],
            'total_lbs' => ['required', 'numeric', 'gt:0'],
            'avg_pp_grams' => ['nullable', 'numeric', 'gt:0'],
            'guide_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
