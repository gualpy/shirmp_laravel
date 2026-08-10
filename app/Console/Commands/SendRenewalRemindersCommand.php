<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\SaaS\Application\Mail\RenewalReminderMail;
use App\Modules\SaaS\Domain\Enums\SubscriptionStatus;
use App\Modules\SaaS\Domain\Models\TenantSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRenewalRemindersCommand extends Command
{
    protected $signature = 'billing:send-renewal-reminders';

    protected $description = 'Email tenant owners 5 days before their subscription period ends';

    public function handle(): int
    {
        $reminderDate = now()->addDays(5)->toDateString();
        $count = 0;

        TenantSubscription::query()
            ->whereNull('renewal_reminder_sent_at')
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->whereDate('ends_at', $reminderDate)
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

                    Mail::to($owner->email)->send(new RenewalReminderMail($tenant, $owner, $plan, $subscription->ends_at));
                    $subscription->update(['renewal_reminder_sent_at' => now()]);
                    $count++;
                }
            });

        $this->info("Sent {$count} renewal reminders.");

        return self::SUCCESS;
    }
}
