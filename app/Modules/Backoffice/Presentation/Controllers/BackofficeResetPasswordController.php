<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Auth\Application\Services\PasswordResetService;
use App\Modules\Backoffice\Presentation\Requests\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BackofficeResetPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('backoffice.auth.reset-password', [
            'token' => (string) $request->query('token', ''),
            'email' => (string) $request->query('email', ''),
            'tenant' => (string) $request->query('tenant', ''),
        ]);
    }

    public function store(ResetPasswordRequest $request, PasswordResetService $service): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('slug', strtolower(trim((string) $request->string('tenant'))))
            ->where('is_active', true)
            ->first();

        if ($tenant === null) {
            return back()
                ->withErrors(['tenant' => __('login.reset_token_invalid')])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $reset = $service->reset(
            $tenant,
            (string) $request->string('email'),
            (string) $request->string('token'),
            (string) $request->string('password'),
        );

        if (! $reset) {
            return back()
                ->withErrors(['token' => __('login.reset_token_invalid')])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        return redirect()->route('login')->with('status', __('login.reset_success'));
    }
}
