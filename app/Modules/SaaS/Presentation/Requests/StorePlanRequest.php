<?php

namespace App\Modules\SaaS\Presentation\Requests;

use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'unique:plans,code'],
            'name' => ['required', 'string', 'max:255'],
            'billing_type' => ['required', Rule::enum(PlanBillingType::class)],
            'price_usd' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

