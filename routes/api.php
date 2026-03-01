<?php

use App\Http\Middleware\ResolveTenant;
use App\Modules\Auth\Presentation\Controllers\LoginController;
use App\Modules\Auth\Presentation\Controllers\LogoutController;
use App\Modules\Auth\Presentation\Controllers\MeController;
use App\Modules\Auth\Presentation\Controllers\RegisterController;
use App\Modules\Production\Presentation\Controllers\HealthController;
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
});
