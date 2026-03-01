<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListHarvestsAction extends BaseAction
{
    public function execute(Cycle $cycle): Collection
    {
        return $cycle->harvests()->orderByDesc('harvested_at')->get();
    }
}
