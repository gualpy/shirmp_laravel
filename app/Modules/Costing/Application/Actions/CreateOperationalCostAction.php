<?php

namespace App\Modules\Costing\Application\Actions;

use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Costing\Application\DTO\OperationalCostEntryDTO;
use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;

final class CreateOperationalCostAction extends BaseAction
{
    public function __construct(
        private readonly CostingService $costingService,
        private readonly AuditLogService $auditLogService,
    )
    {
    }

    public function execute(Cycle $cycle, OperationalCostEntryDTO $dto): OperationalCostEntry
    {
        $entry = $this->costingService->createOperationalCost($cycle, $dto);

        $this->auditLogService->record(
            actionKey: 'cost.created',
            entityType: 'OperationalCostEntry',
            entityId: $entry->id,
            context: [
                'cycle_id' => $cycle->id,
                'cost_type' => $dto->costType,
                'amount' => $dto->amount,
                'occurred_at' => $dto->occurredAt,
            ],
        );

        return $entry;
    }
}
