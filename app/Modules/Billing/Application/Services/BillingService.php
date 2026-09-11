<?php

namespace App\Modules\Billing\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Billing\Application\Mail\PaymentFailedMail;
use App\Modules\Billing\Domain\Enums\BillingInvoiceStatus;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use App\Modules\Billing\Domain\Enums\BillingPaymentStatus;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Domain\Enums\PlanBillingType;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

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

    /**
     * On-demand renewal: generates the next-period invoice for a subscription
     * the moment the tenant clicks "renew", instead of a background job
     * pre-creating it ahead of time. The invoice is what the checkout uses to
     * know the amount to charge, so it must exist before the gateway session.
     */
    public function createRenewalInvoiceForSubscription(Tenant $tenant, TenantSubscription $subscription): BillingInvoice
    {
        $plan = $subscription->plan;

        if ($plan === null || in_array($plan->billing_type, [PlanBillingType::ONPREM, PlanBillingType::LIFETIME], true)) {
            throw new InvalidArgumentException('This plan cannot be renewed through the billing checkout flow.');
        }

        $hasOpenInvoice = BillingInvoice::query()
            ->withoutGlobalScopes()
            ->where('subscription_id', $subscription->id)
            ->whereIn('status', [BillingInvoiceStatus::PENDING->value, BillingInvoiceStatus::OVERDUE->value])
            ->exists();

        if ($hasOpenInvoice) {
            throw new InvalidArgumentException('A pending invoice already exists for this subscription.');
        }

        $billingStart = ($subscription->ends_at !== null
            ? CarbonImmutable::instance($subscription->ends_at)
            : CarbonImmutable::now())->startOfDay();
        $billingEnd = $plan->periodEndFrom($billingStart);

        return $this->createInvoiceForSubscription($tenant, $subscription, [
            'billing_period_start' => $billingStart->toDateString(),
            'billing_period_end' => $billingEnd->toDateString(),
            'amount_usd' => $plan->price_usd,
            'currency' => 'USD',
            'status' => BillingInvoiceStatus::PENDING->value,
            'issued_at' => now(),
            'due_at' => now(),
            'notes' => 'Factura de renovación generada al iniciar el pago.',
        ]);
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
            ->with(['payments.invoice', 'subscription.plan'])
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

    public function createPendingPayment(BillingInvoice $invoice, BillingPaymentProvider $provider, string $providerSessionId): BillingPayment
    {
        return BillingPayment::query()->create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'amount_usd' => $invoice->amount_usd,
            'currency' => $invoice->currency,
            'status' => BillingPaymentStatus::PENDING->value,
            'provider' => $provider->value,
            'provider_session_id' => $providerSessionId,
        ]);
    }

    public function findPendingPaymentBySessionId(string $providerSessionId): ?BillingPayment
    {
        return BillingPayment::query()
            ->withoutGlobalScopes()
            ->where('provider_session_id', $providerSessionId)
            ->first();
    }

    /**
     * Idempotent: if the payment is already completed (e.g. duplicate webhook
     * delivery), this is a no-op and returns the payment untouched.
     */
    public function completePendingPayment(BillingPayment $payment, ?string $providerReference = null): BillingPayment
    {
        if ($payment->status === BillingPaymentStatus::COMPLETED) {
            return $payment;
        }

        $payment->update([
            'status' => BillingPaymentStatus::COMPLETED->value,
            'provider_reference' => $providerReference,
            'paid_at' => now(),
        ]);

        $invoice = BillingInvoice::query()->withoutGlobalScopes()->with('tenant')->findOrFail($payment->invoice_id);

        $this->markInvoicePaid(
            $invoice,
            $payment->paid_at,
            is_string($payment->provider) ? $payment->provider : $payment->provider->value,
        );

        $this->auditLogService->record(
            actionKey: 'payment.gateway_completed',
            entityType: 'BillingPayment',
            entityId: $payment->id,
            context: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount_usd' => (float) $payment->amount_usd,
                'provider' => is_string($payment->provider) ? $payment->provider : $payment->provider->value,
                'provider_reference' => $providerReference,
            ],
            tenant: $invoice->tenant,
        );

        return BillingPayment::query()->withoutGlobalScopes()->findOrFail($payment->id);
    }

    public function markPendingPaymentFailed(BillingPayment $payment): BillingPayment
    {
        if ($payment->status !== BillingPaymentStatus::PENDING) {
            return $payment;
        }

        $payment->update(['status' => BillingPaymentStatus::FAILED->value]);

        $this->sendPaymentFailedEmail($payment);

        return $payment->refresh();
    }

    private function sendPaymentFailedEmail(BillingPayment $payment): void
    {
        $invoice = BillingInvoice::query()->withoutGlobalScopes()->with('tenant')->find($payment->invoice_id);

        if ($invoice === null || $invoice->tenant === null) {
            return;
        }

        $owner = User::withoutGlobalScopes()
            ->where('tenant_id', $invoice->tenant->id)
            ->where('role', UserRole::OWNER->value)
            ->first();

        if ($owner === null) {
            return;
        }

        Mail::to($owner->email)->send(new PaymentFailedMail($invoice->tenant, $owner, $invoice));
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
