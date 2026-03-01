<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\GetCycleMetricsAction;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Presentation\Requests\CycleMetricsRequest;
use App\Modules\Production\Presentation\Resources\CycleMetricsResource;
use Illuminate\Http\JsonResponse;

final class CycleMetricsController extends Controller
{
    public function __invoke(
        CycleMetricsRequest $request,
        Cycle $cycle,
        GetCycleMetricsAction $action,
    ): JsonResponse {
        $metrics = $action->execute(
            cycle: $cycle,
            survivalEstimate: isset($request->validated()['survival_estimate'])
                ? (float) $request->validated()['survival_estimate']
                : null,
        );

        return (new CycleMetricsResource($metrics))->response();
    }
}
