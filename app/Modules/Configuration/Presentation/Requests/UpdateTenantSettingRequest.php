<?php

namespace App\Modules\Configuration\Presentation\Requests;

use App\Modules\Configuration\Domain\Enums\FeedingStrategy;
use App\Modules\Configuration\Domain\Enums\UnitSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTenantSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feeding_strategy' => ['sometimes', Rule::enum(FeedingStrategy::class)],
            'feeding_pct_small' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'feeding_pct_medium' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'feeding_pct_large' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'allow_post_close_adjustments' => ['sometimes', 'boolean'],
            'unit_system' => ['sometimes', Rule::enum(UnitSystem::class)],
            'decimals_precision' => ['sometimes', 'integer', 'min:0', 'max:6'],
            'default_target_pp_grams' => ['sometimes', 'numeric', 'gt:0', 'max:100'],
            'default_sale_price_per_lb' => ['sometimes', 'numeric', 'gt:0', 'max:1000'],
            'default_feed_cost_factor_per_kg_gain' => ['sometimes', 'numeric', 'gt:0', 'max:100'],
        ];
    }
}
