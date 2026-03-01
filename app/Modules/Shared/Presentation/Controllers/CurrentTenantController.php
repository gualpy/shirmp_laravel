<?php

namespace App\Modules\Shared\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Application\Actions\GetCurrentTenantAction;
use Illuminate\Http\JsonResponse;

class CurrentTenantController extends Controller
{
    public function __invoke(GetCurrentTenantAction $action): JsonResponse
    {
        return response()->json([
            'tenant' => $action(),
        ]);
    }
}
