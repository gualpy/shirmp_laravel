<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\GetCycleProjectionAction;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Presentation\Resources\CycleProjectionResource;
use Illuminate\Http\JsonResponse;

final class CycleProjectionController extends Controller
{
    public function __invoke(Cycle $cycle, GetCycleProjectionAction $action): JsonResponse
    {
        return (new CycleProjectionResource($action->execute($cycle)))->response();
    }
}
