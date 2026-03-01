<?php

namespace App\Modules\Feeding\Presentation\Requests;

use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreFeedTypeRequest extends FormRequest
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
                Rule::unique('feed_types', 'name')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'brand' => ['nullable', 'string', 'max:255'],
            'protein_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
