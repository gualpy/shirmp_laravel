<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\SamplingDataDTO;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateSamplingAction extends BaseAction
{
    public function __construct(private readonly ProductionDomainService $domainService)
    {
    }

    public function execute(Cycle $cycle, SamplingDataDTO $dto): Sampling
    {
        $this->domainService->assertCycleActive($cycle);
        $stocking = $this->domainService->assertStockingExists($cycle);

        $this->domainService->assertDateOnOrAfter(
            field: 'sampled_at',
            value: $dto->sampledAt,
            baseline: $stocking->stocked_at->format('Y-m-d'),
            message: __('messages.sampling.sampled_at_after_stocking'),
        );

        return $cycle->samplings()->create($dto->toArray());
    }
}
