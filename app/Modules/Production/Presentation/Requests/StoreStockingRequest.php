<?php

namespace App\Modules\Production\Presentation\Requests;

use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStockingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'stocked_at' => ['required', 'date'],
            'pl_qty' => ['required', 'integer', 'min:1'],
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'batch_code' => ['nullable', 'string', 'max:255'],
            'initial_pp_grams' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}
