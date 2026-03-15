<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeBillingExportService;
use App\Multitenancy\TenantContext;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeTenantBillingXlsxExportController extends Controller
{
    public function __invoke(TenantContext $tenantContext, BackofficeBillingExportService $service): BinaryFileResponse
    {
        $tenant = $tenantContext->currentTenant();
        abort_if($tenant === null, 404);

        return $service->downloadTenantBilling($tenant);
    }
}
