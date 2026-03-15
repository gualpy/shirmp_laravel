<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleReportService;
use App\Modules\Production\Domain\Models\Cycle;
use Illuminate\Contracts\View\View;

final class BackofficeCycleExecutiveReportController extends Controller
{
    public function __invoke(int $cycleId, BackofficeCycleReportService $reports): View
    {
        $cycle = Cycle::query()->with(['pond.farm', 'stocking'])->findOrFail($cycleId);

        return view('backoffice.cycle-report', [
            'vm' => $reports->build($cycle),
        ]);
    }
}
