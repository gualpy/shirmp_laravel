<?php

use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleListController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertAcknowledgeController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertResolveController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeAlertsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleCostsController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeHomeController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeOperationalCostStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeWaterQualityController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeWaterQualityStoreController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeSessionController;
use App\Modules\Backoffice\Presentation\Controllers\CycleDetailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [BackofficeSessionController::class, 'create'])->name('login');
Route::post('/login', [BackofficeSessionController::class, 'store'])->name('backoffice.login.store');
Route::post('/logout', [BackofficeSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['tenant.backoffice', 'auth', 'subscription.active'])
    ->prefix('backoffice')
    ->group(function (): void {
        Route::get('/', BackofficeHomeController::class)->name('backoffice.home');
        Route::get('/alerts', BackofficeAlertsController::class)->name('backoffice.alerts.index');
        Route::post('/alerts/{alertId}/acknowledge', BackofficeAlertAcknowledgeController::class)->name('backoffice.alerts.acknowledge');
        Route::post('/alerts/{alertId}/resolve', BackofficeAlertResolveController::class)->name('backoffice.alerts.resolve');
        Route::get('/water', BackofficeWaterQualityController::class)->name('backoffice.water.index');
        Route::post('/water', BackofficeWaterQualityStoreController::class)->name('backoffice.water.store');
        Route::get('/cycles', BackofficeCycleListController::class)->name('backoffice.cycles.index');
        Route::get('/cycles/{cycleId}/costs', BackofficeCycleCostsController::class)->name('backoffice.cycles.costs');
        Route::post('/cycles/{cycleId}/costs', BackofficeOperationalCostStoreController::class)->name('backoffice.cycles.costs.store');
        Route::get('/cycles/{cycleId}', CycleDetailController::class)->name('backoffice.cycles.show');
    });
