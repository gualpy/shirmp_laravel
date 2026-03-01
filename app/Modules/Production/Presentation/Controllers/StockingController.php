<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateStockingAction;
use App\Modules\Production\Application\Actions\UpdateStockingAction;
use App\Modules\Production\Application\DTO\StockingDataDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Presentation\Requests\StoreStockingRequest;
use App\Modules\Production\Presentation\Requests\UpdateStockingRequest;
use App\Modules\Production\Presentation\Resources\StockingResource;
use Illuminate\Http\JsonResponse;

final class StockingController extends Controller
{
    public function show(Cycle $cycle): StockingResource
    {
        abort_if($cycle->stocking === null, 404, 'Stocking not found.');

        return new StockingResource($cycle->stocking);
    }

    public function store(StoreStockingRequest $request, Cycle $cycle, CreateStockingAction $action): JsonResponse
    {
        $stocking = $action->execute($cycle, StockingDataDTO::fromArray($request->validated()));

        return (new StockingResource($stocking))->response()->setStatusCode(201);
    }

    public function update(UpdateStockingRequest $request, Cycle $cycle, UpdateStockingAction $action): StockingResource
    {
        abort_if($cycle->stocking === null, 404, 'Stocking not found.');

        $stocking = $cycle->stocking;

        $payload = array_merge($stocking->only(['stocked_at', 'pl_qty', 'hatchery_code', 'batch_code', 'initial_pp_grams']), $request->validated());

        return new StockingResource($action->execute($cycle, $stocking, StockingDataDTO::fromArray($payload)));
    }
}
