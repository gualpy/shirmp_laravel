<?php

namespace App\Modules\WaterQuality\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\WaterQuality\Application\Actions\ListPondWaterQualityEntriesAction;
use App\Modules\WaterQuality\Presentation\Requests\ListWaterQualityEntriesRequest;
use App\Modules\WaterQuality\Presentation\Resources\WaterQualityEntryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PondWaterQualityController extends Controller
{
    public function index(
        Pond $pond,
        ListWaterQualityEntriesRequest $request,
        ListPondWaterQualityEntriesAction $action,
    ): AnonymousResourceCollection {
        return WaterQualityEntryResource::collection($action->execute($pond, $request->validated()));
    }
}

