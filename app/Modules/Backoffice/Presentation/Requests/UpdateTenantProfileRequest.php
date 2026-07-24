<?php

namespace App\Modules\Backoffice\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTenantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_display_name' => ['required', 'string', 'max:255'],
            'company_legal_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'report_footer_text' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
