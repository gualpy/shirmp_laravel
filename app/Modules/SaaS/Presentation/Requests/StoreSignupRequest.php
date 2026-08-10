<?php

namespace App\Modules\SaaS\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSignupRequest extends FormRequest
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
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id')
                    ->where('is_active', true)
                    ->whereNotIn('billing_type', ['onprem'])
                    ->whereNotNull('price_usd'),
            ],
            'payment_provider' => ['required', Rule::in(['paypal'])],
        ];
    }
}
