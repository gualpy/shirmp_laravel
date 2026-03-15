<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Multitenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BackofficeAlertsService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_unless($this->saasService->checkFeature($tenant, 'alerts'), 403, 'Alerts no disponible en el plan actual.');

        $state = isset($filters['state']) && $filters['state'] !== '' ? (string) $filters['state'] : null;
        $farmId = isset($filters['farm']) && $filters['farm'] !== '' ? (int) $filters['farm'] : null;
        $pondId = isset($filters['pond']) && $filters['pond'] !== '' ? (int) $filters['pond'] : null;
        $cycleId = isset($filters['cycle']) && $filters['cycle'] !== '' ? (int) $filters['cycle'] : null;
        $severity = isset($filters['severity']) && $filters['severity'] !== '' ? (string) $filters['severity'] : null;
        $dateFrom = isset($filters['date_from']) && $filters['date_from'] !== '' ? (string) $filters['date_from'] : null;
        $dateTo = isset($filters['date_to']) && $filters['date_to'] !== '' ? (string) $filters['date_to'] : null;

        $rows = $this->filteredQuery($filters)
            ->orderByDesc('detected_at')
            ->paginate(20)
            ->withQueryString();

        return [
            'filters' => [
                'farm' => $farmId,
                'pond' => $pondId,
                'cycle' => $cycleId,
                'severity' => $severity,
                'state' => $state,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'options' => [
                'farms' => Farm::query()->orderBy('name')->get(['id', 'name'])->map(fn (Farm $farm): array => [
                    'id' => $farm->id,
                    'name' => $farm->name,
                ])->values()->all(),
                'ponds' => Pond::query()
                    ->when($farmId !== null, fn ($query) => $query->where('farm_id', $farmId))
                    ->orderBy('code')
                    ->get(['id', 'code'])
                    ->map(fn (Pond $pond): array => ['id' => $pond->id, 'code' => $pond->code])
                    ->values()
                    ->all(),
                'cycles' => Cycle::query()
                    ->when($pondId !== null, fn ($query) => $query->where('pond_id', $pondId))
                    ->orderByDesc('started_at')
                    ->get(['id', 'pond_id', 'started_at'])
                    ->map(fn (Cycle $cycle): array => [
                        'id' => $cycle->id,
                        'label' => 'Ciclo #'.$cycle->id.' · '.$cycle->started_at?->format('Y-m-d'),
                    ])
                    ->values()
                    ->all(),
                'severities' => ['critical', 'warning', 'info'],
                'states' => ['open', 'acknowledged', 'resolved'],
            ],
            'rows' => $this->mapRows($rows),
            'pagination' => $rows,
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $filters): array
    {
        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_unless($this->saasService->checkFeature($tenant, 'alerts'), 403, 'Alerts no disponible en el plan actual.');

        return $this->filteredQuery($filters)
            ->orderByDesc('detected_at')
            ->get()
            ->map(fn (AlertEvent $alert): array => $this->mapAlert($alert))
            ->values()
            ->all();
    }

    /**
     * @param LengthAwarePaginator<int, AlertEvent> $paginator
     * @return array<int, array<string, mixed>>
     */
    private function mapRows(LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(fn (AlertEvent $alert): array => $this->mapAlert($alert))->values()->all();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        $state = isset($filters['state']) && $filters['state'] !== '' ? (string) $filters['state'] : null;
        $farmId = isset($filters['farm']) && $filters['farm'] !== '' ? (int) $filters['farm'] : null;
        $pondId = isset($filters['pond']) && $filters['pond'] !== '' ? (int) $filters['pond'] : null;
        $cycleId = isset($filters['cycle']) && $filters['cycle'] !== '' ? (int) $filters['cycle'] : null;
        $severity = isset($filters['severity']) && $filters['severity'] !== '' ? (string) $filters['severity'] : null;
        $dateFrom = isset($filters['date_from']) && $filters['date_from'] !== '' ? (string) $filters['date_from'] : null;
        $dateTo = isset($filters['date_to']) && $filters['date_to'] !== '' ? (string) $filters['date_to'] : null;

        return AlertEvent::query()
            ->with(['farm:id,name', 'cycle.pond:id,code'])
            ->when($farmId !== null, fn ($query) => $query->where('farm_id', $farmId))
            ->when($pondId !== null, fn ($query) => $query->whereHas('cycle', fn ($cycleQuery) => $cycleQuery->where('pond_id', $pondId)))
            ->when($cycleId !== null, fn ($query) => $query->where('cycle_id', $cycleId))
            ->when($severity !== null, fn ($query) => $query->where('severity', $severity))
            ->when($state !== null, function ($query) use ($state) {
                return match ($state) {
                    'open' => $query->where('is_acknowledged', false)->whereNull('resolved_at'),
                    'acknowledged' => $query->where('is_acknowledged', true)->whereNull('resolved_at'),
                    'resolved' => $query->whereNotNull('resolved_at'),
                    default => $query,
                };
            })
            ->when($dateFrom !== null, fn ($query) => $query->whereDate('detected_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($query) => $query->whereDate('detected_at', '<=', $dateTo))
            ->orderByRaw("
                CASE severity
                    WHEN 'critical' THEN 1
                    WHEN 'warning' THEN 2
                    ELSE 3
                END ASC
            ")
            ->orderByRaw("
                CASE
                    WHEN resolved_at IS NULL AND is_acknowledged = 0 THEN 1
                    WHEN resolved_at IS NULL AND is_acknowledged = 1 THEN 2
                    ELSE 3
                END ASC
            ");
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAlert(AlertEvent $alert): array
    {
        $state = $alert->resolved_at !== null ? 'resolved' : ($alert->is_acknowledged ? 'acknowledged' : 'open');

        return [
            'id' => $alert->id,
            'date' => $alert->detected_at?->format('Y-m-d H:i'),
            'farm' => (string) ($alert->farm?->name ?? 'N/A'),
            'pond' => (string) ($alert->cycle?->pond?->code ?? 'N/A'),
            'cycle' => (string) $alert->cycle_id,
            'type' => (string) $alert->rule_code,
            'title' => (string) $alert->title,
            'severity' => is_string($alert->severity) ? $alert->severity : $alert->severity->value,
            'message' => (string) $alert->message,
            'state' => $state,
            'can_acknowledge' => ! $alert->is_acknowledged && $alert->resolved_at === null,
            'can_resolve' => $alert->resolved_at === null,
            'cycle_href' => '/backoffice/cycles/'.$alert->cycle_id,
        ];
    }
}
