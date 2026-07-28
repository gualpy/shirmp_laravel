<?php

namespace App\Modules\WaterQuality\Application\Services;

use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Services\BaseService;
use App\Modules\WaterQuality\Application\DTO\WaterQualityEntryDTO;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class WaterQualityDomainService extends BaseService
{
    public function assertCycleAllowsMeasurements(Cycle $cycle): void
    {
        if ($cycle->status === CycleStatus::CANCELLED) {
            throw ValidationException::withMessages([
                'cycle' => [__('messages.water_quality.cancelled_cycle')],
            ]);
        }
    }

    public function assertMeasuredAtWithinCycle(Cycle $cycle, string $measuredAt): void
    {
        $measured = Carbon::parse($measuredAt);
        $start = $cycle->started_at->copy()->startOfDay();

        if ($measured->lt($start)) {
            throw ValidationException::withMessages([
                'measured_at' => [__('messages.water_quality.measured_at_after_start')],
            ]);
        }

        if ($cycle->ended_at !== null && $measured->gt($cycle->ended_at->copy()->endOfDay())) {
            throw ValidationException::withMessages([
                'measured_at' => [__('messages.water_quality.measured_at_before_end')],
            ]);
        }
    }

    public function createEntry(Cycle $cycle, WaterQualityEntryDTO $dto): WaterQualityEntry
    {
        $this->assertCycleAllowsMeasurements($cycle);
        $this->assertMeasuredAtWithinCycle($cycle, $dto->measuredAt);

        return $cycle->waterQualityEntries()->create(array_merge(
            $dto->toArray(),
            ['pond_id' => $cycle->pond_id]
        ));
    }
}

