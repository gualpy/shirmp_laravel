<?php

namespace App\Modules\Inventory\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class InventoryService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /** @param array<string, mixed> $payload */
    public function createWarehouse(Tenant $tenant, array $payload, ?User $user = null): Warehouse
    {
        $warehouse = Warehouse::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $payload['name'],
            'location' => $payload['location'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ]);

        $this->auditLogService->record(
            actionKey: 'warehouse.created',
            entityType: 'Warehouse',
            entityId: $warehouse->id,
            context: ['name' => $warehouse->name],
            tenant: $tenant,
            user: $user,
        );

        return $warehouse;
    }

    /** @param array<string, mixed> $payload */
    public function createItem(Tenant $tenant, array $payload, ?User $user = null): InventoryItem
    {
        $warehouse = Warehouse::query()->findOrFail((int) $payload['warehouse_id']);
        $this->assertSameTenant($tenant, $warehouse->tenant_id);

        $item = InventoryItem::query()->create([
            'tenant_id' => $tenant->id,
            'warehouse_id' => $warehouse->id,
            'category' => $payload['category'],
            'name' => $payload['name'],
            'unit' => $payload['unit'],
            'current_stock' => $payload['current_stock'] ?? 0,
            'min_stock' => $payload['min_stock'] ?? null,
            'cost_per_unit' => $payload['cost_per_unit'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'notes' => $payload['notes'] ?? null,
        ]);

        $this->auditLogService->record(
            actionKey: 'inventory_item.created',
            entityType: 'InventoryItem',
            entityId: $item->id,
            context: [
                'warehouse_id' => $warehouse->id,
                'category' => $payload['category'],
                'name' => $payload['name'],
                'cost_per_unit' => $payload['cost_per_unit'] ?? null,
            ],
            tenant: $tenant,
            user: $user,
        );

        return $item;
    }

    /** @param array<string, mixed> $payload */
    public function createMovement(Tenant $tenant, InventoryItem $item, array $payload, ?User $user = null): InventoryMovement
    {
        $this->assertSameTenant($tenant, $item->tenant_id);

        return DB::transaction(function () use ($tenant, $item, $payload, $user): InventoryMovement {
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);
            $warehouse = Warehouse::query()->findOrFail((int) $lockedItem->warehouse_id);
            $this->assertSameTenant($tenant, $warehouse->tenant_id);

            $type = InventoryMovementType::from($payload['movement_type']);
            $quantity = (float) $payload['quantity'];

            if ($type !== InventoryMovementType::ADJUSTMENT && $quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => ['Quantity must be greater than zero.']]);
            }

            if ($type === InventoryMovementType::ADJUSTMENT && $quantity == 0.0) {
                throw ValidationException::withMessages(['quantity' => ['Adjustment quantity cannot be zero.']]);
            }

            $delta = match ($type) {
                InventoryMovementType::IN => $quantity,
                InventoryMovementType::OUT => -$quantity,
                InventoryMovementType::ADJUSTMENT => $quantity,
            };

            $nextStock = (float) $lockedItem->current_stock + $delta;
            if ($nextStock < 0) {
                throw ValidationException::withMessages(['quantity' => ['Stock cannot go negative.']]);
            }

            $movement = InventoryMovement::query()->create([
                'tenant_id' => $tenant->id,
                'warehouse_id' => $warehouse->id,
                'inventory_item_id' => $lockedItem->id,
                'movement_type' => $type->value,
                'quantity' => $quantity,
                'occurred_at' => $payload['occurred_at'],
                'reference_type' => $payload['reference_type'] ?? 'manual',
                'reference_id' => $payload['reference_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'created_by' => $user?->id,
            ]);

            $lockedItem->update(['current_stock' => $nextStock]);

            $this->auditLogService->record(
                actionKey: 'inventory_movement.created',
                entityType: 'InventoryMovement',
                entityId: $movement->id,
                context: [
                    'inventory_item_id' => $lockedItem->id,
                    'movement_type' => $type->value,
                    'quantity' => $quantity,
                    'stock_after' => $nextStock,
                ],
                tenant: $tenant,
                user: $user,
            );

            return $movement;
        });
    }

    private function assertSameTenant(Tenant $tenant, int $scopedTenantId): void
    {
        abort_unless($tenant->id === $scopedTenantId, 404);
    }
}
