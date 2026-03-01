<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreatePondAction;
use App\Modules\Production\Application\Actions\DeletePondAction;
use App\Modules\Production\Application\Actions\ListPondsAction;
use App\Modules\Production\Application\Actions\UpdatePondAction;
use App\Modules\Production\Application\DTO\PondDataDTO;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Presentation\Requests\StorePondRequest;
use App\Modules\Production\Presentation\Requests\UpdatePondRequest;
use App\Modules\Production\Presentation\Resources\PondResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class PondController extends Controller
{
    public function index(Request $request, ListPondsAction $action): AnonymousResourceCollection
    {
        return PondResource::collection($action->execute($request->integer('farm_id') ?: null));
    }

    public function store(StorePondRequest $request, CreatePondAction $action): JsonResponse
    {
        $pond = $action->execute(PondDataDTO::fromArray($request->validated()));

        return (new PondResource($pond))->response()->setStatusCode(201);
    }

    public function show(Pond $pond): PondResource
    {
        return new PondResource($pond);
    }

    public function update(UpdatePondRequest $request, Pond $pond, UpdatePondAction $action): PondResource
    {
        $payload = array_merge($pond->only(['farm_id', 'code', 'name', 'area_ha', 'avg_depth_m', 'is_active']), $request->validated());

        return new PondResource($action->execute($pond, PondDataDTO::fromArray($payload)));
    }

    public function destroy(Pond $pond, DeletePondAction $action): Response
    {
        $action->execute($pond);

        return response()->noContent();
    }
}
