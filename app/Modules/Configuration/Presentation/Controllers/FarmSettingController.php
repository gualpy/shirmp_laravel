<?php

namespace App\Modules\Configuration\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuration\Application\Actions\GetFarmSettingsAction;
use App\Modules\Configuration\Application\Actions\UpdateFarmSettingsAction;
use App\Modules\Configuration\Application\DTO\SettingPatchDTO;
use App\Modules\Configuration\Presentation\Requests\UpdateFarmSettingRequest;
use App\Modules\Configuration\Presentation\Resources\ResolvedSettingResource;
use App\Modules\Production\Domain\Models\Farm;
use Illuminate\Http\JsonResponse;

final class FarmSettingController extends Controller
{
    public function show(Farm $farm, GetFarmSettingsAction $action): ResolvedSettingResource
    {
        return new ResolvedSettingResource($action->execute($farm));
    }

    public function update(
        UpdateFarmSettingRequest $request,
        Farm $farm,
        UpdateFarmSettingsAction $action,
    ): JsonResponse {
        $resolved = $action->execute($farm, SettingPatchDTO::fromArray($request->validated()));

        return (new ResolvedSettingResource($resolved))->response();
    }
}
