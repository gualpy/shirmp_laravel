<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\StockingDataDTO;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

final class CreateStockingAction extends BaseAction
{
    public function __construct(private readonly ProductionDomainService $domainService)
    {
    }

    public function execute(Cycle $cycle, StockingDataDTO $dto): Stocking
    {
        $this->domainService->assertCycleActive($cycle);

        if ($cycle->stocking !== null) {
            throw ValidationException::withMessages([
                'cycle_id' => [__('messages.stocking.already_exists')],
            ]);
        }

        $this->domainService->assertDateOnOrAfter(
            field: 'stocked_at',
            value: $dto->stockedAt,
            baseline: $cycle->started_at->format('Y-m-d'),
            message: __('messages.stocking.stocked_at_after_start'),
        );

        $density = $this->domainService->calculateStockingDensity($cycle->pond, $dto->plQty);

        return $cycle->stocking()->create(array_merge($dto->toArray(), $density));
    }
}
