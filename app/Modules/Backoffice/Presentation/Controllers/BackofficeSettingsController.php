<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Configuration\Application\Services\SettingsResolverService;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeSettingsController extends Controller
{
    public function __invoke(
        Request $request,
        TenantContext $tenantContext,
        BackofficeShellService $shellService,
        LicenseService $licenseService,
        SettingsResolverService $settingsResolver,
    ): View {
        $tenant = $tenantContext->currentTenant();
        abort_unless($tenant instanceof Tenant, 400, 'Tenant context is not available.');

        $settings = $settingsResolver->resolveTenantSettings($tenant);

        return view('backoffice.settings-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => [
                'tenant' => $tenant,
                'read_only_mode' => (bool) $licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
                'projection_settings' => [
                    'default_target_pp_grams' => (float) $settings['default_target_pp_grams'],
                    'default_sale_price_per_lb' => (float) $settings['default_sale_price_per_lb'],
                    'default_feed_cost_factor_per_kg_gain' => (float) $settings['default_feed_cost_factor_per_kg_gain'],
                ],
            ],
            'activeMenu' => 'settings',
        ]);
    }
}
