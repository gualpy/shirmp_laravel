<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Presentation\Requests\MarkBillingInvoicePaidRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

final class BackofficeAdminBillingInvoiceMarkPaidController extends Controller
{
    public function __invoke(int $invoiceId, MarkBillingInvoicePaidRequest $request, BillingService $billingService): RedirectResponse
    {
        $invoice = BillingInvoice::query()->withoutGlobalScopes()->with('tenant')->findOrFail($invoiceId);
        $payload = $request->validated();

        $billingService->markInvoicePaid(
            $invoice,
            isset($payload['paid_at']) ? Carbon::parse($payload['paid_at']) : null,
            $payload['payment_method'] ?? null,
            $payload['notes'] ?? null,
        );

        return back()->with('status', 'Invoice marcada como pagada.');
    }
}
