<?php

namespace App\Modules\Configuration\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFeedingTableRowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_from' => ['required', 'integer', 'min:1'],
            'day_to' => ['required', 'integer', 'gte:day_from'],
            'pp_from_grams' => ['nullable', 'numeric', 'min:0'],
            'pp_to_grams' => ['nullable', 'numeric', 'min:0'],
            'feed_pct' => ['required', 'numeric', 'gt:0', 'max:100'],
        ];
    }
}
