<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\CycleDataDTO;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\Shared\Application\Actions\BaseAction;
use App\Multitenancy\TenantContext;

final class CreateCycleAction extends BaseAction
{
    public function __construct(
        private readonly ProductionDomainService $domainService,
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
    ) {
    }

    public function execute(Pond $pond, CycleDataDTO $dto): Cycle
    {
        if ($dto->status === CycleStatus::ACTIVE) {
            $tenant = $this->tenantContext->currentTenant();
            if ($tenant !== null) {
                $this->saasService->enforceLimitOrFail($tenant, 'max_cycles_active');
            }

            $this->domainService->assertNoOtherActiveCycle($pond);
        }

        return $pond->cycles()->create($dto->toArray());
    }
}
