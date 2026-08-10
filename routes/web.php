<?php

use App\Http\Controllers\HealthzController;
use App\Http\Controllers\ReadyzController;
use App\Modules\SaaS\Presentation\Controllers\LandingController;
use App\Modules\SaaS\Presentation\Controllers\SignupController;
use App\Modules\SaaS\Presentation\Controllers\SignupPendingController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertAcknowledgeController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertResolveController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertsXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAuditController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeBillingCheckoutStartController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeBillingCheckoutReturnController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeBillingRenewController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleCostsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleCostsXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleExecutiveReportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleFeedingController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleFeedingStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleHarvestController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleHarvestStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleSamplingController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleSamplingStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleFeedExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleFeedXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleListController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleMortalityController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleMortalityExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleMortalityXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleSamplingExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleSamplingXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleWaterExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleWaterXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeDailyMortalityStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeFarmStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeFarmsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeHomeController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeInventoryController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeInventoryItemController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeInventoryMovementStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeInventoryMovementsXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeInventoryStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeInventoryXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeOperationalCostStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficePondStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficePondsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeSessionController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeSettingsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeSettingsUpdateController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeStockingCreateController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeStockingStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeTenantBillingController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeTenantBillingXlsxExportController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeWarehouseStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeWarehousesController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeWaterQualityController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeWaterQualityStoreController;
use App\Modules\Backoffice\Presentation\Controllers\CycleDetailController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminAuditController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminBillingController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminBillingInvoiceMarkPaidController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminBillingInvoiceStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminBillingPaymentStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminOpsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminPlansController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminTenantBillingController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminTenantCreateController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminTenantDetailController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminTenantsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAdminTenantStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeSuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/healthz', HealthzController::class)->name('healthz');
Route::get('/readyz', ReadyzController::class)->name('readyz');

Route::get('/', LandingController::class)->name('landing');
Route::get('/app', [BackofficeSessionController::class, 'create'])->name('app.login');
Route::get('/login', [BackofficeSessionController::class, 'create'])->name('login');
Route::post('/login', [BackofficeSessionController::class, 'store'])->name('backoffice.login.store');
Route::post('/logout', [BackofficeSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/signup', [SignupController::class, 'create'])->name('signup.create');
Route::post('/signup', [SignupController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('signup.store');
Route::get('/signup/{tenant}/pending', SignupPendingController::class)->name('signup.pending');

Route::middleware(['tenant.backoffice', 'auth', 'subscription.active'])
    ->prefix('backoffice')
    ->group(function (): void {
        Route::get('/', BackofficeHomeController::class)
            ->middleware('role.module:dashboard')
            ->name('backoffice.home');

        Route::get('/alerts', BackofficeAlertsController::class)
            ->middleware('role.module:alerts')
            ->name('backoffice.alerts.index');
        Route::get('/alerts/export.xlsx', BackofficeAlertsXlsxExportController::class)
            ->middleware('role.module:alerts')
            ->name('backoffice.alerts.export.xlsx');
        Route::post('/alerts/{alertId}/acknowledge', BackofficeAlertAcknowledgeController::class)
            ->middleware('role.module:alerts')
            ->name('backoffice.alerts.acknowledge');
        Route::post('/alerts/{alertId}/resolve', BackofficeAlertResolveController::class)
            ->middleware('role.module:alerts')
            ->name('backoffice.alerts.resolve');

        Route::get('/audit', BackofficeAuditController::class)
            ->middleware('role.module:audit')
            ->name('backoffice.audit.index');

        Route::get('/billing', BackofficeTenantBillingController::class)
            ->middleware('role.module:billing')
            ->name('backoffice.billing.index');
        Route::get('/billing/export.xlsx', BackofficeTenantBillingXlsxExportController::class)
            ->middleware('role.module:billing')
            ->name('backoffice.billing.export.xlsx');
        Route::post('/billing/invoices/{invoiceId}/checkout', BackofficeBillingCheckoutStartController::class)
            ->middleware('role.module:billing')
            ->name('backoffice.billing.checkout.start');
        Route::get('/billing/invoices/{invoiceId}/checkout/return', BackofficeBillingCheckoutReturnController::class)
            ->middleware('role.module:billing')
            ->name('backoffice.billing.checkout.return');
        Route::post('/billing/renew', BackofficeBillingRenewController::class)
            ->middleware('role.module:billing')
            ->name('backoffice.billing.renew');

        Route::get('/settings', BackofficeSettingsController::class)
            ->middleware('role.module:settings')
            ->name('backoffice.settings.index');
        Route::post('/settings', BackofficeSettingsUpdateController::class)
            ->middleware('role.module:settings')
            ->name('backoffice.settings.update');

        Route::get('/warehouses', BackofficeWarehousesController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.warehouses.index');
        Route::post('/warehouses', BackofficeWarehouseStoreController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.warehouses.store');

        Route::get('/inventory', BackofficeInventoryController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.inventory.index');
        Route::get('/inventory/export.xlsx', BackofficeInventoryXlsxExportController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.inventory.export.xlsx');
        Route::post('/inventory', BackofficeInventoryStoreController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.inventory.store');
        Route::get('/inventory/{itemId}', BackofficeInventoryItemController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.inventory.show');
        Route::get('/inventory/{itemId}/movements.xlsx', BackofficeInventoryMovementsXlsxExportController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.inventory.movements.export.xlsx');
        Route::post('/inventory/{itemId}/movements', BackofficeInventoryMovementStoreController::class)
            ->middleware('role.module:inventory')
            ->name('backoffice.inventory.movements.store');

        Route::get('/water', BackofficeWaterQualityController::class)
            ->middleware('role.module:water')
            ->name('backoffice.water.index');
        Route::post('/water', BackofficeWaterQualityStoreController::class)
            ->middleware('role.module:water')
            ->name('backoffice.water.store');

        Route::get('/farms', BackofficeFarmsController::class)
            ->middleware('role.module:production')
            ->name('backoffice.farms.index');
        Route::post('/farms', BackofficeFarmStoreController::class)
            ->middleware('role.module:production')
            ->name('backoffice.farms.store');

        Route::get('/ponds', BackofficePondsController::class)
            ->middleware('role.module:production')
            ->name('backoffice.ponds.index');
        Route::post('/ponds', BackofficePondStoreController::class)
            ->middleware('role.module:production')
            ->name('backoffice.ponds.store');

        Route::get('/stocking/create', BackofficeStockingCreateController::class)
            ->middleware('role.module:production')
            ->name('backoffice.stocking.create');
        Route::post('/stocking', BackofficeStockingStoreController::class)
            ->middleware('role.module:production')
            ->name('backoffice.stocking.store');

        Route::get('/cycles', BackofficeCycleListController::class)
            ->middleware('role.module:production')
            ->name('backoffice.cycles.index');
        Route::get('/cycles/{cycleId}', CycleDetailController::class)
            ->middleware('role.module:production')
            ->name('backoffice.cycles.show');

        Route::get('/cycles/{cycleId}/exports/samplings.csv', BackofficeCycleSamplingExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.samplings');
        Route::get('/cycles/{cycleId}/exports/samplings.xlsx', BackofficeCycleSamplingXlsxExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.samplings.xlsx');
        Route::get('/cycles/{cycleId}/exports/feed.csv', BackofficeCycleFeedExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.feed');
        Route::get('/cycles/{cycleId}/exports/feed.xlsx', BackofficeCycleFeedXlsxExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.feed.xlsx');
        Route::get('/cycles/{cycleId}/exports/mortalities.csv', BackofficeCycleMortalityExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.mortalities');
        Route::get('/cycles/{cycleId}/exports/mortalities.xlsx', BackofficeCycleMortalityXlsxExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.mortalities.xlsx');
        Route::get('/cycles/{cycleId}/exports/water.csv', BackofficeCycleWaterExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.water');
        Route::get('/cycles/{cycleId}/exports/water.xlsx', BackofficeCycleWaterXlsxExportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.exports.water.xlsx');
        Route::get('/cycles/{cycleId}/report', BackofficeCycleExecutiveReportController::class)
            ->middleware('role.module:reports')
            ->name('backoffice.cycles.report');

        Route::get('/cycles/{cycleId}/feeding', BackofficeCycleFeedingController::class)
            ->middleware('role.module:feeding')
            ->name('backoffice.cycles.feeding');
        Route::post('/cycles/{cycleId}/feeding', BackofficeCycleFeedingStoreController::class)
            ->middleware('role.module:feeding')
            ->name('backoffice.cycles.feeding.store');

        Route::get('/cycles/{cycleId}/sampling', BackofficeCycleSamplingController::class)
            ->middleware('role.module:sampling')
            ->name('backoffice.cycles.sampling');
        Route::post('/cycles/{cycleId}/sampling', BackofficeCycleSamplingStoreController::class)
            ->middleware('role.module:sampling')
            ->name('backoffice.cycles.sampling.store');

        Route::get('/cycles/{cycleId}/harvest', BackofficeCycleHarvestController::class)
            ->middleware('role.module:harvest')
            ->name('backoffice.cycles.harvest');
        Route::post('/cycles/{cycleId}/harvest', BackofficeCycleHarvestStoreController::class)
            ->middleware('role.module:harvest')
            ->name('backoffice.cycles.harvest.store');

        Route::get('/cycles/{cycleId}/costs', BackofficeCycleCostsController::class)
            ->middleware('role.module:costs')
            ->name('backoffice.cycles.costs');
        Route::get('/cycles/{cycleId}/exports/costs.xlsx', BackofficeCycleCostsXlsxExportController::class)
            ->middleware('role.module:costs')
            ->name('backoffice.cycles.exports.costs.xlsx');
        Route::post('/cycles/{cycleId}/costs', BackofficeOperationalCostStoreController::class)
            ->middleware('role.module:costs')
            ->name('backoffice.cycles.costs.store');

        Route::get('/cycles/{cycleId}/mortalities', BackofficeCycleMortalityController::class)
            ->middleware('role.module:mortality')
            ->name('backoffice.cycles.mortalities');
        Route::post('/cycles/{cycleId}/mortalities', BackofficeDailyMortalityStoreController::class)
            ->middleware('role.module:mortality')
            ->name('backoffice.cycles.mortalities.store');
    });

Route::middleware(['tenant.backoffice', 'auth', 'superadmin'])
    ->prefix('backoffice/admin')
    ->group(function (): void {
        Route::get('/', BackofficeSuperAdminDashboardController::class)
            ->name('backoffice.admin.dashboard');

        Route::get('/audit', BackofficeAdminAuditController::class)
            ->name('backoffice.admin.audit.index');

        Route::get('/billing', BackofficeAdminBillingController::class)
            ->name('backoffice.admin.billing.index');
        Route::post('/billing/invoices/{invoiceId}/mark-paid', BackofficeAdminBillingInvoiceMarkPaidController::class)
            ->name('backoffice.admin.billing.invoices.mark-paid');
        Route::post('/billing/invoices/{invoiceId}/payments', BackofficeAdminBillingPaymentStoreController::class)
            ->name('backoffice.admin.billing.payments.store');

        Route::get('/ops', BackofficeAdminOpsController::class)
            ->name('backoffice.admin.ops');

        Route::get('/plans', BackofficeAdminPlansController::class)
            ->name('backoffice.admin.plans.index');

        Route::get('/tenants', BackofficeAdminTenantsController::class)
            ->name('backoffice.admin.tenants.index');
        Route::get('/tenants/create', BackofficeAdminTenantCreateController::class)
            ->name('backoffice.admin.tenants.create');
        Route::post('/tenants', BackofficeAdminTenantStoreController::class)
            ->name('backoffice.admin.tenants.store');
        Route::get('/tenants/{tenant}', BackofficeAdminTenantDetailController::class)
            ->name('backoffice.admin.tenants.show');
        Route::get('/tenants/{tenant}/billing', BackofficeAdminTenantBillingController::class)
            ->name('backoffice.admin.tenants.billing');
        Route::post('/tenants/{tenant}/billing/invoices', BackofficeAdminBillingInvoiceStoreController::class)
            ->name('backoffice.admin.tenants.billing.invoices.store');
    });
