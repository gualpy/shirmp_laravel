<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Application\Services\RolePermissionService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleModuleAccess
{
    public function __construct(private readonly RolePermissionService $permissionService)
    {
    }

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $isWrite = ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
        $user = $request->user();
        $allowed = $isWrite
            ? $this->permissionService->canManage($user, $module)
            : $this->permissionService->canView($user, $module);

        if (! $allowed) {
            $message = 'No autorizado para acceder a este módulo.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
