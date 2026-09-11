<?php

namespace App\Modules\Auth\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Application\Mail\PasswordResetMail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class PasswordResetService
{
    private const TOKEN_EXPIRY_MINUTES = 60;

    public function sendResetLink(Tenant $tenant, string $email): void
    {
        $user = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->first();

        if ($user === null) {
            return;
        }

        DB::table('password_reset_tokens')
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password?'.http_build_query([
            'token' => $token,
            'email' => $email,
            'tenant' => $tenant->slug,
        ]));

        Mail::to($user->email)->send(new PasswordResetMail($tenant, $user, $resetUrl));
    }

    public function reset(Tenant $tenant, string $email, string $token, string $newPassword): bool
    {
        $row = DB::table('password_reset_tokens')
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->first();

        if ($row === null) {
            return false;
        }

        if (CarbonImmutable::parse($row->created_at)->addMinutes(self::TOKEN_EXPIRY_MINUTES)->isPast()) {
            return false;
        }

        if (! Hash::check($token, $row->token)) {
            return false;
        }

        $user = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->first();

        if ($user === null) {
            return false;
        }

        $user->forceFill(['password' => Hash::make($newPassword)])->save();

        DB::table('password_reset_tokens')
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->delete();

        return true;
    }
}
