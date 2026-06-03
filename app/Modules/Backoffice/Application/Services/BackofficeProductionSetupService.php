<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\User;
use App\Modules\Production\Application\Actions\CreateCycleAction;
use App\Modules\Production\Application\Actions\CreateFarmAction;
use App\Modules\Production\Application\Actions\CreatePondAction;
use App\Modules\Production\Application\Actions\CreateStockingAction;
use App\Modules\Production\Application\Actions\ListFarmsAction;
use App\Modules\Production\Application\Actions\ListPondsAction;
use App\Modules\Production\Application\DTO\CycleDataDTO;
use App\Modules\Production\Application\DTO\FarmDataDTO;
use App\Modules\Production\Application\DTO\PondDataDTO;
use App\Modules\Production\Application\DTO\StockingDataDTO;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Support\Facades\DB;

final class BackofficeProductionSetupService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly LicenseService $licenseService,
        private readonly ListFarmsAction $listFarmsAction,
        private readonly ListPondsAction $listPondsAction,
        private readonly CreateFarmAction $createFarmAction,
        private readonly CreatePondAction $createPondAction,
        private readonly CreateCycleAction $createCycleAction,
        private readonly CreateStockingAction $createStockingAction,
    ) {
    }

    /** @return array<string,mixed> */
    public function farmsView(): array
    {
        $tenant = $this->requireTenant();
        $farms = $this->listFarmsAction->execute()->loadCount('ponds');

        return [
            'rows' => $farms->map(fn (Farm $farm): array => [
                'id' => $farm->id,
                'name' => $farm->name,
                'location' => $farm->location,
                'notes' => $farm->notes,
                'ponds_count' => $farm->ponds_count,
            ])->values()->all(),
            'stats' => [
                'farms' => $farms->count(),
                'ponds' => (int) $farms->sum('ponds_count'),
            ],
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    /** @return array<string,mixed> */
    public function pondsView(?int $farmId = null): array
    {
        $tenant = $this->requireTenant();
        $farms = $this->listFarmsAction->execute();
        $ponds = $this->listPondsAction->execute($farmId)->load(['farm', 'cycles']);

        return [
            'rows' => $ponds->map(function (Pond $pond): array {
                $activeCycle = $pond->cycles->first(fn (Cycle $cycle) => $cycle->status === CycleStatus::ACTIVE);

                return [
                    'id' => $pond->id,
                    'code' => $pond->code,
                    'name' => $pond->name,
                    'farm' => $pond->farm?->name,
                    'area_ha' => number_format((float) $pond->area_ha, 2),
                    'avg_depth_m' => $pond->avg_depth_m !== null ? number_format((float) $pond->avg_depth_m, 2) : null,
                    'is_active' => (bool) $pond->is_active,
                    'has_active_cycle' => $activeCycle !== null,
                    'active_cycle_id' => $activeCycle?->id,
                ];
            })->values()->all(),
            'farm_options' => $farms->map(fn (Farm $farm): array => [
                'id' => $farm->id,
                'name' => $farm->name,
            ])->values()->all(),
            'selected_farm_id' => $farmId,
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    /** @return array<string,mixed> */
    public function stockingCreateView(): array
    {
        $tenant = $this->requireTenant();
        $farms = $this->listFarmsAction->execute()->load('ponds.cycles');

        $pondOptions = $farms
            ->flatMap(function (Farm $farm) {
                return $farm->ponds->map(function (Pond $pond) use ($farm): array {
                    $hasActiveCycle = $pond->cycles->contains(fn (Cycle $cycle) => $cycle->status === CycleStatus::ACTIVE);

                    return [
                        'id' => $pond->id,
                        'label' => $farm->name.' · '.$pond->code.' · '.($pond->name ?: 'Piscina'),
                        'farm_name' => $farm->name,
                        'pond_code' => $pond->code,
                        'pond_name' => $pond->name,
                        'area_ha' => number_format((float) $pond->area_ha, 2),
                        'is_active' => (bool) $pond->is_active,
                        'has_active_cycle' => $hasActiveCycle,
                        'disabled' => ! $pond->is_active || $hasActiveCycle,
                    ];
                });
            })
            ->values();

        return [
            'pond_options' => $pondOptions->all(),
            'available_ponds' => $pondOptions->where('disabled', false)->count(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    public function createFarm(array $payload): Farm
    {
        return $this->createFarmAction->execute(FarmDataDTO::fromArray($payload));
    }

    public function createPond(array $payload): Pond
    {
        return $this->createPondAction->execute(PondDataDTO::fromArray($payload));
    }

    public function createStockingFlow(array $payload, ?User $user = null): Cycle
    {
        /** @var Pond $pond */
        $pond = Pond::query()->findOrFail((int) $payload['pond_id']);

        return DB::transaction(function () use ($pond, $payload) {
            $cycle = $this->createCycleAction->execute($pond, CycleDataDTO::fromArray([
                'started_at' => $payload['started_at'],
                'status' => CycleStatus::ACTIVE->value,
                'notes' => $payload['cycle_notes'] ?? null,
            ]));

            $this->createStockingAction->execute($cycle, StockingDataDTO::fromArray([
                'stocked_at' => $payload['stocked_at'],
                'pl_qty' => $payload['pl_qty'],
                'hatchery_code' => $payload['hatchery_code'] ?? null,
                'batch_code' => $payload['batch_code'] ?? null,
                'initial_pp_grams' => $payload['initial_pp_grams'] ?? null,
            ]));

            return $cycle->fresh(['pond.farm', 'stocking']);
        });
    }

    private function requireTenant()
    {
        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');

        return $tenant;
    }
}
