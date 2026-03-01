<?php

namespace App\Modules\Feeding\Application\Actions;

use App\Modules\Feeding\Application\DTO\FeedTypeDataDTO;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateFeedTypeAction extends BaseAction
{
    public function execute(FeedTypeDataDTO $dto): FeedType
    {
        return FeedType::query()->create($dto->toArray());
    }
}
