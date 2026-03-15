<?php

namespace App\Http\Controllers;

use App\Support\ReadinessService;
use Illuminate\Http\JsonResponse;

final class ReadyzController extends Controller
{
    public function __invoke(ReadinessService $readinessService): JsonResponse
    {
        $result = $readinessService->check();
        $statusCode = $result['status'] === 'ok' ? 200 : 500;

        return response()->json($result, $statusCode);
    }
}
