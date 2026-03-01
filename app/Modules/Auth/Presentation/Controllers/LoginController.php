<?php

namespace App\Modules\Auth\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Auth\Application\Actions\LoginAction;
use App\Modules\Auth\Application\DTO\LoginDTO;
use App\Modules\Auth\Presentation\Requests\LoginRequest;
use App\Modules\Auth\Presentation\Resources\AuthTokenResource;
use App\Multitenancy\TenantContext;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    public function __invoke(
        LoginRequest $request,
        LoginAction $action,
        TenantContext $tenantContext,
    ): JsonResponse {
        $tenant = $tenantContext->currentTenant();

        if (! $tenant instanceof Tenant) {
            abort(400, 'Tenant context is not available.');
        }

        $result = $action->execute(LoginDTO::fromArray($request->validated()), $tenant);

        if ($result === null) {
            return response()->json([
                'message' => 'Invalid credentials for current tenant.',
            ], 401);
        }

        return (new AuthTokenResource($result))->response();
    }
}
