<!DOCTYPE html>
<html lang="es">
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
                            <span>Control operativo y gerencial acuícola</span>
                        </div>
                    </div>

                    <div class="brand-copy">
                        <span class="eyebrow">Backoffice acuícola</span>
                        <h1>Control total de la producción camaronera.</h1>
                        <p>Métricas, alertas, costos, inventario y proyección de cosecha en una sola plataforma para equipos de campo, gerencia y operación financiera.</p>
                    </div>

                    <div class="brand-points">
                        <div class="brand-point">
                            <strong>Operación con contexto</strong>
                            <span>Da seguimiento al ciclo desde siembra hasta cosecha con indicadores y eventos críticos centralizados.</span>
                        </div>
                        <div class="brand-point">
                            <strong>Decisión más rápida</strong>
                            <span>Convierte datos de agua, alimentación, mortalidad y costos en una vista ejecutiva lista para actuar.</span>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    <span class="brand-chip">Producción</span>
                    <span class="brand-chip">Alertas</span>
                    <span class="brand-chip">Costos</span>
                    <span class="brand-chip">Proyección</span>
                </div>
            </aside>

            <section class="form-panel">
                <div class="form-card">
                    <div class="form-brand">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                        <div>
                            <strong>ShrimpApp</strong>
                            <span>Plataforma de gestión acuícola</span>
                        </div>
                    </div>

                    <div class="form-header">
                        <span class="eyebrow" style="margin-bottom:10px; background:rgba(8,145,178,0.08); border:1px solid rgba(8,145,178,0.12); color:#0f5f78;">Ingreso Backoffice</span><h2>Iniciar sesión</h2>
                        <p>Accede a tu plataforma de gestión acuícola.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert" role="alert">
                            No se pudo iniciar sesión con los datos ingresados.
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
                            <label for="email">Correo electrónico</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@tenant-a.test" autocomplete="email" required>
                        </div>

                        <div class="field">
                            <label for="password">Contraseña</label>
                            <div class="password-wrap">
                                <input id="password" name="password" type="password" placeholder="Tu contraseña" autocomplete="current-password" required>
                                <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Mostrar contraseña">Mostrar</button>
                            </div>
                        </div>

                        <div class="remember-row">
                            <label class="remember" for="remember">
                                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                <span>Recordar sesión en este equipo</span>
                            </label>
                            <span>Acceso para operación y gerencia.</span>
                        </div>

                        <button class="submit-btn" type="submit">Entrar al Backoffice</button>
                    </form>

                    <p class="form-note">Volver a la página principal de la operación <a href="{{ url('/') }}">desde aquí</a>.</p>
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
                toggle.textContent = isPassword ? 'Ocultar' : 'Mostrar';
                toggle.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });
    </script>
</body>
</html>
