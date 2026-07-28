<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Inventory\Domain\Enums\InventoryCategory;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Domain\Enums\InventoryReferenceType;
use App\Modules\Inventory\Domain\Enums\InventoryUnit;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;

final class BackofficeInventoryService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly LicenseService $licenseService,
    ) {
    }

    /** @return array<string, mixed> */
    public function warehousesView(): array
    {
        $tenant = $this->requireTenant();
        $warehouses = Warehouse::query()->withCount('items')->orderBy('name')->get();

        return [
            'rows' => $warehouses->map(fn (Warehouse $warehouse): array => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'location' => $warehouse->location,
                'notes' => $warehouse->notes,
                'items_count' => $warehouse->items_count,
            ])->values()->all(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    /** @return array<string, mixed> */
    public function inventoryIndexView(?int $warehouseId = null): array
    {
        $tenant = $this->requireTenant();
        $items = InventoryItem::query()
            ->with('warehouse')
            ->when($warehouseId !== null, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->orderBy('name')
            ->get();
        $warehouses = Warehouse::query()->orderBy('name')->get();

        return [
            'filter_warehouse' => $warehouseId !== null
                ? $warehouses->firstWhere('id', $warehouseId)?->name
                : null,
            'rows' => $items->map(fn (InventoryItem $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category->value,
                'warehouse' => $item->warehouse?->name,
                'current_stock' => number_format((float) $item->current_stock, 2),
                'unit' => $item->unit->value,
                'min_stock' => $item->min_stock !== null ? number_format((float) $item->min_stock, 2) : null,
                'cost_per_unit' => $item->cost_per_unit !== null ? number_format((float) $item->cost_per_unit, 2) : null,
                'is_active' => $item->is_active,
                'low_stock' => $item->min_stock !== null && (float) $item->current_stock <= (float) $item->min_stock,
            ])->values()->all(),
            'warehouse_options' => $warehouses->map(fn (Warehouse $warehouse): array => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
            ])->values()->all(),
            'category_options' => array_map(fn (InventoryCategory $c): array => ['value' => $c->value, 'label' => ucfirst(str_replace('_', ' ', $c->value))], InventoryCategory::cases()),
            'unit_options' => array_map(fn (InventoryUnit $u): array => ['value' => $u->value, 'label' => ucfirst($u->value)], InventoryUnit::cases()),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    /** @return array<string, mixed> */
    public function itemDetailView(InventoryItem $item): array
    {
        $tenant = $this->requireTenant();
        $item->loadMissing(['warehouse', 'movements.creator']);

        return [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category->value,
                'warehouse' => $item->warehouse?->name,
                'warehouse_id' => $item->warehouse_id,
                'unit' => $item->unit->value,
                'current_stock' => number_format((float) $item->current_stock, 2),
                'min_stock' => $item->min_stock !== null ? number_format((float) $item->min_stock, 2) : null,
                'cost_per_unit' => $item->cost_per_unit !== null ? number_format((float) $item->cost_per_unit, 2) : null,
                'notes' => $item->notes,
                'low_stock' => $item->min_stock !== null && (float) $item->current_stock <= (float) $item->min_stock,
            ],
            'movements' => $item->movements()
                ->with('creator')
                ->orderByDesc('occurred_at')
                ->get()
                ->map(fn (InventoryMovement $movement): array => [
                    'id' => $movement->id,
                    'movement_type' => $movement->movement_type->value,
                    'quantity' => number_format((float) $movement->quantity, 2),
                    'occurred_at' => $movement->occurred_at?->format('Y-m-d H:i'),
                    'reference_type' => $movement->reference_type?->value,
                    'reference_id' => $movement->reference_id,
                    'notes' => $movement->notes,
                    'created_by' => $movement->creator?->name,
                ])->values()->all(),
            'movement_type_options' => array_map(fn (InventoryMovementType $t): array => ['value' => $t->value, 'label' => ucfirst($t->value)], InventoryMovementType::cases()),
            'reference_type_options' => array_map(fn (InventoryReferenceType $t): array => ['value' => $t->value, 'label' => ucfirst(str_replace('_', ' ', $t->value))], InventoryReferenceType::cases()),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
        ];
    }

    private function requireTenant()
    {
        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');

        return $tenant;
    }
}
