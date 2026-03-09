<?php

namespace App\Modules\Configuration\Presentation\Requests;

use App\Modules\Configuration\Domain\Enums\FeedingStrategy;
use App\Modules\Configuration\Domain\Enums\UnitSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFarmSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feeding_strategy' => ['sometimes', 'nullable', Rule::enum(FeedingStrategy::class)],
            'feeding_pct_small' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'feeding_pct_medium' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'feeding_pct_large' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'allow_post_close_adjustments' => ['sometimes', 'nullable', 'boolean'],
            'unit_system' => ['sometimes', 'nullable', Rule::enum(UnitSystem::class)],
            'decimals_precision' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:6'],
            'default_target_pp_grams' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100'],
            'default_sale_price_per_lb' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:1000'],
            'default_feed_cost_factor_per_kg_gain' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100'],
        ];
    }
}
