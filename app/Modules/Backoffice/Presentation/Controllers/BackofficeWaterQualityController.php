<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use App\Modules\Backoffice\Application\Services\BackofficeWaterQualityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeWaterQualityController extends Controller
{
    public function __invoke(
        Request $request,
        BackofficeWaterQualityService $waterQualityService,
        BackofficeShellService $shellService,
    ): View {
        return view('backoffice.water-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $waterQualityService->build($request->query()),
            'activeMenu' => 'water_quality',
        ]);
    }
}
