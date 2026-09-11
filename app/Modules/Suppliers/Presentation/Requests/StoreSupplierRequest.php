<?php

namespace App\Modules\Suppliers\Presentation\Requests;

use App\Modules\Suppliers\Domain\Enums\SupplierType;
use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'name')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'type' => ['required', Rule::in(array_map(fn (SupplierType $t) => $t->value, SupplierType::cases()))],
            'code' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
