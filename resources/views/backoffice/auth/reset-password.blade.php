<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('login.reset_password_title') }} | ShrimpApp</title>
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice-login.css') }}">
</head>
<body>
    <main class="login-page">
        <section class="login-shell" aria-label="Restablecer contraseña">
            <aside class="brand-panel">
                <div class="brand-top">
                    <div class="brand-logo">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                        <div class="brand-logo-text">
                            <strong>ShrimpApp</strong>
                            <span>{{ __('login.brand_subtitle') }}</span>
                        </div>
                    </div>

                    <div class="brand-copy">
                        <span class="eyebrow">{{ __('login.aquaculture_backoffice') }}</span>
                        <h1>{{ __('login.tagline') }}</h1>
                        <p>{{ __('login.description') }}</p>
                    </div>
                </div>
            </aside>

            <section class="form-panel">
                <div class="form-card">
                    <div class="form-brand">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                        <div>
                            <strong>ShrimpApp</strong>
                            <span>{{ __('login.management_platform') }}</span>
                        </div>
                    </div>

                    <div class="form-header">
                        <h2>{{ __('login.reset_password_title') }}</h2>
                        <p>{{ __('login.reset_password_subtitle') }}</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ old('token', $token) }}">

                        <div class="field">
                            <label for="tenant">{{ __('login.tenant_label') }}</label>
                            <input id="tenant" name="tenant" type="text" value="{{ old('tenant', $tenant) }}" placeholder="tenant-a" autocomplete="organization" required>
                        </div>

                        <div class="field">
                            <label for="email">{{ __('login.email_label') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="admin@tenant-a.test" autocomplete="email" required>
                        </div>

                        <div class="field">
                            <label for="password">{{ __('login.new_password_label') }}</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8">
                        </div>

                        <div class="field">
                            <label for="password_confirmation">{{ __('login.confirm_password_label') }}</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8">
                        </div>

                        <button class="submit-btn" type="submit">{{ __('login.reset_password_submit') }}</button>
                    </form>

                    <p class="form-note"><a href="{{ route('login') }}">{{ __('login.back_to_login') }}</a></p>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
