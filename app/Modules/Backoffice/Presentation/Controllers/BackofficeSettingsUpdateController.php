<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Backoffice\Presentation\Requests\UpdateTenantProfileRequest;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeSettingsUpdateController extends Controller
{
    public function __invoke(
        UpdateTenantProfileRequest $request,
        TenantContext $tenantContext,
        LicenseService $licenseService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_unless($tenant instanceof Tenant, 400, 'Tenant context is not available.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'La suscripción actual está en modo solo lectura.');

        $tenant->update($request->validated());

        $auditLogService->record(
            actionKey: 'tenant.profile_updated',
            entityType: 'Tenant',
            entityId: $tenant->id,
            context: ['keys' => array_keys($request->validated())],
            tenant: $tenant,
            user: $request->user(),
        );

        return redirect('/backoffice/settings')->with('status', __('settings.saved'));
    }
}
