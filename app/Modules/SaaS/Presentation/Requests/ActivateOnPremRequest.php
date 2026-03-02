<?php

namespace App\Modules\SaaS\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ActivateOnPremRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:255'],
            'offline_grace_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'machine_fingerprint' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

