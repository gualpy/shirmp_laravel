<?php

namespace App\Modules\Production\Application\Services;

use App\Modules\Shared\Application\Services\BaseService;

final class HealthService extends BaseService
{
    /**
     * @return array{status: string}
     */
    public function check(): array
    {
        return ['status' => 'ok'];
    }
}
