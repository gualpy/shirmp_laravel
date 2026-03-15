<?php

namespace App\Modules\Billing\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MarkBillingInvoicePaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
