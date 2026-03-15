<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Presentation\Requests\StoreManualBillingPaymentRequest;
use Illuminate\Http\RedirectResponse;

final class BackofficeAdminBillingManualPaymentStoreController extends Controller
{
    public function __invoke(BillingInvoice $invoice, StoreManualBillingPaymentRequest $request, BillingService $billingService): RedirectResponse
    {
        $billingService->registerManualPayment($invoice, $request->validated());

        return back()->with('status', 'Pago manual registrado.');
    }
}
