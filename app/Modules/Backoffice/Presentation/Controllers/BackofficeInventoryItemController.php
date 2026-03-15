<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeInventoryService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Inventory\Domain\Models\InventoryItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeInventoryItemController extends Controller
{
    public function __invoke(int $itemId, Request $request, BackofficeInventoryService $service, BackofficeShellService $shellService): View
    {
        $item = InventoryItem::query()->findOrFail($itemId);

        return view('backoffice.inventory-show', [
            'shell' => $shellService->build($request->user()),
            'vm' => $service->itemDetailView($item),
            'activeMenu' => 'inventory',
        ]);
    }
}
