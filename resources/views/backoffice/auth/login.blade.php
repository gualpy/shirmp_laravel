<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('login.title') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice-login.css') }}">
</head>
<body>
    <main class="login-page">
        <section class="login-shell" aria-label="Acceso a ShrimpApp">
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

                    <div class="brand-points">
                        <div class="brand-point">
                            <strong>{{ __('login.point_1_title') }}</strong>
                            <span>{{ __('login.point_1_body') }}</span>
                        </div>
                        <div class="brand-point">
                            <strong>{{ __('login.point_2_title') }}</strong>
                            <span>{{ __('login.point_2_body') }}</span>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    <span class="brand-chip">{{ __('login.chip_production') }}</span>
                    <span class="brand-chip">{{ __('login.chip_alerts') }}</span>
                    <span class="brand-chip">{{ __('login.chip_costs') }}</span>
                    <span class="brand-chip">{{ __('login.chip_projection') }}</span>
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
                        <span class="eyebrow" style="margin-bottom:10px; background:rgba(8,145,178,0.08); border:1px solid rgba(8,145,178,0.12); color:#0f5f78;">{{ __('login.eyebrow_backoffice') }}</span><h2>{{ __('login.sign_in') }}</h2>
                        <p>{{ __('login.access_platform') }}</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert" role="alert">
                            {{ __('login.sign_in_error') }}
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('backoffice.login.store') }}">
                        @csrf

                        <div class="field">
                            <label for="tenant">{{ __('login.tenant_label') }}</label>
                            <input id="tenant" name="tenant" type="text" value="{{ old('tenant', $defaultTenant) }}" placeholder="tenant-a" autocomplete="organization" required>
                        </div>

                        <div class="field">
                            <label for="email">{{ __('login.email_label') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@tenant-a.test" autocomplete="email" required>
                        </div>

                        <div class="field">
                            <label for="password">{{ __('login.password_label') }}</label>
                            <div class="password-wrap">
                                <input id="password" name="password" type="password" placeholder="{{ __('login.password_placeholder') }}" autocomplete="current-password" required>
                                <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="{{ __('login.show_password') }}">{{ __('login.show_password') }}</button>
                            </div>
                        </div>

                        <div class="remember-row">
                            <label class="remember" for="remember">
                                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                <span>{{ __('login.remember_device') }}</span>
                            </label>
                            <a href="{{ route('password.request') }}">{{ __('login.forgot_password_link') }}</a>
                        </div>

                        <button class="submit-btn" type="submit">{{ __('login.enter_backoffice') }}</button>
                    </form>

                    <p class="form-note">{{ __('login.back_to_main') }} <a href="{{ url('/') }}">aquí</a>.</p>
                    <p class="form-note">{{ __('login.no_account') }} <a href="{{ route('signup.create') }}">{{ __('login.create_account') }}</a></p>
                </div>
            </section>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('[data-password-toggle]');
            var input = document.getElementById('password');

            if (!toggle || !input) {
                return;
            }

            toggle.addEventListener('click', function () {
                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                toggle.textContent = isPassword ? '{{ __("login.hide_password") }}' : '{{ __("login.show_password") }}';
                toggle.setAttribute('aria-label', isPassword ? '{{ __("login.hide_password") }}' : '{{ __("login.show_password") }}');
            });
        });
    </script>
</body>
</html>
