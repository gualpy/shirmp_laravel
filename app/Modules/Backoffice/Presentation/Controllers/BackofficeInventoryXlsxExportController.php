<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeInventoryExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeInventoryXlsxExportController extends Controller
{
    public function __invoke(BackofficeInventoryExportService $service): BinaryFileResponse
    {
        return $service->downloadInventoryItems();
    }
}
