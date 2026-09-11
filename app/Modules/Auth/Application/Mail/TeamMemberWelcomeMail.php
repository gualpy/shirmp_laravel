<?php

namespace App\Modules\Auth\Application\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class TeamMemberWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User $user,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('mail.team_member_welcome_subject', ['app' => config('app.name')]))
            ->view('emails.auth.team-member-welcome')
            ->with([
                'tenant' => $this->tenant,
                'user' => $this->user,
                'loginUrl' => route('login', ['tenant' => $this->tenant->slug]),
            ]);
    }
}
