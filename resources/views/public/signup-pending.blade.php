<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('signup.pending_title') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice-login.css') }}">
    @unless($tenant->is_active)
        <meta http-equiv="refresh" content="5">
    @endunless
</head>
<body>
    <main class="login-page">
        <section class="login-shell" aria-label="Estado del pago">
            <section class="form-panel" style="margin: 0 auto;">
                <div class="form-card">
                    @if($tenant->is_active)
                        <div class="form-header">
                            <h2>{{ __('signup.pending_activated_title') }}</h2>
                            <p>{{ __('signup.pending_activated_body') }}</p>
                        </div>
                        <a class="submit-btn" style="display:block;text-align:center;text-decoration:none;" href="{{ route('login', ['tenant' => $tenant->slug]) }}">{{ __('signup.go_to_login_btn') }}</a>
                    @else
                        <div class="form-header">
                            <h2>{{ __('signup.pending_still_pending_title') }}</h2>
                            <p>{{ __('signup.pending_still_pending_body') }}</p>
                        </div>
                        <button class="submit-btn" type="button" onclick="window.location.reload()">{{ __('signup.refresh_btn') }}</button>
                    @endif
                </div>
            </section>
        </section>
    </main>
</body>
</html>
