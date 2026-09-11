<?php

namespace App\Modules\Backoffice\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProjectionSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'default_target_pp_grams' => ['required', 'numeric', 'gt:0'],
            'default_sale_price_per_lb' => ['required', 'numeric', 'gt:0'],
            'default_feed_cost_factor_per_kg_gain' => ['required', 'numeric', 'min:0'],
        ];
    }
}
