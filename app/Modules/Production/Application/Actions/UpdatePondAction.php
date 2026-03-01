<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\PondDataDTO;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

final class UpdatePondAction extends BaseAction
{
    public function execute(Pond $pond, PondDataDTO $dto): Pond
    {
        $farm = Farm::query()->find($dto->farmId);

        if ($farm === null) {
            throw ValidationException::withMessages([
                'farm_id' => ['Farm not found for current tenant.'],
            ]);
        }

        $pond->update($dto->toArray());

        return $pond->refresh();
    }
}
