<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\HealthCheckDTO;
use App\Modules\Production\Application\Services\HealthService;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetHealthAction extends BaseAction
{
    public function __construct(
        private readonly HealthService $healthService,
    ) {
    }

    /**
     * @return array{status: string}
     */
    public function execute(HealthCheckDTO $dto): array
    {
        return $this->healthService->check();
    }
}
