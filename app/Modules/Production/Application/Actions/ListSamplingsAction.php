<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListSamplingsAction extends BaseAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(Cycle $cycle, array $filters): LengthAwarePaginator
    {
        return $cycle->samplings()
            ->when(isset($filters['date_from']), fn ($query) => $query->whereDate('sampled_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn ($query) => $query->whereDate('sampled_at', '<=', $filters['date_to']))
            ->orderByDesc('sampled_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
