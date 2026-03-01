<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Modules\Configuration\Application\DTO\FeedingTableRowDTO;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTableRow;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateFeedingTableRowAction extends BaseAction
{
    public function execute(FeedingGrowthTable $table, FeedingTableRowDTO $dto): FeedingGrowthTableRow
    {
        return $table->rows()->create($dto->toArray());
    }
}
