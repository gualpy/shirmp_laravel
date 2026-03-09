<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Backoffice Acuícola')</title>
    <style>
        :root {
            --bg: #f2f6fb;
            --card: #ffffff;
            --text: #0f2233;
            --muted: #5f6f7f;
            --primary: #0c7a6a;
            --accent: #2f8fff;
            --warning: #db8d1b;
            --critical: #c63636;
            --border: #d7e2ed;
            --chip: #eaf1f8;
            --shadow: 0 14px 32px rgba(15, 34, 51, 0.07);
            --shadow-soft: 0 6px 18px rgba(15, 34, 51, 0.05);
            --nav-shadow: 0 10px 28px rgba(15, 34, 51, 0.1);
            --nav-bg: rgba(10, 26, 40, 0.76);
            --nav-solid: #0d2232;
            --surface: rgba(255, 255, 255, 0.72);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 0 0, rgba(232, 243, 255, 0.92), transparent 44%),
                linear-gradient(180deg, #f7fbff 0%, #f1f6fb 38%, #eef4f8 100%);
        }
        .shell { min-height: 100vh; }
        .masthead {
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 10px 18px 0;
        }
        .masthead-inner {
            width: min(1440px, calc(100vw - 24px));
            margin: 0 auto;
            background: var(--nav-bg);
            color: #dbe8f4;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: none;
            transition: background .22s ease, box-shadow .22s ease, border-color .22s ease;
        }
        .masthead.is-scrolled .masthead-inner {
            background: rgba(9, 23, 35, 0.88);
            box-shadow: var(--nav-shadow);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .nav-row {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 18px;
            padding: 10px 16px;
        }
        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 11px;
            background: linear-gradient(145deg, #12a58c, #1e88e5);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.24);
            display: grid;
            place-items: center;
            color: #fff;
            font-weight: 800;
            letter-spacing: .08em;
        }
        .brand-copy {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .brand {
            font-weight: 800;
            letter-spacing: 0.08em;
            font-size: .88rem;
            white-space: nowrap;
            line-height: 1.1;
        }
        .brand-subtitle {
            color: rgba(219, 232, 244, 0.56);
            font-size: .68rem;
            white-space: nowrap;
            margin-top: 2px;
        }
        .nav-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 0;
        }
        .nav-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border-radius: 999px;
            color: #dbe8f4;
            text-decoration: none;
            font-weight: 650;
            font-size: .86rem;
            border: 1px solid transparent;
            transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease, opacity .18s ease;
        }
        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }
        .nav-links a.active {
            background: linear-gradient(135deg, rgba(47, 143, 255, 0.2), rgba(12, 122, 106, 0.26));
            border-color: rgba(123, 210, 255, 0.34);
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 6px 16px rgba(11, 50, 72, 0.18);
        }
        .nav-links a.disabled {
            opacity: .42;
            pointer-events: none;
        }
        .nav-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }
        .chips { display: flex; gap: 8px; flex-wrap: wrap; }
        .logout-btn {
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.92);
            color: #11263b;
            border-radius: 999px;
            padding: 8px 13px;
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(10, 22, 33, 0.16);
        }
        .chip {
            background: var(--chip);
            border: 1px solid var(--border);
            color: #2d4257;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: .76rem;
            font-weight: 700;
        }
        .read-only-badge { background: #ffe7d6; color: #8a3517; border-color: #f3c5aa; }
        .mobile-toggle {
            display: none;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border-radius: 12px;
            width: 42px;
            height: 42px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .mobile-panel {
            display: none;
            padding: 0 18px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .mobile-panel.open { display: block; }
        .mobile-links {
            display: grid;
            gap: 8px;
            margin-top: 14px;
        }
        .mobile-links a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 12px;
            color: #dbe8f4;
            text-decoration: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            font-weight: 600;
        }
        .mobile-links a.active {
            background: linear-gradient(135deg, rgba(47, 143, 255, 0.24), rgba(12, 122, 106, 0.24));
            border-color: rgba(80, 188, 255, 0.35);
        }
        .mobile-links a.disabled {
            opacity: .5;
            pointer-events: none;
        }
        .main {
            width: min(1440px, calc(100vw - 24px));
            margin: 0 auto;
            padding: 22px 0 40px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }
        .read-only-banner {
            display: none;
            background: linear-gradient(90deg, rgba(255, 244, 232, 0.96), rgba(255, 250, 244, 0.92));
            border: 1px solid #f1d2b8;
            color: #7f4f27;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: .88rem;
            box-shadow: var(--shadow-soft);
        }
        .read-only .read-only-banner { display: block; }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 16px;
        }
        .card-soft {
            background: var(--surface);
            backdrop-filter: blur(10px);
        }
        .section-heading {
            margin: 0 0 6px;
            font-size: 1.45rem;
            line-height: 1.08;
            letter-spacing: -0.03em;
        }
        .section-subtitle {
            color: var(--muted);
            font-size: .96rem;
            line-height: 1.45;
        }
        .cta-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(12, 122, 106, 0.08);
            background: linear-gradient(135deg, #0d907d, #0c7a6a);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 12px 26px rgba(12, 122, 106, 0.18);
        }
        .cta-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            box-shadow: var(--shadow-soft);
        }
        .muted { color: var(--muted); }
        @media (max-width: 980px) {
            .masthead { padding: 8px 10px 0; }
            .masthead-inner { width: calc(100vw - 20px); }
            .nav-row {
                grid-template-columns: 1fr auto;
                align-items: center;
            }
            .nav-links,
            .nav-meta { display: none; }
            .mobile-toggle { display: inline-flex; }
            .brand-subtitle { display: none; }
            .main { width: calc(100vw - 20px); padding-top: 18px; }
            .card { padding: 14px; }
        }
    </style>
    @stack('head')
</head>
<body class="{{ $shell['read_only_mode'] ? 'read-only' : '' }}">
<div class="shell">
    <header class="masthead" id="backofficeMasthead">
        <div class="masthead-inner">
            <div class="nav-row">
                <div class="brand-wrap">
                    <div class="brand-mark">GA</div>
                    <div class="brand-copy">
                        <div class="brand">GAMBA BACKOFFICE</div>
                        <div class="brand-subtitle">Control operativo y gerencial acuícola</div>
                    </div>
                </div>

                <nav class="nav-links" aria-label="Navegación principal">
                    @foreach($shell['menu'] as $item)
                        @if($item['visible'])
                            <a href="{{ $item['href'] }}"
                               class="{{ $activeMenu === $item['key'] ? 'active' : '' }} {{ !$item['enabled'] ? 'disabled' : '' }}"
                               title="{{ !$item['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'No disponible.') : '' }}">
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>

                <div class="nav-meta">
                    <div class="chips">
                        <span class="chip">{{ $shell['tenant_name'] }}</span>
                        <span class="chip">{{ $shell['user_name'] }}</span>
                        <span class="chip">Plan: {{ $shell['plan_code'] ?? 'N/A' }}</span>
                        @if($shell['read_only_mode'])
                            <span class="chip read-only-badge">READ-ONLY</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-btn" type="submit">Cerrar sesión</button>
                    </form>
                </div>

                <button
                    class="mobile-toggle"
                    type="button"
                    aria-label="Abrir navegación"
                    aria-expanded="false"
                    aria-controls="mobileNavPanel"
                    data-mobile-nav-toggle
                >
                    ☰
                </button>
            </div>

            <div class="mobile-panel" id="mobileNavPanel">
                <div class="chips" style="margin-top:14px;">
                    <span class="chip">{{ $shell['tenant_name'] }}</span>
                    <span class="chip">{{ $shell['user_name'] }}</span>
                    <span class="chip">Plan: {{ $shell['plan_code'] ?? 'N/A' }}</span>
                    @if($shell['read_only_mode'])
                        <span class="chip read-only-badge">READ-ONLY</span>
                    @endif
                </div>
                <nav class="mobile-links" aria-label="Navegación móvil">
                    @foreach($shell['menu'] as $item)
                        @if($item['visible'])
                            <a href="{{ $item['href'] }}"
                               class="{{ $activeMenu === $item['key'] ? 'active' : '' }} {{ !$item['enabled'] ? 'disabled' : '' }}"
                               title="{{ !$item['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'No disponible.') : '' }}">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ $activeMenu === $item['key'] ? '•' : '›' }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:14px;">
                    @csrf
                    <button class="logout-btn" type="submit" style="width:100%;">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </header>

    <main class="main">

        <div class="read-only-banner">
            Modo solo lectura activo. No se permiten registros ni cambios hasta verificar licencia/conectividad.
        </div>

        @yield('content')
    </main>
</div>
<script>
    (function () {
        const masthead = document.getElementById('backofficeMasthead');
        const toggle = document.querySelector('[data-mobile-nav-toggle]');
        const panel = document.getElementById('mobileNavPanel');

        function syncScrolledState() {
            if (!masthead) {
                return;
            }

            masthead.classList.toggle('is-scrolled', window.scrollY > 8);
        }

        if (toggle && panel) {
            toggle.addEventListener('click', function () {
                const isOpen = panel.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        window.addEventListener('scroll', syncScrolledState, { passive: true });
        syncScrolledState();
    })();
</script>
</body>
</html>
