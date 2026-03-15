<?php

namespace App\Modules\Billing\Application\Services;

use App\Models\Tenant;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Enums\BillingPaymentStatus;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

final class BillingService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /** @param array<string, mixed> $payload */
    public function createInvoiceForSubscription(Tenant $tenant, ?TenantSubscription $subscription, array $payload): BillingInvoice
    {
        $invoice = BillingInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'invoice_number' => $this->nextInvoiceNumber(),
            'billing_period_start' => $payload['billing_period_start'],
            'billing_period_end' => $payload['billing_period_end'],
            'amount_usd' => $payload['amount_usd'],
            'currency' => $payload['currency'] ?? 'USD',
            'status' => $payload['status'] ?? BillingInvoiceStatus::PENDING->value,
            'issued_at' => $payload['issued_at'] ?? now(),
            'due_at' => $payload['due_at'] ?? null,
            'paid_at' => $payload['paid_at'] ?? null,
            'payment_method' => $payload['payment_method'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ]);

        $this->auditLogService->record(
            actionKey: 'invoice.created',
            entityType: 'BillingInvoice',
            entityId: $invoice->id,
            context: [
                'invoice_number' => $invoice->invoice_number,
                'amount_usd' => (float) $invoice->amount_usd,
                'subscription_id' => $subscription?->id,
            ],
            tenant: $tenant,
        );

        return $invoice;
    }

    public function markInvoicePaid(BillingInvoice $invoice, ?CarbonInterface $paidAt = null, ?string $paymentMethod = null, ?string $notes = null): BillingInvoice
    {
        $invoice->update([
            'status' => BillingInvoiceStatus::PAID,
            'paid_at' => $paidAt ?? now(),
            'payment_method' => $paymentMethod ?? $invoice->payment_method,
            'notes' => $notes ?? $invoice->notes,
        ]);

        $this->auditLogService->record(
            actionKey: 'invoice.marked_paid',
            entityType: 'BillingInvoice',
            entityId: $invoice->id,
            context: [
                'invoice_number' => $invoice->invoice_number,
                'payment_method' => $invoice->payment_method,
            ],
            tenant: $invoice->tenant,
        );

        return BillingInvoice::query()->withoutGlobalScopes()->with('tenant')->findOrFail($invoice->id);
    }

    public function markInvoiceOverdue(BillingInvoice $invoice): BillingInvoice
    {
        $invoice->update(['status' => BillingInvoiceStatus::OVERDUE]);

        return BillingInvoice::query()->withoutGlobalScopes()->findOrFail($invoice->id);
    }

    /** @return Collection<int, BillingInvoice> */
    public function listInvoicesForTenant(Tenant $tenant): Collection
    {
        return BillingInvoice::query()
            ->withoutGlobalScopes()
            ->with(['payments.invoice'])
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('issued_at')
            ->get();
    }

    /** @return Collection<int, BillingInvoice> */
    public function listInvoicesGlobal(): Collection
    {
        return BillingInvoice::query()
            ->withoutGlobalScopes()
            ->with(['tenant:id,name,slug,company_display_name', 'payments.tenant:id,name,company_display_name', 'payments.invoice'])
            ->orderByDesc('issued_at')
            ->get();
    }

    /** @param array<string, mixed> $payload */
    public function registerManualPayment(BillingInvoice $invoice, array $payload): BillingPayment
    {
        $payment = BillingPayment::query()->create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'amount_usd' => $payload['amount_usd'],
            'currency' => $payload['currency'] ?? 'USD',
            'status' => $payload['status'] ?? BillingPaymentStatus::COMPLETED->value,
            'provider' => $payload['provider'] ?? BillingPaymentProvider::MANUAL->value,
            'provider_reference' => $payload['provider_reference'] ?? null,
            'paid_at' => $payload['paid_at'] ?? now(),
            'notes' => $payload['notes'] ?? null,
        ]);

        if ($payment->status === BillingPaymentStatus::COMPLETED) {
            $this->markInvoicePaid(
                $invoice,
                $payment->paid_at,
                is_string($payment->provider) ? $payment->provider : $payment->provider->value,
                $payment->notes,
            );
        }

        $this->auditLogService->record(
            actionKey: 'payment.manual_registered',
            entityType: 'BillingPayment',
            entityId: $payment->id,
            context: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount_usd' => (float) $payment->amount_usd,
                'provider' => is_string($payment->provider) ? $payment->provider : $payment->provider->value,
            ],
            tenant: $invoice->tenant,
        );

        return BillingPayment::query()->withoutGlobalScopes()->findOrFail($payment->id);
    }

    private function nextInvoiceNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $sequence = BillingInvoice::query()
            ->withoutGlobalScopes()
            ->where('invoice_number', 'like', 'INV-'.$datePrefix.'-%')
            ->count() + 1;

        return sprintf('INV-%s-%04d', $datePrefix, $sequence);
    }
}
