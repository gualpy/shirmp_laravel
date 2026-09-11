<?php

namespace App\Modules\Auth\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class PasswordResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $user,
        public readonly string $resetUrl,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.password_reset_subject', ['app' => config('app.name')]))
            ->view('emails.auth.password-reset')
            ->with([
                'tenant' => $this->tenant,
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
