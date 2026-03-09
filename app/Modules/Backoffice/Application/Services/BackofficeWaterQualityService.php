<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use App\Multitenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BackofficeWaterQualityService
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
        abort_unless($this->saasService->checkFeature($tenant, 'water_quality'), 403, 'Water Quality no disponible en el plan actual.');

        $farmId = isset($filters['farm']) && $filters['farm'] !== '' ? (int) $filters['farm'] : null;
        $pondId = isset($filters['pond']) && $filters['pond'] !== '' ? (int) $filters['pond'] : null;
        $cycleId = isset($filters['cycle']) && $filters['cycle'] !== '' ? (int) $filters['cycle'] : null;
        $dateFrom = isset($filters['date_from']) && $filters['date_from'] !== '' ? (string) $filters['date_from'] : null;
        $dateTo = isset($filters['date_to']) && $filters['date_to'] !== '' ? (string) $filters['date_to'] : null;

        $entries = WaterQualityEntry::query()
            ->with(['pond.farm:id,name', 'cycle:id,started_at'])
            ->when($farmId !== null, fn ($query) => $query->whereHas('pond', fn ($pondQuery) => $pondQuery->where('farm_id', $farmId)))
            ->when($pondId !== null, fn ($query) => $query->where('pond_id', $pondId))
            ->when($cycleId !== null, fn ($query) => $query->where('cycle_id', $cycleId))
            ->when($dateFrom !== null, fn ($query) => $query->whereDate('measured_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($query) => $query->whereDate('measured_at', '<=', $dateTo))
            ->orderByDesc('measured_at')
            ->paginate(20)
            ->withQueryString();

        $selectedCycle = $cycleId !== null ? Cycle::query()->with('pond')->find($cycleId) : null;

        return [
            'filters' => [
                'farm' => $farmId,
                'pond' => $pondId,
                'cycle' => $cycleId,
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
                    ->values()->all(),
                'cycles' => Cycle::query()
                    ->when($pondId !== null, fn ($query) => $query->where('pond_id', $pondId))
                    ->orderByDesc('started_at')
                    ->get(['id', 'pond_id', 'started_at'])
                    ->map(fn (Cycle $cycle): array => [
                        'id' => $cycle->id,
                        'label' => 'Ciclo #'.$cycle->id.' · '.$cycle->started_at?->format('Y-m-d'),
                    ])
                    ->values()->all(),
                'form_ponds' => Pond::query()
                    ->whereHas('cycles', fn ($query) => $query->where('status', CycleStatus::ACTIVE->value))
                    ->with('farm:id,name')
                    ->orderBy('code')
                    ->get(['id', 'farm_id', 'code'])
                    ->map(fn (Pond $pond): array => [
                        'id' => $pond->id,
                        'label' => $pond->code.' · '.($pond->farm?->name ?? 'N/A'),
                    ])
                    ->values()->all(),
            ],
            'rows' => $this->mapRows($entries),
            'pagination' => $entries,
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'defaults' => [
                'pond' => $selectedCycle?->pond_id ?? $pondId,
                'measured_at' => now()->format('Y-m-d\TH:i'),
            ],
        ];
    }

    /**
     * @param LengthAwarePaginator<int, WaterQualityEntry> $paginator
     * @return array<int, array<string, mixed>>
     */
    private function mapRows(LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(function (WaterQualityEntry $entry): array {
            $doValue = $entry->dissolved_oxygen_mg_l !== null ? (float) $entry->dissolved_oxygen_mg_l : null;
            $phValue = $entry->ph !== null ? (float) $entry->ph : null;

            return [
                'id' => $entry->id,
                'date' => $entry->measured_at?->format('Y-m-d H:i'),
                'farm' => (string) ($entry->pond?->farm?->name ?? 'N/A'),
                'pond' => (string) ($entry->pond?->code ?? 'N/A'),
                'cycle' => (string) $entry->cycle_id,
                'dissolved_oxygen_mg_l' => $doValue,
                'ph' => $phValue,
                'temp_c' => $entry->temp_c !== null ? (float) $entry->temp_c : null,
                'salinity_ppt' => $entry->salinity_ppt !== null ? (float) $entry->salinity_ppt : null,
                'ammonia_mg_l' => $entry->ammonia_mg_l !== null ? (float) $entry->ammonia_mg_l : null,
                'nitrite_mg_l' => $entry->nitrite_mg_l !== null ? (float) $entry->nitrite_mg_l : null,
                'do_low' => $doValue !== null && $doValue < 3.5,
                'ph_out' => $phValue !== null && ($phValue < 7.2 || $phValue > 8.8),
                'cycle_href' => '/backoffice/cycles/'.$entry->cycle_id,
            ];
        })->values()->all();
    }
}
