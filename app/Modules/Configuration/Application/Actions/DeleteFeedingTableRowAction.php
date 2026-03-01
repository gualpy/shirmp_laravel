<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTableRow;
use App\Modules\Shared\Application\Actions\BaseAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeleteFeedingTableRowAction extends BaseAction
{
    public function execute(FeedingGrowthTable $table, FeedingGrowthTableRow $row): void
    {
        if ($row->table_id !== $table->id) {
            throw new NotFoundHttpException('Feeding table row not found for this table.');
        }

        $row->delete();
    }
}
