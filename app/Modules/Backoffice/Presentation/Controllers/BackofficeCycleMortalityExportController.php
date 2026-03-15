<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeCycleExportService;
use App\Modules\Production\Domain\Models\Cycle;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BackofficeCycleMortalityExportController extends Controller
{
    public function __invoke(int $cycleId, BackofficeCycleExportService $exports): StreamedResponse
    {
        $cycle = Cycle::query()->with('pond')->findOrFail($cycleId);

        return $exports->downloadMortalities($cycle);
    }
}
