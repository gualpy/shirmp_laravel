<?php

namespace App\Modules\SaaS\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\SaaS\Domain\Models\Plan;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class SubscriptionExpiredMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $owner,
        public readonly Plan $plan,
        public readonly CarbonInterface $endedAt,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.subscription_expired_subject', ['app' => config('app.name')]))
            ->view('emails.saas.subscription-expired')
            ->with([
                'tenant' => $this->tenant,
                'owner' => $this->owner,
                'plan' => $this->plan,
                'endedAt' => $this->endedAt,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
            ]);
    }
}
