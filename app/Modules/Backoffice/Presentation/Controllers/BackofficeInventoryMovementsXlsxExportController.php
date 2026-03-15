<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeInventoryExportService;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeInventoryMovementsXlsxExportController extends Controller
{
    public function __invoke(int $itemId, BackofficeInventoryExportService $service): BinaryFileResponse
    {
        $item = InventoryItem::query()->findOrFail($itemId);

        return $service->downloadItemMovements($item);
    }
}
