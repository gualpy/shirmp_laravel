<?php

namespace App\Modules\SaaS\Presentation\Requests;

use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Enums\VerificationSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignPlanToTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'status' => ['nullable', Rule::enum(SubscriptionStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'license_key' => ['nullable', 'string', 'max:255'],
            'last_verified_at' => ['nullable', 'date'],
            'offline_grace_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'offline_mode_enabled' => ['nullable', 'boolean'],
            'verification_source' => ['nullable', Rule::enum(VerificationSource::class)],
        ];
    }
}
