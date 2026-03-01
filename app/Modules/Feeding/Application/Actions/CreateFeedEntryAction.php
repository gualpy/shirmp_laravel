<?php

namespace App\Modules\Feeding\Application\Actions;

use App\Modules\Feeding\Application\DTO\FeedEntryDataDTO;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

final class CreateFeedEntryAction extends BaseAction
{
    public function __construct(
        private readonly ProductionDomainService $productionDomainService,
    ) {
    }

    public function execute(Cycle $cycle, FeedEntryDataDTO $dto): FeedEntry
    {
        $this->productionDomainService->assertCycleActive($cycle);

        $stocking = $this->productionDomainService->assertStockingExists($cycle);
        $this->productionDomainService->assertDateOnOrAfter(
            field: 'fed_at',
            value: $dto->fedAt,
            baseline: $stocking->stocked_at->format('Y-m-d'),
            message: 'fed_at must be on or after stocking stocked_at.',
        );

        $feedType = FeedType::query()->find($dto->feedTypeId);

        if ($feedType === null) {
            throw ValidationException::withMessages([
                'feed_type_id' => ['Feed type not found for current tenant.'],
            ]);
        }

        return $cycle->feedEntries()->create($dto->toArray());
    }
}
