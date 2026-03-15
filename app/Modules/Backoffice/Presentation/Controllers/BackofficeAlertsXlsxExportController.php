<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeAlertsExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeAlertsXlsxExportController extends Controller
{
    public function __invoke(Request $request, BackofficeAlertsExportService $service): BinaryFileResponse
    {
        return $service->download($request->query());
    }
}
