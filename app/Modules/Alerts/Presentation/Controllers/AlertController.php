<?php

namespace App\Modules\Alerts\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Alerts\Application\Actions\AcknowledgeAlertAction;
use App\Modules\Alerts\Application\Actions\ListAlertsAction;
use App\Modules\Alerts\Application\Actions\ResolveAlertAction;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Alerts\Presentation\Requests\ListAlertsRequest;
use App\Modules\Alerts\Presentation\Resources\AlertEventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AlertController extends Controller
{
    public function index(ListAlertsRequest $request, ListAlertsAction $action): AnonymousResourceCollection
    {
        return AlertEventResource::collection($action->execute($request->validated()));
    }

    public function acknowledge(
        AlertEvent $alert,
        Request $request,
        AcknowledgeAlertAction $action,
    ): JsonResponse {
        $updated = $action->execute($alert, $request->user());

        return (new AlertEventResource($updated))->response();
    }

    public function resolve(
        AlertEvent $alert,
        Request $request,
        ResolveAlertAction $action,
    ): JsonResponse {
        $updated = $action->execute($alert, $request->user());

        return (new AlertEventResource($updated))->response();
    }
}
