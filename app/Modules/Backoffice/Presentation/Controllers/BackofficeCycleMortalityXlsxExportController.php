<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleExportService;
use App\Modules\Production\Domain\Models\Cycle;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeCycleMortalityXlsxExportController extends Controller
{
    public function __invoke(int $cycleId, BackofficeCycleExportService $exports): BinaryFileResponse
    {
        $cycle = Cycle::query()->with('pond')->findOrFail($cycleId);

        return $exports->downloadMortalitiesXlsx($cycle);
    }
}
