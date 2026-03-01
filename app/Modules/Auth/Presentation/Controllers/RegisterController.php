<?php

namespace App\Modules\Auth\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Auth\Application\Actions\RegisterUserAction;
use App\Modules\Auth\Application\DTO\RegisterUserDTO;
use App\Modules\Auth\Presentation\Requests\RegisterRequest;
use App\Modules\Auth\Presentation\Resources\AuthTokenResource;
use App\Multitenancy\TenantContext;
use Illuminate\Http\JsonResponse;

final class RegisterController extends Controller
{
    public function __invoke(
        RegisterRequest $request,
        RegisterUserAction $action,
        TenantContext $tenantContext,
    ): JsonResponse {
        $tenant = $tenantContext->currentTenant();

        if (! $tenant instanceof Tenant) {
            abort(400, 'Tenant context is not available.');
        }

        $result = $action->execute(RegisterUserDTO::fromArray($request->validated()), $tenant);

        return (new AuthTokenResource($result))->response()->setStatusCode(201);
    }
}
