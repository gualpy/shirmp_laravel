<?php

namespace App\Modules\WaterQuality\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\WaterQuality\Application\Actions\CreateWaterQualityEntryAction;
use App\Modules\WaterQuality\Application\DTO\WaterQualityEntryDTO;
use App\Modules\WaterQuality\Presentation\Requests\StoreQuickWaterQualityEntryRequest;
use App\Modules\WaterQuality\Presentation\Resources\WaterQualityEntryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class QuickWaterQualityController extends Controller
{
    public function __invoke(
        StoreQuickWaterQualityEntryRequest $request,
        CreateWaterQualityEntryAction $action,
    ): JsonResponse {
        $payload = $request->normalized();
        $pond = Pond::query()->findOrFail($payload['pond_id']);

        $cycle = $pond->cycles()
            ->where('status', CycleStatus::ACTIVE->value)
            ->latest('started_at')
            ->first();

        if ($cycle === null) {
            throw ValidationException::withMessages([
                'pond' => [__('messages.pond.no_active_cycle_water')],
            ]);
        }

        $entry = $action->execute($cycle, WaterQualityEntryDTO::fromArray(array_merge($payload, [
            'measured_by_user_id' => $request->user()?->id,
        ])));

        return (new WaterQualityEntryResource($entry))->response()->setStatusCode(201);
    }
}
