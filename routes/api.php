<?php

use App\Http\Middleware\ResolveTenant;
use App\Modules\Production\Presentation\Controllers\HealthController;
use App\Modules\Shared\Presentation\Controllers\CurrentTenantController;
use App\Modules\Shared\Presentation\Controllers\TenantNoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->withoutMiddleware(ResolveTenant::class);

    Route::get('/tenant/current', CurrentTenantController::class);
    Route::get('/tenant-notes', [TenantNoteController::class, 'index']);
});
