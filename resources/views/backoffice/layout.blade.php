<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Backoffice Acuícola')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice.css') }}">
    @stack('head')
</head>
<body class="{{ $shell['read_only_mode'] ? 'read-only' : '' }}">
<div class="shell">
    <header class="masthead" id="backofficeMasthead">
        <div class="masthead-inner">
            <div class="nav-row">
                <div class="brand-wrap">
                    <div class="brand-mark">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                    </div>
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
