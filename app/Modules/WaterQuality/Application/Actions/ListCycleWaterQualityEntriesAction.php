<?php

namespace App\Modules\WaterQuality\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListCycleWaterQualityEntriesAction extends BaseAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(Cycle $cycle, array $filters): LengthAwarePaginator
    {
        return $cycle->waterQualityEntries()
            ->when(isset($filters['date_from']), fn ($query) => $query->where('measured_at', '>=', $filters['date_from'].' 00:00:00'))
            ->when(isset($filters['date_to']), fn ($query) => $query->where('measured_at', '<=', $filters['date_to'].' 23:59:59'))
            ->orderByDesc('measured_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}

