<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateFarmAction;
use App\Modules\Production\Application\Actions\DeleteFarmAction;
use App\Modules\Production\Application\Actions\ListFarmsAction;
use App\Modules\Production\Application\Actions\UpdateFarmAction;
use App\Modules\Production\Application\DTO\FarmDataDTO;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Presentation\Requests\StoreFarmRequest;
use App\Modules\Production\Presentation\Requests\UpdateFarmRequest;
use App\Modules\Production\Presentation\Resources\FarmResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class FarmController extends Controller
{
    public function index(ListFarmsAction $action): AnonymousResourceCollection
    {
        return FarmResource::collection($action->execute());
    }

    public function store(StoreFarmRequest $request, CreateFarmAction $action): JsonResponse
    {
        $farm = $action->execute(FarmDataDTO::fromArray($request->validated()));

        return (new FarmResource($farm))->response()->setStatusCode(201);
    }

    public function show(Farm $farm): FarmResource
    {
        return new FarmResource($farm);
    }

    public function update(UpdateFarmRequest $request, Farm $farm, UpdateFarmAction $action): FarmResource
    {
        $payload = array_merge($farm->only(['name', 'location', 'notes']), $request->validated());

        return new FarmResource($action->execute($farm, FarmDataDTO::fromArray($payload)));
    }

    public function destroy(Farm $farm, DeleteFarmAction $action): Response
    {
        $action->execute($farm);

        return response()->noContent();
    }
}
