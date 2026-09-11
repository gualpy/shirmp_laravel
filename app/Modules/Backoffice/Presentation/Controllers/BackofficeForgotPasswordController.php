<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Auth\Application\Services\PasswordResetService;
use App\Modules\Backoffice\Presentation\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class BackofficeForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('backoffice.auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request, PasswordResetService $service): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('slug', strtolower(trim((string) $request->string('tenant'))))
            ->where('is_active', true)
            ->first();

        if ($tenant !== null) {
            $service->sendResetLink($tenant, (string) $request->string('email'));
        }

        return back()->with('status', __('login.reset_link_sent'));
    }
}
