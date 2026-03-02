<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\FarmDataDTO;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\Shared\Application\Actions\BaseAction;
use App\Multitenancy\TenantContext;

final class CreateFarmAction extends BaseAction
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
    ) {
    }

    public function execute(FarmDataDTO $dto): Farm
    {
        $tenant = $this->tenantContext->currentTenant();
        if ($tenant !== null) {
            $this->saasService->enforceLimitOrFail($tenant, 'max_farms');
        }

        return Farm::query()->create($dto->toArray());
    }
}
