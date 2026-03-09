<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Database\Eloquent\Collection;

final class ListCycleMortalitiesAction
{
    public function execute(Cycle $cycle): Collection
    {
        return $cycle->dailyMortalities()
            ->with('pond:id,code')
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->get();
    }
}
