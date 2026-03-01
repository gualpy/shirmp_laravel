<?php

namespace App\Modules\Costing\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListOperationalCostsAction extends BaseAction
{
    public function execute(Cycle $cycle): Collection
    {
        return $cycle->operationalCosts()->orderByDesc('occurred_at')->get();
    }
}
