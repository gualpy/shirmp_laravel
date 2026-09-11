<?php

namespace App\Modules\Suppliers\Application\Actions;

use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\Suppliers\Domain\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

final class ListSuppliersAction extends BaseAction
{
    public function execute(?string $type = null): Collection
    {
        return Supplier::query()
            ->when($type !== null, fn ($q) => $q->where('type', $type))
            ->orderBy('name')
            ->get();
    }
}
