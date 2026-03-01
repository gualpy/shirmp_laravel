<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateCycleAction;
use App\Modules\Production\Application\Actions\ListPondCyclesAction;
use App\Modules\Production\Application\Actions\UpdateCycleAction;
use App\Modules\Production\Application\DTO\CycleDataDTO;
use App\Modules\Production\Application\DTO\CycleUpdateDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Presentation\Requests\StoreCycleRequest;
use App\Modules\Production\Presentation\Requests\UpdateCycleRequest;
use App\Modules\Production\Presentation\Resources\CycleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CycleController extends Controller
{
    public function indexByPond(Pond $pond, ListPondCyclesAction $action): AnonymousResourceCollection
    {
        return CycleResource::collection($action->execute($pond));
    }

    public function store(StoreCycleRequest $request, Pond $pond, CreateCycleAction $action): JsonResponse
    {
        $cycle = $action->execute($pond, CycleDataDTO::fromArray($request->validated()));

        return (new CycleResource($cycle))->response()->setStatusCode(201);
    }

    public function show(Cycle $cycle): CycleResource
    {
        return new CycleResource($cycle);
    }

    public function update(UpdateCycleRequest $request, Cycle $cycle, UpdateCycleAction $action): CycleResource
    {
        return new CycleResource($action->execute($cycle, CycleUpdateDTO::fromArray($request->validated())));
    }
}
