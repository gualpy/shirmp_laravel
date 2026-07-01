<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ShrimpApp</title>
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
                            <span>Operational and management control for aquaculture</span>
                        </div>
                    </div>

                    <div class="brand-copy">
                        <span class="eyebrow">Aquaculture backoffice</span>
                        <h1>Total control of shrimp production.</h1>
                        <p>Metrics, alerts, costs, inventory and harvest projection in one platform for field teams, management and finance operations.</p>
                    </div>

                    <div class="brand-points">
                        <div class="brand-point">
                            <strong>Operations with context</strong>
                            <span>Track each cycle from stocking to harvest with centralized indicators and critical events.</span>
                        </div>
                        <div class="brand-point">
                            <strong>Faster decisions</strong>
                            <span>Turn water, feeding, mortality and cost data into an executive view ready for action.</span>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    <span class="brand-chip">Production</span>
                    <span class="brand-chip">Alertas</span>
                    <span class="brand-chip">Costs</span>
                    <span class="brand-chip">Projection</span>
                </div>
            </aside>

            <section class="form-panel">
                <div class="form-card">
                    <div class="form-brand">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                        <div>
                            <strong>ShrimpApp</strong>
                            <span>Aquaculture management platform</span>
                        </div>
                    </div>

                    <div class="form-header">
                        <span class="eyebrow" style="margin-bottom:10px; background:rgba(8,145,178,0.08); border:1px solid rgba(8,145,178,0.12); color:#0f5f78;">Ingreso Backoffice</span><h2>Sign in</h2>
                        <p>Access your aquaculture management platform.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert" role="alert">
                            Unable to sign in with the provided credentials.
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
                            <label for="tenant">Tenant</label>
                            <input id="tenant" name="tenant" type="text" value="{{ old('tenant', $defaultTenant) }}" placeholder="tenant-a" autocomplete="organization" required>
                        </div>

                        <div class="field">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@tenant-a.test" autocomplete="email" required>
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <div class="password-wrap">
                                <input id="password" name="password" type="password" placeholder="Your password" autocomplete="current-password" required>
                                <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Show password">Mostrar</button>
                            </div>
                        </div>

                        <div class="remember-row">
                            <label class="remember" for="remember">
                                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                <span>Remember this device</span>
                            </label>
                            <span>Access for operations and management.</span>
                        </div>

                        <button class="submit-btn" type="submit">Enter Backoffice</button>
                    </form>

                    <p class="form-note">Return to the main operations page <a href="{{ url('/') }}">here</a>.</p>
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
                toggle.textContent = isPassword ? 'Hide' : 'Show';
                toggle.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Show password');
            });
        });
    </script>
</body>
</html>
