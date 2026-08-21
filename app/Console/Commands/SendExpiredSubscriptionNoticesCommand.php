<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\SaaS\Application\Mail\SubscriptionExpiredMail;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpiredSubscriptionNoticesCommand extends Command
{
    protected $signature = 'billing:send-expired-notices';

    protected $description = 'Email tenant owners once their subscription period has ended and the service was suspended';

    public function handle(): int
    {
        $count = 0;

        TenantSubscription::query()
            ->whereNull('expired_notification_sent_at')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->whereNotIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
            ->with(['tenant', 'plan'])
            ->chunkById(100, function ($subscriptions) use (&$count): void {
                foreach ($subscriptions as $subscription) {
                    $tenant = $subscription->tenant;
                    $plan = $subscription->plan;

                    if ($tenant === null || $plan === null) {
                        continue;
                    }

                    $owner = User::withoutGlobalScopes()
                        ->where('tenant_id', $tenant->id)
                        ->where('role', UserRole::OWNER->value)
                        ->first();

                    if ($owner === null) {
                        continue;
                    }

                    Mail::to($owner->email)->send(new SubscriptionExpiredMail($tenant, $owner, $plan, $subscription->ends_at));
                    $subscription->update(['expired_notification_sent_at' => now()]);
                    $count++;
                }
            });

        $this->info("Sent {$count} expired subscription notices.");

        return self::SUCCESS;
    }
}
