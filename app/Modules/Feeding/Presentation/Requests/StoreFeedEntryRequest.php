<?php

namespace App\Modules\Feeding\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFeedEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fed_at' => ['required', 'date'],
            'feed_type_id' => ['required', 'integer', 'exists:feed_types,id'],
            'amount_kg' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
