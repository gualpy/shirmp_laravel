<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Modules\Configuration\Application\DTO\FeedingTableDTO;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

final class UpdateFeedingTableAction extends BaseAction
{
    public function execute(FeedingGrowthTable $table, FeedingTableDTO $dto): FeedingGrowthTable
    {
        if ($dto->farmId !== null && Farm::query()->find($dto->farmId) === null) {
            throw ValidationException::withMessages([
                'farm_id' => [__('messages.farm.not_found')],
            ]);
        }

        $table->update($dto->toArray());

        return $table->refresh()->load('rows');
    }
}
