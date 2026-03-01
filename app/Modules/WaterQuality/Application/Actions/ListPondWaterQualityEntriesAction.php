<?php

namespace App\Modules\WaterQuality\Application\Actions;

use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPondWaterQualityEntriesAction extends BaseAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(Pond $pond, array $filters): LengthAwarePaginator
    {
        return WaterQualityEntry::query()
            ->where('pond_id', $pond->id)
            ->when(isset($filters['date_from']), fn ($query) => $query->where('measured_at', '>=', $filters['date_from'].' 00:00:00'))
            ->when(isset($filters['date_to']), fn ($query) => $query->where('measured_at', '<=', $filters['date_to'].' 23:59:59'))
            ->orderByDesc('measured_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}

