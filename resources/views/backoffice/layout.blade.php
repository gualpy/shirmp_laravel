<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aquaculture Backoffice')</title>
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
                        <div class="brand">{{ __('layout.brand') }}</div>
                        <div class="brand-subtitle">{{ __('layout.brand_subtitle') }}</div>
                    </div>
                </div>

                <nav class="nav-links" aria-label="Primary navigation">
                    @foreach($shell['menu'] as $item)
                        @if($item['visible'])
                            @php($children = collect($item['children'] ?? [])->filter(fn ($child) => $child['visible'] ?? false)->values())
                            @php($parentActive = $activeMenu === $item['key'] || $children->contains(fn ($child) => $activeMenu === $child['key']))
                            @if($children->isNotEmpty())
                                <div class="nav-group {{ $parentActive ? 'is-active' : '' }}">
                                    <button
                                       type="button"
                                       class="nav-group-trigger {{ $parentActive ? 'active' : '' }} {{ !$item['enabled'] ? 'disabled' : '' }}"
                                       title="{{ !$item['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'Not available.') : '' }}"
                                       aria-expanded="{{ $parentActive ? 'true' : 'false' }}"
                                       data-nav-group-trigger>
                                        {{ $item['label'] }}
                                    </button>
                                    <div class="nav-group-menu">
                                        @foreach($children as $child)
                                            <a href="{{ $child['href'] }}"
                                               class="{{ $activeMenu === $child['key'] ? 'active' : '' }} {{ !$child['enabled'] ? 'disabled' : '' }}"
                                               title="{{ !$child['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'Not available.') : '' }}">
                                                {{ $child['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $item['href'] }}"
                                   class="{{ $activeMenu === $item['key'] ? 'active' : '' }} {{ !$item['enabled'] ? 'disabled' : '' }}"
                                   title="{{ !$item['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'Not available.') : '' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>

                <div class="nav-meta">
                    <div class="chips">
                        <span class="chip">{{ $shell['tenant_name'] }}</span>
                        <span class="chip">{{ $shell['user_name'] }}</span>
                        <span class="chip">Plan: {{ $shell['plan_code'] ?? 'N/A' }}</span>
                        @if($shell['read_only_mode'])
                            <span class="chip read-only-badge">{{ __('layout.read_only') }}</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-btn" type="submit">{{ __('layout.sign_out') }}</button>
                    </form>
                </div>

                <button
                    class="mobile-toggle"
                    type="button"
                    aria-label="{{ __('layout.open_navigation') }}"
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
                        <span class="chip read-only-badge">{{ __('layout.read_only') }}</span>
                    @endif
                </div>
                <nav class="mobile-links" aria-label="Mobile navigation">
                    @foreach($shell['menu'] as $item)
                        @if($item['visible'])
                            @php($children = collect($item['children'] ?? [])->filter(fn ($child) => $child['visible'] ?? false)->values())
                            @php($parentActive = $activeMenu === $item['key'] || $children->contains(fn ($child) => $activeMenu === $child['key']))
                            @if($children->isNotEmpty())
                                <div class="mobile-group {{ $parentActive ? 'is-active' : '' }}">
                                    <div class="mobile-group-title">{{ $item['label'] }}</div>
                                    <div class="mobile-group-links">
                                        @foreach($children as $child)
                                            <a href="{{ $child['href'] }}"
                                               class="{{ $activeMenu === $child['key'] ? 'active' : '' }} {{ !$child['enabled'] ? 'disabled' : '' }}"
                                               title="{{ !$child['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'Not available.') : '' }}">
                                                <span>{{ $child['label'] }}</span>
                                                <span>{{ $activeMenu === $child['key'] ? '•' : '›' }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $item['href'] }}"
                                   class="{{ $activeMenu === $item['key'] ? 'active' : '' }} {{ !$item['enabled'] ? 'disabled' : '' }}"
                                   title="{{ !$item['enabled'] ? ($shell['menu_disabled_tooltip'] ?? 'Not available.') : '' }}">
                                    <span>{{ $item['label'] }}</span>
                                    <span>{{ $activeMenu === $item['key'] ? '•' : '›' }}</span>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:14px;">
                    @csrf
                    <button class="logout-btn" type="submit" style="width:100%;">{{ __('layout.sign_out') }}</button>
                </form>
            </div>
        </div>
    </header>

    <main class="main">

        <div class="read-only-banner">
            {{ __('layout.read_only_banner') }}
        </div>

        @yield('content')
    </main>
</div>
<script>
    (function () {
        const masthead = document.getElementById('backofficeMasthead');
        const toggle = document.querySelector('[data-mobile-nav-toggle]');
        const panel = document.getElementById('mobileNavPanel');
        const navGroupTriggers = document.querySelectorAll('[data-nav-group-trigger]');

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

        navGroupTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                const group = trigger.closest('.nav-group');

                if (!group || trigger.classList.contains('disabled')) {
                    return;
                }

                event.preventDefault();
                const willOpen = !group.classList.contains('is-open');

                document.querySelectorAll('.nav-group.is-open').forEach(function (openGroup) {
                    openGroup.classList.remove('is-open');
                    const openTrigger = openGroup.querySelector('[data-nav-group-trigger]');
                    if (openTrigger) {
                        openTrigger.setAttribute('aria-expanded', 'false');
                    }
                });

                if (willOpen) {
                    group.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('.nav-group')) {
                return;
            }

            document.querySelectorAll('.nav-group.is-open').forEach(function (openGroup) {
                openGroup.classList.remove('is-open');
                const openTrigger = openGroup.querySelector('[data-nav-group-trigger]');
                if (openTrigger) {
                    openTrigger.setAttribute('aria-expanded', 'false');
                }
            });
        });

        window.addEventListener('scroll', syncScrolledState, { passive: true });
        syncScrolledState();
    })();
</script>
</body>
</html>
