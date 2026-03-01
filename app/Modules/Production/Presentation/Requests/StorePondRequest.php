<?php

namespace App\Modules\Production\Presentation\Requests;

use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePondRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ponds', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'area_ha' => ['required', 'numeric', 'gt:0'],
            'avg_depth_m' => ['nullable', 'numeric', 'gt:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
