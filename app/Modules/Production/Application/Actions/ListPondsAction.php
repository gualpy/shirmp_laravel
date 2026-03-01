<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListPondsAction extends BaseAction
{
    public function execute(?int $farmId = null): Collection
    {
        return Pond::query()
            ->when($farmId !== null, fn ($query) => $query->where('farm_id', $farmId))
            ->orderBy('code')
            ->get();
    }
}
