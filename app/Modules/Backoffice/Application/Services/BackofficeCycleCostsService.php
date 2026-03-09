<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Costing\Application\Services\CostingService;
use App\Modules\Costing\Domain\Enums\OperationalCostType;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Multitenancy\TenantContext;

final class BackofficeCycleCostsService
{
    public function __construct(
        private readonly CostingService $costingService,
        private readonly TenantContext $tenantContext,
        private readonly SaaSService $saasService,
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm']);

        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_unless($this->saasService->checkFeature($tenant, 'cost_engine'), 403, 'Cost Engine no disponible en el plan actual.');

        $summary = $this->costingService->summarizeCycleCosts($cycle);
        $rows = $cycle->operationalCosts()
            ->orderByDesc('occurred_at')
            ->get()
            ->map(fn (OperationalCostEntry $entry): array => [
                'id' => $entry->id,
                'occurred_at' => $entry->occurred_at?->format('Y-m-d'),
                'cost_type' => is_string($entry->cost_type) ? $entry->cost_type : $entry->cost_type->value,
                'amount' => (float) $entry->amount,
                'notes' => $entry->notes,
            ])
            ->values()
            ->all();

        return [
            'header' => [
                'cycle_id' => $cycle->id,
                'farm_name' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'started_at' => $cycle->started_at?->format('Y-m-d'),
            ],
            'summary' => $summary,
            'rows' => $rows,
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'cost_type_options' => array_map(
                static fn (OperationalCostType $type): array => ['value' => $type->value, 'label' => ucfirst($type->value)],
                OperationalCostType::cases()
            ),
        ];
    }
}
