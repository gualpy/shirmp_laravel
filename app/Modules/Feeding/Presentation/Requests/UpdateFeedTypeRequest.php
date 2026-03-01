<?php

namespace App\Modules\Feeding\Presentation\Requests;

use App\Modules\Feeding\Domain\Models\FeedType;
use App\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFeedTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var FeedType $feedType */
        $feedType = $this->route('feedType');
        $tenantId = app(TenantContext::class)->currentTenant()?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('feed_types', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($feedType->id),
            ],
            'brand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'protein_pct' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
