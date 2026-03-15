<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

final class HealthzController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
