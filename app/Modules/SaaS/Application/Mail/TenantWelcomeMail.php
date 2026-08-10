<?php

namespace App\Modules\SaaS\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\SaaS\Domain\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class TenantWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $owner,
        public readonly Plan $plan,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.welcome_subject', ['app' => config('app.name')]))
            ->view('emails.saas.tenant-welcome')
            ->with([
                'tenant' => $this->tenant,
                'owner' => $this->owner,
                'plan' => $this->plan,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
            ]);
    }
}
