<?php

namespace App\Modules\Configuration\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Configuration\Application\Actions\GetTenantSettingsAction;
use App\Modules\Configuration\Application\Actions\UpdateTenantSettingsAction;
use App\Modules\Configuration\Application\DTO\SettingPatchDTO;
use App\Modules\Configuration\Presentation\Requests\UpdateTenantSettingRequest;
use App\Modules\Configuration\Presentation\Resources\ResolvedSettingResource;
use App\Multitenancy\TenantContext;
use Illuminate\Http\JsonResponse;

final class TenantSettingController extends Controller
{
    public function show(TenantContext $tenantContext, GetTenantSettingsAction $action): ResolvedSettingResource
    {
        $tenant = $tenantContext->currentTenant();
        abort_unless($tenant instanceof Tenant, 400, 'Tenant context is not available.');

        return new ResolvedSettingResource($action->execute($tenant));
    }

    public function update(
        UpdateTenantSettingRequest $request,
        TenantContext $tenantContext,
        UpdateTenantSettingsAction $action,
    ): JsonResponse {
        $tenant = $tenantContext->currentTenant();
        abort_unless($tenant instanceof Tenant, 400, 'Tenant context is not available.');

        $resolved = $action->execute($tenant, SettingPatchDTO::fromArray($request->validated()));

        return (new ResolvedSettingResource($resolved))->response();
    }
}
