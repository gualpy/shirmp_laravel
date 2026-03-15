<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ShrimpApp</title>
    <style>
        :root {
            color-scheme: light;
            --bg-deep: #06273a;
            --bg-mid: #0f4a68;
            --bg-soft: #0d6a7e;
            --accent: #12b5cb;
            --accent-strong: #0891b2;
            --surface: rgba(255, 255, 255, 0.92);
            --surface-soft: rgba(255, 255, 255, 0.12);
            --border: rgba(7, 37, 58, 0.12);
            --text: #123047;
            --muted: #5b7388;
            --danger-bg: #fff1f2;
            --danger-border: #fecdd3;
            --danger-text: #9f1239;
            --shadow: 0 36px 90px rgba(4, 21, 33, 0.24);
            --radius-xl: 30px;
            --radius-lg: 20px;
            --radius-md: 14px;
            --transition-fast: 220ms ease-out;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(18, 181, 203, 0.22), transparent 34%),
                radial-gradient(circle at 85% 15%, rgba(77, 208, 225, 0.18), transparent 22%),
                linear-gradient(135deg, #031a29 0%, #0a3850 48%, #0f6b80 100%);
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: auto;
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            width: 78vw;
            height: 78vw;
            left: -18vw;
            bottom: -36vw;
            border-radius: 44% 56% 58% 42% / 43% 44% 56% 57%;
            background: radial-gradient(circle at center, rgba(21, 132, 154, 0.26), rgba(3, 26, 41, 0));
            opacity: 0.9;
        }

        body::after {
            width: 62vw;
            height: 32vw;
            right: -8vw;
            top: 8vh;
            border-radius: 58% 42% 60% 40% / 56% 53% 47% 44%;
            background: radial-gradient(circle at center, rgba(103, 232, 249, 0.16), rgba(103, 232, 249, 0));
        }

        .login-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .login-shell {
            width: min(1140px, 100%);
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            border-radius: var(--radius-xl);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .brand-panel {
            position: relative;
            padding: 44px;
            background:
                linear-gradient(180deg, rgba(8, 33, 50, 0.72), rgba(6, 28, 45, 0.88)),
                linear-gradient(135deg, rgba(12, 55, 78, 0.94), rgba(10, 103, 122, 0.9));
            color: #ecfeff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            isolation: isolate;
        }

        .brand-panel::before,
        .brand-panel::after {
            content: "";
            position: absolute;
            inset: auto;
            pointer-events: none;
            z-index: -1;
        }

        .brand-panel::before {
            width: 380px;
            height: 380px;
            right: -130px;
            top: -90px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(107, 230, 246, 0.24), rgba(107, 230, 246, 0));
        }

        .brand-panel::after {
            width: 520px;
            height: 220px;
            left: -120px;
            bottom: -120px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(17, 157, 180, 0.24), rgba(17, 157, 180, 0));
        }

        .brand-top {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 18px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.18);
        }

        .brand-logo-text {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .brand-logo-text strong {
            font-size: 1.35rem;
            letter-spacing: 0.01em;
        }

        .brand-logo-text span {
            font-size: 0.86rem;
            color: rgba(236, 254, 255, 0.74);
        }

        .eyebrow {
            display: inline-flex;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(236, 254, 255, 0.82);
        }

        .brand-copy h1 {
            margin: 0 0 16px;
            font-size: clamp(2.3rem, 4vw, 3.55rem);
            line-height: 1.02;
            letter-spacing: -0.04em;
            max-width: 10ch;
        }

        .brand-copy p {
            margin: 0;
            max-width: 540px;
            font-size: 1.02rem;
            line-height: 1.75;
            color: rgba(236, 254, 255, 0.8);
        }

        .brand-points {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .brand-point {
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .brand-point strong {
            display: block;
            margin-bottom: 6px;
            font-size: 0.98rem;
        }

        .brand-point span {
            display: block;
            font-size: 0.9rem;
            line-height: 1.5;
            color: rgba(236, 254, 255, 0.74);
        }

        .brand-footer {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .brand-chip {
            display: inline-flex;
            align-items: center;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.88rem;
            color: rgba(236, 254, 255, 0.78);
        }

        .form-panel {
            background: linear-gradient(180deg, rgba(250, 253, 255, 0.98), rgba(240, 248, 251, 0.96));
            padding: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-card {
            width: min(420px, 100%);
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-brand img {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border-radius: 14px;
            padding: 7px;
            background: linear-gradient(180deg, #ecfeff, #d9f7fb);
            border: 1px solid rgba(8, 145, 178, 0.12);
        }

        .form-brand span {
            display: block;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .form-header h2 {
            margin: 0 0 8px;
            font-size: 2rem;
            letter-spacing: -0.03em;
            color: #08243a;
        }

        .form-header p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }

        .alert {
            padding: 14px 16px;
            border-radius: var(--radius-md);
            border: 1px solid var(--danger-border);
            background: var(--danger-bg);
            color: var(--danger-text);
            font-size: 0.93rem;
            line-height: 1.55;
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #16344a;
        }

        .field input {
            width: 100%;
            border: 1px solid rgba(14, 54, 78, 0.14);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.94);
            padding: 14px 16px;
            font: inherit;
            color: #0d2c41;
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast), transform var(--transition-fast);
        }

        .field input::placeholder {
            color: #8aa0b3;
        }

        .field input:focus {
            outline: none;
            border-color: rgba(8, 145, 178, 0.68);
            box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.12);
            transform: translateY(-1px);
        }

        .password-wrap {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--accent-strong);
            font: inherit;
            font-size: 0.86rem;
            font-weight: 600;
            cursor: pointer;
            padding: 6px 8px;
            border-radius: 10px;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            background: rgba(8, 145, 178, 0.08);
            outline: none;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent-strong);
        }

        .submit-btn {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 15px 18px;
            font: inherit;
            font-weight: 700;
            color: #ecfeff;
            background: linear-gradient(135deg, #08728f 0%, #0aa0bc 100%);
            box-shadow: 0 18px 34px rgba(8, 145, 178, 0.26);
            cursor: pointer;
            transition: transform var(--transition-fast), box-shadow var(--transition-fast), filter var(--transition-fast);
        }

        .submit-btn:hover,
        .submit-btn:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 20px 36px rgba(8, 145, 178, 0.3);
            filter: saturate(1.03);
            outline: none;
        }

        .form-note {
            margin: 0;
            font-size: 0.88rem;
            color: var(--muted);
        }

        .form-note a {
            color: var(--accent-strong);
            text-decoration: none;
            font-weight: 600;
        }

        .form-note a:hover {
            text-decoration: underline;
        }

        @media (max-width: 980px) {
            .login-page {
                padding: 22px;
            }

            .login-shell {
                grid-template-columns: 1fr;
            }

            .brand-panel,
            .form-panel {
                padding: 32px;
            }

            .brand-copy h1 {
                max-width: none;
            }
        }

        @media (max-width: 640px) {
            .login-page {
                padding: 14px;
            }

            .brand-panel {
                gap: 24px;
            }

            .brand-points {
                grid-template-columns: 1fr;
            }

            .brand-panel,
            .form-panel {
                padding: 24px 20px;
            }

            .form-header h2 {
                font-size: 1.72rem;
            }

            .remember-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
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
