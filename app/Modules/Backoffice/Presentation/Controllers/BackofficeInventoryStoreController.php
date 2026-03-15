<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Presentation\Requests\StoreInventoryItemRequest;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeInventoryStoreController extends Controller
{
    public function __invoke(StoreInventoryItemRequest $request, InventoryService $inventoryService, TenantContext $tenantContext, LicenseService $licenseService): RedirectResponse
    {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'La suscripción actual está en modo solo lectura.');

        $inventoryService->createItem($tenant, $request->validated(), $request->user());

        return redirect('/backoffice/inventory')->with('status', 'Item de inventario creado.');
    }
}
