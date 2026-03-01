<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\FarmDataDTO;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateFarmAction extends BaseAction
{
    public function execute(FarmDataDTO $dto): Farm
    {
        return Farm::query()->create($dto->toArray());
    }
}
