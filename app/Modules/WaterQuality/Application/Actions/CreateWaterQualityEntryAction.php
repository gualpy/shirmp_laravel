<?php

namespace App\Modules\WaterQuality\Application\Actions;

use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\WaterQuality\Application\DTO\WaterQualityEntryDTO;
use App\Modules\WaterQuality\Application\Services\WaterQualityDomainService;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;

final class CreateWaterQualityEntryAction extends BaseAction
{
    public function __construct(
        private readonly WaterQualityDomainService $domainService,
        private readonly AuditLogService $auditLogService,
    )
    {
    }

    public function execute(Cycle $cycle, WaterQualityEntryDTO $dto): WaterQualityEntry
    {
        $entry = $this->domainService->createEntry($cycle, $dto);

        $this->auditLogService->record(
            actionKey: 'water_quality.created',
            entityType: 'WaterQualityEntry',
            entityId: $entry->id,
            context: [
                'cycle_id' => $cycle->id,
                'pond_id' => $cycle->pond_id,
                'measured_at' => $dto->measuredAt,
            ],
        );

        return $entry;
    }
}
