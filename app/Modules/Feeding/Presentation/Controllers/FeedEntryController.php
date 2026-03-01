<?php

namespace App\Modules\Feeding\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Feeding\Application\Actions\CreateFeedEntryAction;
use App\Modules\Feeding\Application\Actions\ListFeedEntriesAction;
use App\Modules\Feeding\Application\DTO\FeedEntryDataDTO;
use App\Modules\Feeding\Presentation\Requests\ListFeedEntriesRequest;
use App\Modules\Feeding\Presentation\Requests\StoreFeedEntryRequest;
use App\Modules\Feeding\Presentation\Resources\FeedEntryResource;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FeedEntryController extends Controller
{
    public function index(Cycle $cycle, ListFeedEntriesRequest $request, ListFeedEntriesAction $action): AnonymousResourceCollection
    {
        return FeedEntryResource::collection($action->execute($cycle, $request->validated()));
    }

    public function store(Cycle $cycle, StoreFeedEntryRequest $request, CreateFeedEntryAction $action): JsonResponse
    {
        $entry = $action->execute($cycle, FeedEntryDataDTO::fromArray($request->validated()));

        return (new FeedEntryResource($entry->load('feedType')))->response()->setStatusCode(201);
    }
}
