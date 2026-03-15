<?php

namespace App\Console\Commands;

use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use Illuminate\Console\Command;

class MarkOverdueInvoicesCommand extends Command
{
    protected $signature = 'billing:mark-overdue';

    protected $description = 'Mark pending invoices as overdue when due_at is in the past';

    public function handle(BillingService $billingService): int
    {
        $count = 0;

        BillingInvoice::query()
            ->withoutGlobalScopes()
            ->where('status', BillingInvoiceStatus::PENDING->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->chunkById(100, function ($invoices) use ($billingService, &$count): void {
                foreach ($invoices as $invoice) {
                    $billingService->markInvoiceOverdue($invoice);
                    $count++;
                }
            });

        $this->info("Marked {$count} invoices as overdue.");

        return self::SUCCESS;
    }
}
