<?php

namespace App\Modules\Backoffice\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tenant' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
