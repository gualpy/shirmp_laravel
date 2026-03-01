<?php

namespace App\Modules\Production\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Application\Actions\GetHealthAction;
use App\Modules\Production\Application\DTO\HealthCheckDTO;
use App\Modules\Production\Presentation\Requests\HealthCheckRequest;
use App\Modules\Production\Presentation\Resources\HealthResource;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(HealthCheckRequest $request, GetHealthAction $action): JsonResponse
    {
        $dto = HealthCheckDTO::fromArray($request->validated());
        $result = $action->execute($dto);

        return (new HealthResource($result))->response();
    }
}
