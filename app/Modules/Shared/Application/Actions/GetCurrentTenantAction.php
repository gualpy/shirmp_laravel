<?php

namespace App\Modules\Shared\Application\Actions;

use App\Multitenancy\TenantContext;

class GetCurrentTenantAction
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {
    }

    public function __invoke(): array
    {
        $tenant = $this->tenantContext->currentTenant();

        return [
            'id' => $tenant?->id,
            'slug' => $tenant?->slug,
            'name' => $tenant?->name,
        ];
    }
}
