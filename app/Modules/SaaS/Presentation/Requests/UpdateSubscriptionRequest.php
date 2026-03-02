<?php

namespace App\Modules\SaaS\Presentation\Requests;

use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Enums\VerificationSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['sometimes', 'integer', 'exists:plans,id'],
            'status' => ['sometimes', Rule::enum(SubscriptionStatus::class)],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date'],
            'license_key' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_verified_at' => ['sometimes', 'nullable', 'date'],
            'offline_grace_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'offline_mode_enabled' => ['sometimes', 'boolean'],
            'verification_source' => ['sometimes', Rule::enum(VerificationSource::class)],
        ];
    }
}
