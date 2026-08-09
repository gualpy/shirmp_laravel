<?php

use App\Http\Middleware\ResolveTenant;
use App\Modules\Alerts\Presentation\Controllers\AlertController;
use App\Modules\Auth\Presentation\Controllers\LoginController;
use App\Modules\Auth\Presentation\Controllers\LogoutController;
use App\Modules\Auth\Presentation\Controllers\MeController;
use App\Modules\Auth\Presentation\Controllers\RegisterController;
use App\Modules\Configuration\Presentation\Controllers\FarmSettingController;
use App\Modules\Configuration\Presentation\Controllers\FeedingGrowthTableController;
use App\Modules\Configuration\Presentation\Controllers\FeedingGrowthTableRowController;
use App\Modules\Configuration\Presentation\Controllers\TenantSettingController;
use App\Modules\Costing\Presentation\Controllers\CycleCostController;
use App\Modules\Costing\Presentation\Controllers\OperationalCostController;
use App\Modules\Dashboard\Presentation\Controllers\FarmDashboardController;
use App\Modules\Dashboard\Presentation\Controllers\TenantDashboardController;
use App\Modules\Feeding\Presentation\Controllers\FeedEntryController;
use App\Modules\Feeding\Presentation\Controllers\FeedTypeController;
use App\Modules\Production\Presentation\Controllers\CycleController;
use App\Modules\Production\Presentation\Controllers\DailyMortalityController;
use App\Modules\Production\Presentation\Controllers\CycleMetricsController;
use App\Modules\Production\Presentation\Controllers\CycleProjectionController;
use App\Modules\Production\Presentation\Controllers\FarmController;
use App\Modules\Production\Presentation\Controllers\HarvestController;
use App\Modules\Production\Presentation\Controllers\HealthController;
use App\Modules\Production\Presentation\Controllers\PondController;
use App\Modules\Production\Presentation\Controllers\SamplingController;
use App\Modules\Production\Presentation\Controllers\StockingController;
use App\Modules\SaaS\Presentation\Controllers\AdminPlanController;
use App\Modules\SaaS\Presentation\Controllers\AdminPlanFeatureController;
use App\Modules\SaaS\Presentation\Controllers\AdminPlanLimitController;
use App\Modules\SaaS\Presentation\Controllers\AdminTenantSubscriptionController;
use App\Modules\SaaS\Presentation\Controllers\SubscriptionStatusController;
use App\Modules\Billing\Presentation\Controllers\PayPalWebhookController;
use App\Modules\Billing\Presentation\Controllers\StripeWebhookController;
use App\Modules\Shared\Presentation\Controllers\CurrentTenantController;
use App\Modules\Shared\Presentation\Controllers\TenantNoteController;
use App\Modules\WaterQuality\Presentation\Controllers\CycleWaterQualityController;
use App\Modules\WaterQuality\Presentation\Controllers\PondWaterQualityController;
use App\Modules\WaterQuality\Presentation\Controllers\QuickWaterQualityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->withoutMiddleware(ResolveTenant::class);

    Route::post('/webhooks/stripe', StripeWebhookController::class)->withoutMiddleware(ResolveTenant::class);
    Route::post('/webhooks/paypal', PayPalWebhookController::class)->withoutMiddleware(ResolveTenant::class);

    Route::get('/tenant/current', CurrentTenantController::class);
    Route::get('/tenant-notes', [TenantNoteController::class, 'index']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', RegisterController::class);
        Route::post('/login', LoginController::class);
        Route::get('/me', MeController::class)->middleware(['auth:sanctum', 'subscription.active']);
        Route::post('/logout', LogoutController::class)->middleware(['auth:sanctum', 'subscription.active', 'write.allowed']);
    });

    Route::get('/subscription/status', SubscriptionStatusController::class)->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'subscription.active', 'write.allowed'])->group(function (): void {
        Route::middleware('role.module:production')->group(function (): void {
            Route::apiResource('farms', FarmController::class);
            Route::apiResource('ponds', PondController::class);

            Route::get('/ponds/{pond}/cycles', [CycleController::class, 'indexByPond']);
            Route::post('/ponds/{pond}/cycles', [CycleController::class, 'store']);
            Route::get('/cycles/{cycle}', [CycleController::class, 'show']);
            Route::patch('/cycles/{cycle}', [CycleController::class, 'update']);

            Route::get('/cycles/{cycle}/stocking', [StockingController::class, 'show']);
            Route::post('/cycles/{cycle}/stocking', [StockingController::class, 'store']);
            Route::patch('/cycles/{cycle}/stocking', [StockingController::class, 'update']);

            Route::get('/cycles/{cycle}/samplings', [SamplingController::class, 'index']);
            Route::post('/cycles/{cycle}/samplings', [SamplingController::class, 'store']);

            Route::get('/cycles/{cycle}/harvests', [HarvestController::class, 'index']);
            Route::post('/cycles/{cycle}/harvests', [HarvestController::class, 'store']);
            Route::get('/harvests/{harvest}', [HarvestController::class, 'show']);
            Route::get('/cycles/{cycle}/metrics', CycleMetricsController::class);
            Route::get('/cycles/{cycle}/projection', CycleProjectionController::class);

            Route::get('/feed-types', [FeedTypeController::class, 'index']);
            Route::post('/feed-types', [FeedTypeController::class, 'store']);
            Route::patch('/feed-types/{feedType}', [FeedTypeController::class, 'update']);

            Route::get('/cycles/{cycle}/feed-entries', [FeedEntryController::class, 'index']);
            Route::post('/cycles/{cycle}/feed-entries', [FeedEntryController::class, 'store']);
        });

        Route::middleware('role.module:mortality')->group(function (): void {
            Route::get('/cycles/{cycle}/mortalities', [DailyMortalityController::class, 'index']);
            Route::post('/mortalities', [DailyMortalityController::class, 'store']);
        });

        Route::middleware(['feature:water_quality', 'role.module:water'])->group(function (): void {
            Route::post('/water-quality', QuickWaterQualityController::class);
            Route::get('/cycles/{cycle}/water-quality', [CycleWaterQualityController::class, 'index']);
            Route::post('/cycles/{cycle}/water-quality', [CycleWaterQualityController::class, 'store']);
            Route::get('/cycles/{cycle}/water-quality/latest', [CycleWaterQualityController::class, 'latest']);
            Route::get('/ponds/{pond}/water-quality', [PondWaterQualityController::class, 'index']);
        });

        Route::middleware(['feature:cost_engine', 'role.module:costs'])->group(function (): void {
            Route::get('/cycles/{cycle}/operational-costs', [OperationalCostController::class, 'index']);
            Route::post('/cycles/{cycle}/operational-costs', [OperationalCostController::class, 'store']);
            Route::get('/cycles/{cycle}/costs', CycleCostController::class);
        });

        Route::middleware('role.module:settings')->group(function (): void {
            Route::get('/tenant/settings', [TenantSettingController::class, 'show']);
            Route::patch('/tenant/settings', [TenantSettingController::class, 'update']);
            Route::get('/farms/{farm}/settings', [FarmSettingController::class, 'show']);
            Route::patch('/farms/{farm}/settings', [FarmSettingController::class, 'update']);
        });

        Route::middleware('role.module:settings')->group(function (): void {
            Route::get('/feeding-tables', [FeedingGrowthTableController::class, 'index']);
            Route::post('/feeding-tables', [FeedingGrowthTableController::class, 'store']);
            Route::patch('/feeding-tables/{table}', [FeedingGrowthTableController::class, 'update']);
            Route::post('/feeding-tables/{table}/rows', [FeedingGrowthTableRowController::class, 'store']);
            Route::delete('/feeding-tables/{table}/rows/{row}', [FeedingGrowthTableRowController::class, 'destroy']);
        });

        Route::middleware(['feature:alerts', 'role.module:alerts'])->group(function (): void {
            Route::get('/alerts', [AlertController::class, 'index']);
            Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
            Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);
        });

        Route::middleware(['feature:dashboard', 'role.module:dashboard'])->group(function (): void {
            Route::get('/dashboard/tenant', TenantDashboardController::class);
            Route::get('/dashboard/farms/{farm}', FarmDashboardController::class);
        });
    });

    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'superadmin'])
        ->group(function (): void {
            Route::post('/plans', [AdminPlanController::class, 'store']);
            Route::post('/plans/{plan}/limits', [AdminPlanLimitController::class, 'store']);
            Route::post('/plans/{plan}/features', [AdminPlanFeatureController::class, 'store']);
            Route::post('/tenants/{tenant}/assign-plan', [AdminTenantSubscriptionController::class, 'assignPlan']);
            Route::post('/tenants/{tenant}/activate-onprem', [AdminTenantSubscriptionController::class, 'activateOnPrem']);
            Route::post('/tenants/{tenant}/verify-now', [AdminTenantSubscriptionController::class, 'verifyNow']);
            Route::patch('/subscriptions/{subscription}', [AdminTenantSubscriptionController::class, 'update']);
    });
});
