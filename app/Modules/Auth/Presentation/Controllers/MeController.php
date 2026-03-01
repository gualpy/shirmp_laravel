<?php

namespace App\Modules\Auth\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\GetMeAction;
use App\Modules\Auth\Presentation\Resources\MeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    public function __invoke(Request $request, GetMeAction $action): JsonResponse
    {
        $user = $request->user();

        return (new MeResource($action->execute($user)))->response();
    }
}
