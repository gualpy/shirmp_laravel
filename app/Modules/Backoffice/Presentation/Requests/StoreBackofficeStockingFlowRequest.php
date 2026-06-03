<?php

namespace App\Modules\Backoffice\Presentation\Requests;

use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBackofficeStockingFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'pond_id' => ['required', 'integer', Rule::exists('ponds', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId))],
            'started_at' => ['required', 'date'],
            'cycle_notes' => ['nullable', 'string'],
            'stocked_at' => ['required', 'date'],
            'pl_qty' => ['required', 'integer', 'min:1'],
            'hatchery_code' => ['nullable', 'string', 'max:255'],
            'batch_code' => ['nullable', 'string', 'max:255'],
            'initial_pp_grams' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}
