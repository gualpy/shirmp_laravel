<?php

namespace App\Modules\Billing\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreManualBillingPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_usd' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],
            'provider' => ['nullable', 'in:manual,stripe,onprem,other'],
            'provider_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
