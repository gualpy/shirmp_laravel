<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\CreateDailyMortalityAction;
use App\Modules\Production\Application\Actions\ListCycleMortalitiesAction;
use App\Modules\Production\Application\DTO\DailyMortalityDTO;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Presentation\Requests\StoreDailyMortalityRequest;
use App\Modules\Production\Presentation\Resources\DailyMortalityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DailyMortalityController extends Controller
{
    public function index(Cycle $cycle, ListCycleMortalitiesAction $action): AnonymousResourceCollection
    {
        return DailyMortalityResource::collection($action->execute($cycle));
    }

    public function store(
        StoreDailyMortalityRequest $request,
        CreateDailyMortalityAction $action,
    ): JsonResponse {
        $pond = Pond::query()->findOrFail((int) $request->validated()['pond_id']);

        $entry = $action->execute($pond, DailyMortalityDTO::fromArray(array_merge(
            $request->validated(),
            ['created_by' => $request->user()?->id],
        )));

        return (new DailyMortalityResource($entry))->response()->setStatusCode(201);
    }
}
