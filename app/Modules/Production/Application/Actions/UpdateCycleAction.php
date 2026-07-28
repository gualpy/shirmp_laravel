<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Application\DTO\CycleUpdateDTO;
use App\Modules\Production\Application\Services\ProductionDomainService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Validation\ValidationException;

final class UpdateCycleAction extends BaseAction
{
    public function __construct(private readonly ProductionDomainService $domainService)
    {
    }

    public function execute(Cycle $cycle, CycleUpdateDTO $dto): Cycle
    {
        $data = [];

        if ($dto->status !== null) {
            if ($cycle->status === CycleStatus::HARVESTED && $dto->status !== CycleStatus::HARVESTED) {
                throw ValidationException::withMessages([
                    'status' => [__('messages.cycle.harvested_locked')],
                ]);
            }

            if ($dto->status === CycleStatus::ACTIVE) {
                $this->domainService->assertNoOtherActiveCycle($cycle->pond, $cycle->id);
            }

            $data['status'] = $dto->status->value;
        }

        if ($dto->endedAt !== null) {
            $data['ended_at'] = $dto->endedAt;
        }

        if ($dto->notes !== null) {
            $data['notes'] = $dto->notes;
        }

        if ($data !== []) {
            $cycle->update($data);
        }

        return $cycle->refresh();
    }
}
