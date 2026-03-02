<?php

namespace App\Http\Middleware;

use App\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWriteAllowed
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $readOnly = (bool) $request->attributes->get('read_only_mode', false) || $this->tenantContext->isReadOnlyMode();
        $isWrite = ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);

        if ($readOnly && $isWrite) {
            return new JsonResponse([
                'message' => 'Tenant is in read-only mode during offline grace period.',
            ], 403, ['X-Read-Only-Mode' => '1']);
        }

        return $next($request);
    }
}

