<?php

namespace App\Multitenancy\Scopes;

use App\Multitenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->isBypassed()) {
            return;
        }

        $tenant = $context->currentTenant();

        if ($tenant !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenant->getKey());

            return;
        }

        $builder->whereRaw('1 = 0');
    }
}
