<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateSamplingAction;
use App\Modules\Production\Application\Actions\ListSamplingsAction;
use App\Modules\Production\Application\DTO\SamplingDataDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Presentation\Requests\ListSamplingRequest;
use App\Modules\Production\Presentation\Requests\StoreSamplingRequest;
use App\Modules\Production\Presentation\Resources\SamplingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SamplingController extends Controller
{
    public function index(Cycle $cycle, ListSamplingRequest $request, ListSamplingsAction $action): AnonymousResourceCollection
    {
        return SamplingResource::collection($action->execute($cycle, $request->validated()));
    }

    public function store(StoreSamplingRequest $request, Cycle $cycle, CreateSamplingAction $action): JsonResponse
    {
        $sampling = $action->execute($cycle, SamplingDataDTO::fromArray($request->validated()));

        return (new SamplingResource($sampling))->response()->setStatusCode(201);
    }
}
