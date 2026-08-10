<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Models\Tenant;
use App\Modules\Billing\Application\Services\BillingService;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use Illuminate\Support\Collection;

final class BackofficeBillingViewService
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly SaaSService $saasService,
    ) {
    }

    /** @return array<string, mixed> */
    public function tenantView(Tenant $tenant): array
    {
        $invoices = $this->billingService->listInvoicesForTenant($tenant);
        $mappedInvoices = $this->mapInvoices($invoices, false)->all();
        $subscription = $this->saasService->currentSubscription($tenant);

        $hasOpenInvoice = collect($mappedInvoices)->contains('payable', true);
        $canRenew = $subscription !== null
            && $subscription->plan !== null
            && ! in_array($subscription->plan->billing_type, [PlanBillingType::ONPREM, PlanBillingType::LIFETIME], true)
            && ! $hasOpenInvoice;

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->company_display_name ?: $tenant->name,
                'slug' => $tenant->slug,
            ],
            'invoices' => $mappedInvoices,
            'payments' => $this->mapPayments($this->paymentsFromInvoices($invoices), false)->all(),
            'can_renew' => $canRenew,
            'subscription_ends_at' => $subscription?->ends_at?->format('Y-m-d'),
        ];
    }

    /** @return array<string, mixed> */
    public function adminGlobalView(): array
    {
        $invoices = $this->billingService->listInvoicesGlobal();

        return [
            'invoices' => $this->mapInvoices($invoices, true)->all(),
            'payments' => $this->mapPayments($this->paymentsFromInvoices($invoices), true)->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function adminTenantView(Tenant $tenant): array
    {
        $invoices = $this->billingService->listInvoicesForTenant($tenant);
        $subscription = $this->saasService->currentSubscription($tenant);

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->company_display_name ?: $tenant->name,
                'slug' => $tenant->slug,
            ],
            'subscription' => [
                'id' => $subscription?->id,
                'plan_name' => $subscription?->plan?->name,
                'plan_code' => $subscription?->plan?->code,
                'status' => $subscription?->status?->value,
                'billing_type' => $subscription?->plan?->billing_type?->value,
            ],
            'invoices' => $this->mapInvoices($invoices, false)->all(),
            'payments' => $this->mapPayments($this->paymentsFromInvoices($invoices), false)->all(),
            'invoice_defaults' => [
                'billing_period_start' => now()->startOfMonth()->format('Y-m-d'),
                'billing_period_end' => now()->endOfMonth()->format('Y-m-d'),
                'issued_at' => now()->format('Y-m-d\TH:i'),
                'due_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
                'currency' => 'USD',
                'status' => 'pending',
            ],
        ];
    }

    /** @param Collection<int, BillingInvoice> $invoices */
    private function mapInvoices(Collection $invoices, bool $includeTenant): Collection
    {
        return $invoices->map(function (BillingInvoice $invoice) use ($includeTenant): array {
            $latestPayment = $invoice->payments->sortByDesc(fn (BillingPayment $payment) => optional($payment->paid_at)->timestamp ?? 0)->first();
            $status = $invoice->status instanceof BillingInvoiceStatus ? $invoice->status : BillingInvoiceStatus::from($invoice->status);

            return [
                'id' => $invoice->id,
                'tenant_id' => $includeTenant ? $invoice->tenant_id : null,
                'invoice_number' => $invoice->invoice_number,
                'tenant_name' => $includeTenant ? ($invoice->tenant?->company_display_name ?: $invoice->tenant?->name) : null,
                'tenant_slug' => $includeTenant ? $invoice->tenant?->slug : null,
                'period' => $invoice->billing_period_start?->format('Y-m-d').' -> '.$invoice->billing_period_end?->format('Y-m-d'),
                'amount_usd' => number_format((float) $invoice->amount_usd, 2),
                'status' => $status->value,
                'issued_at' => $invoice->issued_at?->format('Y-m-d H:i'),
                'due_at' => $invoice->due_at?->format('Y-m-d H:i'),
                'paid_at' => $invoice->paid_at?->format('Y-m-d H:i'),
                'provider' => $latestPayment ? (is_string($latestPayment->provider) ? $latestPayment->provider : $latestPayment->provider->value) : ($invoice->payment_method ?: 'N/A'),
                'notes' => $invoice->notes,
                'payable' => ! $includeTenant
                    && in_array($status, [BillingInvoiceStatus::PENDING, BillingInvoiceStatus::OVERDUE], true)
                    && $invoice->subscription?->plan !== null,
            ];
        });
    }

    /** @param Collection<int, BillingPayment> $payments */
    private function mapPayments(Collection $payments, bool $includeTenant): Collection
    {
        return $payments->map(function (BillingPayment $payment) use ($includeTenant): array {
            return [
                'id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'invoice_number' => $payment->invoice?->invoice_number,
                'tenant_name' => $includeTenant ? ($payment->tenant?->company_display_name ?: $payment->tenant?->name) : null,
                'amount_usd' => number_format((float) $payment->amount_usd, 2),
                'status' => is_string($payment->status) ? $payment->status : $payment->status->value,
                'provider' => is_string($payment->provider) ? $payment->provider : $payment->provider->value,
                'provider_reference' => $payment->provider_reference,
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
                'notes' => $payment->notes,
            ];
        })->values();
    }

    /** @param Collection<int, BillingInvoice> $invoices */
    private function paymentsFromInvoices(Collection $invoices): Collection
    {
        return $invoices
            ->flatMap(fn (BillingInvoice $invoice) => $invoice->payments)
            ->sortByDesc(fn (BillingPayment $payment) => optional($payment->paid_at)->timestamp ?? $payment->id)
            ->values();
    }
}
