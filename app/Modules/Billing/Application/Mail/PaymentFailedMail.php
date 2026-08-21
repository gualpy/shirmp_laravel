<?php

namespace App\Modules\Billing\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Billing\Domain\Models\BillingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class PaymentFailedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $owner,
        public readonly BillingInvoice $invoice,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.payment_failed_subject', ['app' => config('app.name')]))
            ->view('emails.billing.payment-failed')
            ->with([
                'tenant' => $this->tenant,
                'owner' => $this->owner,
                'invoice' => $this->invoice,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
            ]);
    }
}
