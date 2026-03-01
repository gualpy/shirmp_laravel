<?php

namespace App\Modules\Alerts\Application\Actions;

use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListAlertsAction extends BaseAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        return AlertEvent::query()
            ->when(isset($filters['farm_id']), fn ($query) => $query->where('farm_id', (int) $filters['farm_id']))
            ->when(isset($filters['cycle_id']), fn ($query) => $query->where('cycle_id', (int) $filters['cycle_id']))
            ->when(isset($filters['rule_code']), fn ($query) => $query->where('rule_code', (string) $filters['rule_code']))
            ->when(isset($filters['severity']), fn ($query) => $query->where('severity', (string) $filters['severity']))
            ->when(isset($filters['is_acknowledged']), fn ($query) => $query->where('is_acknowledged', (bool) $filters['is_acknowledged']))
            ->when(isset($filters['date_from']), fn ($query) => $query->whereDate('detected_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn ($query) => $query->whereDate('detected_at', '<=', $filters['date_to']))
            ->orderByDesc('detected_at')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }
}
