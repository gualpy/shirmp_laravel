<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateHarvestAction;
use App\Modules\Production\Application\Actions\ListHarvestsAction;
use App\Modules\Production\Application\DTO\HarvestDataDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Harvest;
use App\Modules\Production\Presentation\Requests\StoreHarvestRequest;
use App\Modules\Production\Presentation\Resources\HarvestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class HarvestController extends Controller
{
    public function index(Cycle $cycle, ListHarvestsAction $action): AnonymousResourceCollection
    {
        return HarvestResource::collection($action->execute($cycle));
    }

    public function show(Harvest $harvest): HarvestResource
    {
        return new HarvestResource($harvest);
    }

    public function store(StoreHarvestRequest $request, Cycle $cycle, CreateHarvestAction $action): JsonResponse
    {
        $harvest = $action->execute($cycle, HarvestDataDTO::fromArray($request->validated()));

        return (new HarvestResource($harvest))->response()->setStatusCode(201);
    }
}
