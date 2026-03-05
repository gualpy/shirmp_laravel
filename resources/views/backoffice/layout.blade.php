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
            --shadow: 0 10px 28px rgba(15, 34, 51, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 0 0, #e8f3ff, transparent 44%), var(--bg);
        }
        .shell { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .nav {
            background: linear-gradient(180deg, #0f2433, #142b3d);
            color: #dbe8f4;
            padding: 16px 12px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand { font-weight: 800; letter-spacing: 0.06em; margin-bottom: 16px; font-size: .95rem; }
        .nav a {
            display: block;
            padding: 9px 10px;
            border-radius: 9px;
            color: #dbe8f4;
            text-decoration: none;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: .9rem;
        }
        .nav a.active { background: #21435c; }
        .nav a.disabled { opacity: .45; pointer-events: none; }
        .main { padding: 14px 14px 30px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }
        .chips { display: flex; gap: 8px; flex-wrap: wrap; }
        .chip {
            background: var(--chip);
            border: 1px solid var(--border);
            color: #2d4257;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .8rem;
            font-weight: 700;
        }
        .read-only-badge { background: #ffe7d6; color: #8a3517; border-color: #f3c5aa; }
        .read-only-banner {
            display: none;
            background: linear-gradient(90deg, #fff4e8, #ffe6e6);
            border: 1px solid #f1c2a4;
            color: #7f2c19;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-weight: 600;
            font-size: .9rem;
        }
        .read-only .read-only-banner { display: block; }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 12px;
        }
        .muted { color: var(--muted); }
        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .nav {
                height: auto;
                position: static;
                padding: 10px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
            }
            .brand { grid-column: 1 / -1; margin-bottom: 4px; }
            .main { padding-top: 10px; }
        }
    </style>
    @stack('head')
</head>
<body class="{{ $shell['read_only_mode'] ? 'read-only' : '' }}">
<div class="shell">
    <aside class="nav">
        <div class="brand">GAMBA BACKOFFICE</div>
        @foreach($shell['menu'] as $item)
            @if($item['visible'])
                <a href="{{ $item['href'] }}"
                   class="{{ $activeMenu === $item['key'] ? 'active' : '' }} {{ !$item['enabled'] ? 'disabled' : '' }}"
                   title="{{ !$item['enabled'] ? 'No disponible en tu plan.' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </aside>
    <main class="main">
        <div class="topbar">
            <div class="chips">
                <span class="chip">{{ $shell['tenant_name'] }}</span>
                <span class="chip">{{ $shell['user_name'] }}</span>
                <span class="chip">Plan: {{ $shell['plan_code'] ?? 'N/A' }}</span>
                @if($shell['read_only_mode'])
                    <span class="chip read-only-badge">READ-ONLY</span>
                @endif
            </div>
        </div>

        <div class="read-only-banner">
            Modo solo lectura activo. No se permiten registros ni cambios hasta verificar licencia/conectividad.
        </div>

        @yield('content')
    </main>
</div>
</body>
</html>

