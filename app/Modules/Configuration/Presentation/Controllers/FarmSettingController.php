<?php

namespace App\Modules\Configuration\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Application\Services\AuditLogService;
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
        AuditLogService $auditLogService,
    ): JsonResponse {
        $resolved = $action->execute($farm, SettingPatchDTO::fromArray($request->validated()));

        $auditLogService->record(
            actionKey: 'settings.updated',
            entityType: 'FarmSetting',
            entityId: $farm->id,
            context: ['scope' => 'farm', 'keys' => array_keys($request->validated())],
            tenant: $farm->tenant,
            user: $request->user(),
        );

        return (new ResolvedSettingResource($resolved))->response();
    }
}
