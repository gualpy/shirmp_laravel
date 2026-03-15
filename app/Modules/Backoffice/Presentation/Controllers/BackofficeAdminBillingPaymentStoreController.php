<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Presentation\Requests\StoreManualBillingPaymentRequest;
use Illuminate\Http\RedirectResponse;

final class BackofficeAdminBillingPaymentStoreController extends Controller
{
    public function __invoke(int $invoiceId, StoreManualBillingPaymentRequest $request, BillingService $billingService): RedirectResponse
    {
        $invoice = BillingInvoice::query()->withoutGlobalScopes()->with('tenant')->findOrFail($invoiceId);
        $billingService->registerManualPayment($invoice, $request->validated());

        return back()->with('status', 'Pago manual registrado.');
    }
}
