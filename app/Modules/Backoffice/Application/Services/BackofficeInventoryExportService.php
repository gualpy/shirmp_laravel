<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Backoffice\Presentation\Exports\CycleArrayExport;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeInventoryExportService
{
    public function __construct(private readonly BackofficeInventoryService $inventoryService)
    {
    }

    public function downloadInventoryItems(): BinaryFileResponse
    {
        $vm = $this->inventoryService->inventoryIndexView();

        $rows = collect($vm['rows'])
            ->map(fn (array $row): array => [
                $row['warehouse'],
                $row['category'],
                $row['name'],
                $row['unit'],
                $row['current_stock'],
                $row['min_stock'],
                $row['cost_per_unit'],
                $row['low_stock'] ? 'yes' : 'no',
                $row['is_active'] ? 'yes' : 'no',
            ])
            ->all();

        return Excel::download(
            new CycleArrayExport('Inventory Items', [
                'warehouse',
                'category',
                'name',
                'unit',
                'current_stock',
                'min_stock',
                'cost_per_unit',
                'low_stock',
                'is_active',
            ], $rows),
            'inventory-items.xlsx'
        );
    }

    public function downloadItemMovements(InventoryItem $item): BinaryFileResponse
    {
        $vm = $this->inventoryService->itemDetailView($item);

        $rows = collect($vm['movements'])
            ->map(fn (array $row): array => [
                $row['occurred_at'],
                $row['movement_type'],
                $row['quantity'],
                $row['reference_type'],
                $row['reference_id'],
                $row['notes'],
                $row['created_by'],
            ])
            ->all();

        return Excel::download(
            new CycleArrayExport('Inventory Movements', [
                'occurred_at',
                'movement_type',
                'quantity',
                'reference_type',
                'reference_id',
                'notes',
                'created_by',
            ], $rows),
            'inventory-item-'.$item->id.'-movements.xlsx'
        );
    }
}
