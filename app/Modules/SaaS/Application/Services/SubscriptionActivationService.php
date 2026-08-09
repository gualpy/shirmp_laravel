<?php

namespace App\Modules\SaaS\Application\Services;

use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\DB;

final class SubscriptionActivationService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /**
     * Flips tenant + subscription to active once a gateway payment has been
     * confirmed completed. Idempotent: re-activating an already-active
     * subscription is a harmless no-op.
     */
    public function activateFromPayment(BillingPayment $payment): void
    {
        $invoice = $payment->invoice()->withoutGlobalScopes()->with('tenant', 'subscription')->firstOrFail();
        $subscription = $invoice->subscription;

        if ($subscription === null) {
            return;
        }

        if ($subscription->status === SubscriptionStatus::ACTIVE) {
            return;
        }

        DB::transaction(function () use ($subscription, $invoice): void {
            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE->value,
                'starts_at' => now(),
            ]);

            $tenant = $invoice->tenant;
            $tenant->update(['is_active' => true]);

            $this->auditLogService->record(
                actionKey: 'tenant.activated',
                entityType: 'Tenant',
                entityId: $tenant->id,
                context: [
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice->id,
                ],
                tenant: $tenant,
            );
        });
    }
}
