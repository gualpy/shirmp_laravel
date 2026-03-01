<?php

namespace App\Modules\Feeding\Application\Actions;

use App\Modules\Feeding\Application\DTO\FeedTypeDataDTO;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Shared\Application\Actions\BaseAction;

final class UpdateFeedTypeAction extends BaseAction
{
    public function execute(FeedType $feedType, FeedTypeDataDTO $dto): FeedType
    {
        $feedType->update($dto->toArray());

        return $feedType->refresh();
    }
}
