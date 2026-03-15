<?php

namespace App\Modules\Backoffice\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTenantOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:tenants,slug'],
            'company_display_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:60'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
            'subscription_status' => ['required', Rule::in(['active', 'trial'])],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'create_initial_farm' => ['nullable', 'boolean'],
            'farm_name' => ['nullable', 'string', 'max:255', 'required_if:create_initial_farm,1'],
            'create_ponds' => ['nullable', 'boolean'],
            'pond_count' => ['nullable', 'integer', 'min:0', 'max:50', 'required_if:create_ponds,1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'create_initial_farm' => filter_var($this->input('create_initial_farm', false), FILTER_VALIDATE_BOOL),
            'create_ponds' => filter_var($this->input('create_ponds', false), FILTER_VALIDATE_BOOL),
        ]);
    }
}
