<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleExportService;
use App\Modules\Production\Domain\Models\Cycle;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeCycleCostsXlsxExportController extends Controller
{
    public function __invoke(int $cycleId, BackofficeCycleExportService $exports): BinaryFileResponse
    {
        $cycle = Cycle::query()->with(['pond.farm'])->findOrFail($cycleId);

        return $exports->downloadCostsXlsx($cycle);
    }
}
