<?php

namespace App\Modules\Shared\Application\Actions;

use App\Models\TenantNote;

class ListTenantNotesAction
{
    public function __invoke(): array
    {
        return TenantNote::query()
            ->orderBy('id')
            ->get(['id', 'tenant_id', 'note'])
            ->toArray();
    }
}
