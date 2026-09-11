<?php

namespace App\Modules\Production\Presentation\Requests;

use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'stocked_at' => ['sometimes', 'required', 'date'],
            'pl_qty' => ['sometimes', 'required', 'integer', 'min:1'],
            'supplier_id' => ['sometimes', 'nullable', 'integer', Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'batch_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'initial_pp_grams' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
        ];
    }
}
