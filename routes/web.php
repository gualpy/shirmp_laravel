<?php

use App\Modules\Backoffice\Presentation\Controllers\BackofficeCycleListController;
use App\Modules\Backoffice\Presentation\Controllers\BackofficeHomeController;
use App\Modules\Backoffice\Presentation\Controllers\CycleDetailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'tenant.backoffice', 'subscription.active'])
    ->prefix('backoffice')
    ->group(function (): void {
        Route::get('/', BackofficeHomeController::class)->name('backoffice.home');
        Route::get('/cycles', BackofficeCycleListController::class)->name('backoffice.cycles.index');
        Route::get('/cycles/{cycle}', CycleDetailController::class)->name('backoffice.cycles.show');
    });
