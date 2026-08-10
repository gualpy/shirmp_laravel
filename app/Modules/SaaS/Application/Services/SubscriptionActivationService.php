<?php

namespace App\Modules\SaaS\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Billing\Domain\Models\BillingPayment;
use App\Modules\SaaS\Application\Mail\TenantWelcomeMail;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class SubscriptionActivationService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /**
     * Flips tenant + subscription to active once a gateway payment has been
     * confirmed completed, and always rolls the subscription's ends_at
     * forward to the paid invoice's period end - both on first activation
     * and on a renewal of an already-active subscription. Resets the
     * renewal reminder flag so the next period gets its own reminder.
     * Idempotent: re-processing an already-active period is a harmless
     * no-op beyond re-writing the same ends_at.
     */
    public function activateFromPayment(BillingPayment $payment): void
    {
        $invoice = $payment->invoice()->withoutGlobalScopes()->with('tenant', 'subscription.plan')->firstOrFail();
        $subscription = $invoice->subscription;

        if ($subscription === null) {
            return;
        }

        $wasActive = $subscription->status === SubscriptionStatus::ACTIVE;

        DB::transaction(function () use ($subscription, $invoice, $wasActive): void {
            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE->value,
                'starts_at' => $wasActive ? $subscription->starts_at : now(),
                'ends_at' => $invoice->billing_period_end,
                'renewal_reminder_sent_at' => null,
            ]);

            if ($wasActive) {
                return;
            }

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

        if (! $wasActive) {
            $this->sendWelcomeEmail($invoice->tenant, $subscription->plan);
        }
    }

    private function sendWelcomeEmail(Tenant $tenant, ?Plan $plan): void
    {
        if ($plan === null) {
            return;
        }

        $owner = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('role', UserRole::OWNER->value)
            ->first();

        if ($owner === null) {
            return;
        }

        Mail::to($owner->email)->send(new TenantWelcomeMail($tenant, $owner, $plan));
    }
}
