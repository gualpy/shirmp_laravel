<?php

namespace App\Modules\Alerts\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class CriticalAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $owner,
        public readonly AlertEvent $alert,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.critical_alert_subject', ['app' => config('app.name'), 'title' => $this->alert->title]))
            ->view('emails.alerts.critical-alert')
            ->with([
                'tenant' => $this->tenant,
                'owner' => $this->owner,
                'alert' => $this->alert,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
            ]);
    }
}
