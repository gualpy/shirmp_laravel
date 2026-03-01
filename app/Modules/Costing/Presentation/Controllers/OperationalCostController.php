<?php

namespace App\Modules\Costing\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Costing\Application\Actions\CreateOperationalCostAction;
use App\Modules\Costing\Application\Actions\ListOperationalCostsAction;
use App\Modules\Costing\Application\DTO\OperationalCostEntryDTO;
use App\Modules\Costing\Presentation\Requests\StoreOperationalCostRequest;
use App\Modules\Costing\Presentation\Resources\OperationalCostEntryResource;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OperationalCostController extends Controller
{
    public function index(Cycle $cycle, ListOperationalCostsAction $action): AnonymousResourceCollection
    {
        return OperationalCostEntryResource::collection($action->execute($cycle));
    }

    public function store(
        Cycle $cycle,
        StoreOperationalCostRequest $request,
        CreateOperationalCostAction $action,
    ): JsonResponse {
        $entry = $action->execute($cycle, OperationalCostEntryDTO::fromArray($request->validated()));

        return (new OperationalCostEntryResource($entry))->response()->setStatusCode(201);
    }
}
