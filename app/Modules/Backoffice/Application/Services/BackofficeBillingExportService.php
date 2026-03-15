<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\Tenant;
use App\Modules\Backoffice\Presentation\Exports\CycleArrayExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BackofficeBillingExportService
{
    public function __construct(private readonly BackofficeBillingViewService $billingViewService)
    {
    }

    public function downloadTenantBilling(Tenant $tenant): BinaryFileResponse
    {
        $vm = $this->billingViewService->tenantView($tenant);

        $rows = collect($vm['invoices'])
            ->map(fn (array $invoice): array => [
                $invoice['invoice_number'],
                $this->periodStart($invoice['period']),
                $this->periodEnd($invoice['period']),
                $invoice['amount_usd'],
                'USD',
                $invoice['status'],
                $invoice['issued_at'],
                $invoice['due_at'],
                $invoice['paid_at'],
            ])
            ->all();

        $filename = sprintf(
            '%s.xlsx',
            Str::slug(($tenant->slug ?: 'tenant').'-billing-'.now()->format('Y-m'))
        );

        return Excel::download(
            new CycleArrayExport('Billing', [
                'invoice_number',
                'billing_period_start',
                'billing_period_end',
                'amount_usd',
                'currency',
                'status',
                'issued_at',
                'due_at',
                'paid_at',
            ], $rows),
            $filename
        );
    }

    private function periodStart(string $period): string
    {
        return trim(explode('->', $period)[0] ?? '');
    }

    private function periodEnd(string $period): string
    {
        return trim(explode('->', $period)[1] ?? '');
    }
}
