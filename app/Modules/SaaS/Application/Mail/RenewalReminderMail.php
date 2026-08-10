<?php

namespace App\Modules\SaaS\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\SaaS\Domain\Models\Plan;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class RenewalReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $owner,
        public readonly Plan $plan,
        public readonly CarbonInterface $endsAt,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.renewal_reminder_subject', ['app' => config('app.name')]))
            ->view('emails.saas.renewal-reminder')
            ->with([
                'tenant' => $this->tenant,
                'owner' => $this->owner,
                'plan' => $this->plan,
                'endsAt' => $this->endsAt,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
            ]);
    }
}
