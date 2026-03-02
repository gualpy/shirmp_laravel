<?php

namespace App\Http\Middleware;

use App\Modules\Auth\Domain\Enums\UserRole;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->role !== UserRole::SUPER_ADMIN) {
            return new JsonResponse(['message' => 'Super admin role is required.'], 403);
        }

        return $next($request);
    }
}

