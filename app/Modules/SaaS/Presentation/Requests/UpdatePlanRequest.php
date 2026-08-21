<?php

namespace App\Modules\SaaS\Presentation\Requests;

use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'billing_type' => ['sometimes', Rule::enum(PlanBillingType::class)],
            'price_usd' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
