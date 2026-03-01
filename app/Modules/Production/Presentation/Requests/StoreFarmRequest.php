<?php

namespace App\Modules\Production\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
