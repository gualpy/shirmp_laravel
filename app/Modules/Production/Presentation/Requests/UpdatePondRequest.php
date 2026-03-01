<?php

namespace App\Modules\Production\Presentation\Requests;

use App\Modules\Production\Domain\Models\Pond;
use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePondRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Pond $pond */
        $pond = $this->route('pond');
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'farm_id' => ['sometimes', 'required', 'integer', 'exists:farms,id'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('ponds', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($pond->id),
            ],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'area_ha' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'avg_depth_m' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
