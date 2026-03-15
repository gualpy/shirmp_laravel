<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Backoffice\Presentation\Exports\CycleArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeAlertsExportService
{
    public function __construct(private readonly BackofficeAlertsService $alertsService)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function download(array $filters): BinaryFileResponse
    {
        $rows = collect($this->alertsService->exportRows($filters))
            ->map(fn (array $row): array => [
                $row['date'],
                $row['severity'],
                $row['type'],
                $row['title'],
                $row['message'],
                $row['state'],
                $row['farm'],
                $row['pond'],
                $row['cycle'],
            ])
            ->all();

        return Excel::download(
            new CycleArrayExport('Alerts', [
                'detected_at',
                'severity',
                'rule_code',
                'title',
                'message',
                'state',
                'farm',
                'pond',
                'cycle',
            ], $rows),
            'alerts-export.xlsx'
        );
    }
}
