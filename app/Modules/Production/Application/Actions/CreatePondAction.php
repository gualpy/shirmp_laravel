<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\PondDataDTO;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

final class CreatePondAction extends BaseAction
{
    public function execute(PondDataDTO $dto): Pond
    {
        $farm = Farm::query()->find($dto->farmId);

        if ($farm === null) {
            throw ValidationException::withMessages([
                'farm_id' => [__('messages.farm.not_found')],
            ]);
        }

        return Pond::query()->create($dto->toArray());
    }
}
