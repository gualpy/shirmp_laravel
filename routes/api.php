<?php

use App\Http\Middleware\ResolveTenant;
use App\Modules\Auth\Presentation\Controllers\LoginController;
use App\Modules\Auth\Presentation\Controllers\LogoutController;
use App\Modules\Auth\Presentation\Controllers\MeController;
use App\Modules\Auth\Presentation\Controllers\RegisterController;
use App\Modules\Feeding\Presentation\Controllers\FeedEntryController;
use App\Modules\Feeding\Presentation\Controllers\FeedTypeController;
use App\Modules\Production\Presentation\Controllers\CycleController;
use App\Modules\Production\Presentation\Controllers\CycleMetricsController;
use App\Modules\Production\Presentation\Controllers\FarmController;
use App\Modules\Production\Presentation\Controllers\HarvestController;
use App\Modules\Production\Presentation\Controllers\HealthController;
use App\Modules\Production\Presentation\Controllers\PondController;
use App\Modules\Production\Presentation\Controllers\SamplingController;
use App\Modules\Production\Presentation\Controllers\StockingController;
use App\Modules\Shared\Presentation\Controllers\CurrentTenantController;
use App\Modules\Shared\Presentation\Controllers\TenantNoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->withoutMiddleware(ResolveTenant::class);

    Route::get('/tenant/current', CurrentTenantController::class);
    Route::get('/tenant-notes', [TenantNoteController::class, 'index']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', RegisterController::class);
        Route::post('/login', LoginController::class);
        Route::get('/me', MeController::class)->middleware('auth:sanctum');
        Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
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

        Route::get('/feed-types', [FeedTypeController::class, 'index']);
        Route::post('/feed-types', [FeedTypeController::class, 'store']);
        Route::patch('/feed-types/{feedType}', [FeedTypeController::class, 'update']);

        Route::get('/cycles/{cycle}/feed-entries', [FeedEntryController::class, 'index']);
        Route::post('/cycles/{cycle}/feed-entries', [FeedEntryController::class, 'store']);
    });
});
