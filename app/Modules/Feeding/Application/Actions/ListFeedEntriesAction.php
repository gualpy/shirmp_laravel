<?php

namespace App\Modules\Feeding\Application\Actions;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListFeedEntriesAction extends BaseAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(Cycle $cycle, array $filters): LengthAwarePaginator
    {
        return $cycle->feedEntries()
            ->with('feedType')
            ->when(isset($filters['date_from']), fn ($query) => $query->whereDate('fed_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn ($query) => $query->whereDate('fed_at', '<=', $filters['date_to']))
            ->orderByDesc('fed_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
