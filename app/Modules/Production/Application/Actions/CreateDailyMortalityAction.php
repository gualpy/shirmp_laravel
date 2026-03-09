<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Alerts\Application\Services\AlertEngineService;
use App\Modules\Production\Application\DTO\DailyMortalityDTO;
use App\Modules\Production\Application\Services\DailyMortalityDomainService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\DailyMortality;
use App\Modules\Production\Domain\Models\Pond;
use Carbon\Carbon;

final class CreateDailyMortalityAction
{
    public function __construct(
        private readonly DailyMortalityDomainService $domainService,
        private readonly AlertEngineService $alertEngineService,
    ) {
    }

    public function execute(Pond $pond, DailyMortalityDTO $dto): DailyMortality
    {
        $cycle = $pond->cycles()
            ->where('status', \App\Modules\Production\Domain\Enums\CycleStatus::ACTIVE->value)
            ->latest('started_at')
            ->firstOrFail();

        $entry = $this->domainService->create($cycle, $dto);

        $this->alertEngineService->emitHighDailyMortalityAlert(
            cycle: $cycle->fresh(['stocking']),
            date: Carbon::parse($dto->recordedAt),
            mortalityCount: $pond->dailyMortalities()
                ->where('cycle_id', $cycle->id)
                ->whereDate('recorded_at', $dto->recordedAt)
                ->sum('mortality_count'),
        );

        return $entry;
    }
}
