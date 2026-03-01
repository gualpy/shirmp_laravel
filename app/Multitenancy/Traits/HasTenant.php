<?php

namespace App\Multitenancy\Traits;

use App\Multitenancy\Scopes\TenantScope;
use App\Multitenancy\TenantContext;
use RuntimeException;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model): void {
            if (! empty($model->tenant_id)) {
                return;
            }

            $context = app(TenantContext::class);

            if ($context->isBypassed()) {
                return;
            }

            $tenant = $context->currentTenant();

            if ($tenant !== null) {
                $model->tenant_id = $tenant->getKey();

                return;
            }

            throw new RuntimeException(
                'Cannot create tenant-scoped model without TenantContext. Use TenantScopeBypass in CLI/seeders when required.'
            );
        });
    }
}
