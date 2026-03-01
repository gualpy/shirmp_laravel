<?php

namespace App\Modules\Feeding\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Feeding\Application\Actions\CreateFeedTypeAction;
use App\Modules\Feeding\Application\Actions\ListFeedTypesAction;
use App\Modules\Feeding\Application\Actions\UpdateFeedTypeAction;
use App\Modules\Feeding\Application\DTO\FeedTypeDataDTO;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Feeding\Presentation\Requests\StoreFeedTypeRequest;
use App\Modules\Feeding\Presentation\Requests\UpdateFeedTypeRequest;
use App\Modules\Feeding\Presentation\Resources\FeedTypeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FeedTypeController extends Controller
{
    public function index(ListFeedTypesAction $action): AnonymousResourceCollection
    {
        return FeedTypeResource::collection($action->execute());
    }

    public function store(StoreFeedTypeRequest $request, CreateFeedTypeAction $action): JsonResponse
    {
        $feedType = $action->execute(FeedTypeDataDTO::fromArray($request->validated()));

        return (new FeedTypeResource($feedType))->response()->setStatusCode(201);
    }

    public function update(UpdateFeedTypeRequest $request, FeedType $feedType, UpdateFeedTypeAction $action): FeedTypeResource
    {
        $payload = array_merge($feedType->only(['name', 'brand', 'protein_pct', 'notes', 'is_active']), $request->validated());

        return new FeedTypeResource($action->execute($feedType, FeedTypeDataDTO::fromArray($payload)));
    }
}
