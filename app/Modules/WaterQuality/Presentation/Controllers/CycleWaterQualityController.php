<?php

namespace App\Modules\WaterQuality\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\WaterQuality\Application\Actions\CreateWaterQualityEntryAction;
use App\Modules\WaterQuality\Application\Actions\GetLatestCycleWaterQualityEntryAction;
use App\Modules\WaterQuality\Application\Actions\ListCycleWaterQualityEntriesAction;
use App\Modules\WaterQuality\Application\DTO\WaterQualityEntryDTO;
use App\Modules\WaterQuality\Presentation\Requests\ListWaterQualityEntriesRequest;
use App\Modules\WaterQuality\Presentation\Requests\StoreWaterQualityEntryRequest;
use App\Modules\WaterQuality\Presentation\Resources\WaterQualityEntryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CycleWaterQualityController extends Controller
{
    public function index(
        Cycle $cycle,
        ListWaterQualityEntriesRequest $request,
        ListCycleWaterQualityEntriesAction $action,
    ): AnonymousResourceCollection {
        return WaterQualityEntryResource::collection($action->execute($cycle, $request->validated()));
    }

    public function store(
        Cycle $cycle,
        StoreWaterQualityEntryRequest $request,
        CreateWaterQualityEntryAction $action,
    ): JsonResponse {
        $payload = array_merge($request->validated(), [
            'measured_by_user_id' => $request->user()?->id,
        ]);

        $entry = $action->execute($cycle, WaterQualityEntryDTO::fromArray($payload));

        return (new WaterQualityEntryResource($entry))->response()->setStatusCode(201);
    }

    public function latest(Cycle $cycle, GetLatestCycleWaterQualityEntryAction $action): WaterQualityEntryResource|JsonResponse
    {
        $entry = $action->execute($cycle);

        if ($entry === null) {
            return response()->json(['message' => 'No water quality entries found for this cycle.'], 404);
        }

        return new WaterQualityEntryResource($entry);
    }
}

