<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Backoffice\Presentation\Requests\UpdateProjectionSettingsRequest;
use App\Modules\Configuration\Domain\Models\TenantSetting;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Http\RedirectResponse;

final class BackofficeProjectionSettingsUpdateController extends Controller
{
    public function __invoke(
        UpdateProjectionSettingsRequest $request,
        TenantContext $tenantContext,
        LicenseService $licenseService,
        AuditLogService $auditLogService,
    ): RedirectResponse {
        $tenant = $tenantContext->currentTenant();
        abort_unless($tenant instanceof Tenant, 400, 'Tenant context is not available.');
        abort_if((bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'], 403, 'La suscripción actual está en modo solo lectura.');

        TenantSetting::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $request->validated(),
        );

        $auditLogService->record(
            actionKey: 'tenant.projection_settings_updated',
            entityType: 'Tenant',
            entityId: $tenant->id,
            context: ['keys' => array_keys($request->validated())],
            tenant: $tenant,
            user: $request->user(),
        );

        return redirect('/backoffice/settings')->with('status', __('settings.projection_saved'));
    }
}
