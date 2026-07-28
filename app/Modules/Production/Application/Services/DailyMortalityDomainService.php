<?php

namespace App\Modules\Production\Application\Services;

use App\Modules\Production\Application\DTO\DailyMortalityDTO;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\DailyMortality;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class DailyMortalityDomainService
{
    public function assertCycleAllowsRecords(Cycle $cycle): void
    {
        if ($cycle->status !== CycleStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'cycle' => [__('messages.mortality.active_cycle_only')],
            ]);
        }
    }

    public function assertRecordedAtWithinCycle(Cycle $cycle, string $recordedAt): void
    {
        $recorded = Carbon::parse($recordedAt);
        $start = $cycle->started_at?->copy()->startOfDay();

        if ($start !== null && $recorded->lt($start)) {
            throw ValidationException::withMessages([
                'recorded_at' => [__('messages.mortality.recorded_at_after_start')],
            ]);
        }

        if ($cycle->ended_at !== null && $recorded->gt($cycle->ended_at->copy()->endOfDay())) {
            throw ValidationException::withMessages([
                'recorded_at' => [__('messages.mortality.recorded_at_before_end')],
            ]);
        }
    }

    public function assertPondBelongsToCycle(Cycle $cycle, int $pondId): void
    {
        if ($cycle->pond_id !== $pondId) {
            throw ValidationException::withMessages([
                'pond_id' => [__('messages.pond.not_belongs_to_cycle')],
            ]);
        }
    }

    public function create(Cycle $cycle, DailyMortalityDTO $dto): DailyMortality
    {
        $this->assertCycleAllowsRecords($cycle);
        $this->assertPondBelongsToCycle($cycle, $dto->pondId);
        $this->assertRecordedAtWithinCycle($cycle, $dto->recordedAt);

        return $cycle->dailyMortalities()->create([
            'pond_id' => $dto->pondId,
            'recorded_at' => $dto->recordedAt,
            'mortality_count' => $dto->mortalityCount,
            'notes' => $dto->notes,
            'created_by' => $dto->createdBy,
        ]);
    }
}
