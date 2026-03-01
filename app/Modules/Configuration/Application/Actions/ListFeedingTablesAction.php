<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListFeedingTablesAction extends BaseAction
{
    public function execute(): Collection
    {
        return FeedingGrowthTable::query()
            ->with('rows')
            ->orderBy('name')
            ->get();
    }
}
