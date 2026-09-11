<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('login.forgot_password_title') }} | ShrimpApp</title>
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice-login.css') }}">
</head>
<body>
    <main class="login-page">
        <section class="login-shell" aria-label="Recuperar contraseña">
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
                        <h2>{{ __('login.forgot_password_title') }}</h2>
                        <p>{{ __('login.forgot_password_subtitle') }}</p>
                    </div>

                    @if (session('status'))
                        <div class="alert" style="border-color:#b9e2cf;background:#eefaf4;color:#176448;" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="field">
                            <label for="tenant">{{ __('login.tenant_label') }}</label>
                            <input id="tenant" name="tenant" type="text" value="{{ old('tenant') }}" placeholder="tenant-a" autocomplete="organization" required>
                        </div>

                        <div class="field">
                            <label for="email">{{ __('login.email_label') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@tenant-a.test" autocomplete="email" required>
                        </div>

                        <button class="submit-btn" type="submit">{{ __('login.send_reset_link') }}</button>
                    </form>

                    <p class="form-note"><a href="{{ route('login') }}">{{ __('login.back_to_login') }}</a></p>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
