<?php

namespace App\Modules\Configuration\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuration\Application\Actions\CreateFeedingTableRowAction;
use App\Modules\Configuration\Application\Actions\DeleteFeedingTableRowAction;
use App\Modules\Configuration\Application\DTO\FeedingTableRowDTO;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTableRow;
use App\Modules\Configuration\Presentation\Requests\StoreFeedingTableRowRequest;
use App\Modules\Configuration\Presentation\Resources\FeedingGrowthTableRowResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class FeedingGrowthTableRowController extends Controller
{
    public function store(
        StoreFeedingTableRowRequest $request,
        FeedingGrowthTable $table,
        CreateFeedingTableRowAction $action,
    ): JsonResponse {
        $row = $action->execute($table, FeedingTableRowDTO::fromArray($request->validated()));

        return (new FeedingGrowthTableRowResource($row))->response()->setStatusCode(201);
    }

    public function destroy(
        FeedingGrowthTable $table,
        FeedingGrowthTableRow $row,
        DeleteFeedingTableRowAction $action,
    ): Response {
        $action->execute($table, $row);

        return response()->noContent();
    }
}
