<?php

namespace App\Modules\Configuration\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuration\Application\Actions\CreateFeedingTableAction;
use App\Modules\Configuration\Application\Actions\ListFeedingTablesAction;
use App\Modules\Configuration\Application\Actions\UpdateFeedingTableAction;
use App\Modules\Configuration\Application\DTO\FeedingTableDTO;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Configuration\Presentation\Requests\StoreFeedingTableRequest;
use App\Modules\Configuration\Presentation\Requests\UpdateFeedingTableRequest;
use App\Modules\Configuration\Presentation\Resources\FeedingGrowthTableResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FeedingGrowthTableController extends Controller
{
    public function index(ListFeedingTablesAction $action): AnonymousResourceCollection
    {
        return FeedingGrowthTableResource::collection($action->execute());
    }

    public function store(StoreFeedingTableRequest $request, CreateFeedingTableAction $action): JsonResponse
    {
        $table = $action->execute(FeedingTableDTO::fromArray($request->validated()));

        return (new FeedingGrowthTableResource($table->load('rows')))->response()->setStatusCode(201);
    }

    public function update(
        UpdateFeedingTableRequest $request,
        FeedingGrowthTable $table,
        UpdateFeedingTableAction $action,
    ): FeedingGrowthTableResource {
        $payload = array_merge(
            $table->only(['farm_id', 'name', 'is_active']),
            $request->validated(),
        );

        return new FeedingGrowthTableResource($action->execute($table, FeedingTableDTO::fromArray($payload)));
    }
}
