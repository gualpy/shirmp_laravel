<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.title') }}</title>
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>
<body>
    <nav class="nav-bar">
        <div class="container nav-inner">
            <a class="nav-brand" href="{{ route('landing') }}">
                <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                ShrimpApp
            </a>
            <div class="nav-links">
                <a class="nav-link" href="#features">{{ __('landing.nav_features') }}</a>
                <a class="nav-link" href="#pricing">{{ __('landing.nav_pricing') }}</a>
                <a class="nav-link" href="{{ route('app.login') }}">{{ __('landing.nav_login') }}</a>
                <a class="nav-cta" href="{{ route('signup.create') }}">{{ __('landing.nav_cta') }}</a>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <span class="hero-eyebrow">{{ __('landing.hero_eyebrow') }}</span>
            <h1>{{ __('landing.hero_title') }}</h1>
            <p>{{ __('landing.hero_body') }}</p>
            <div class="hero-actions">
                <a class="btn-primary" href="{{ route('signup.create') }}">{{ __('landing.hero_cta_primary') }}</a>
                <a class="btn-ghost" href="{{ route('app.login') }}">{{ __('landing.hero_cta_secondary') }}</a>
            </div>
            <div class="hero-chips">
                <span class="hero-chip">{{ __('landing.hero_chip_1') }}</span>
                <span class="hero-chip">{{ __('landing.hero_chip_2') }}</span>
                <span class="hero-chip">{{ __('landing.hero_chip_3') }}</span>
                <span class="hero-chip">{{ __('landing.hero_chip_4') }}</span>
            </div>
        </div>
    </header>

    <section class="section" id="features">
        <div class="container">
            <div class="section-head">
                <span class="section-eyebrow">{{ __('landing.features_eyebrow') }}</span>
                <h2>{{ __('landing.features_title') }}</h2>
                <p>{{ __('landing.features_body') }}</p>
            </div>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🦐</div>
                    <h3>{{ __('landing.feature_1_title') }}</h3>
                    <p>{{ __('landing.feature_1_body') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌾</div>
                    <h3>{{ __('landing.feature_2_title') }}</h3>
                    <p>{{ __('landing.feature_2_body') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📉</div>
                    <h3>{{ __('landing.feature_3_title') }}</h3>
                    <p>{{ __('landing.feature_3_body') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💧</div>
                    <h3>{{ __('landing.feature_4_title') }}</h3>
                    <p>{{ __('landing.feature_4_body') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💵</div>
                    <h3>{{ __('landing.feature_5_title') }}</h3>
                    <p>{{ __('landing.feature_5_body') }}</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>{{ __('landing.feature_6_title') }}</h3>
                    <p>{{ __('landing.feature_6_body') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <div class="section-head">
                <span class="section-eyebrow">{{ __('landing.steps_eyebrow') }}</span>
                <h2>{{ __('landing.steps_title') }}</h2>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>{{ __('landing.step_1_title') }}</h3>
                    <p>{{ __('landing.step_1_body') }}</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>{{ __('landing.step_2_title') }}</h3>
                    <p>{{ __('landing.step_2_body') }}</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>{{ __('landing.step_3_title') }}</h3>
                    <p>{{ __('landing.step_3_body') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="pricing">
        <div class="container">
            <div class="section-head">
                <span class="section-eyebrow">{{ __('landing.pricing_eyebrow') }}</span>
                <h2>{{ __('landing.pricing_title') }}</h2>
                <p>{{ __('landing.pricing_body') }}</p>
            </div>
            <div class="pricing-grid">
                @foreach($plans as $plan)
                    <div class="pricing-card {{ $plan['code'] === 'pro' ? 'pricing-card--featured' : '' }}">
                        <span class="pricing-plan-name">{{ $plan['name'] }}</span>
                        <div class="pricing-price">
                            @if($plan['is_contact_sales'])
                                {{ __('landing.pricing_contact_sales') }}
                            @else
                                ${{ $plan['price_usd'] }}
                                <span>{{ $plan['billing_type'] === 'yearly' ? __('landing.pricing_year_suffix') : __('landing.pricing_month_suffix') }}</span>
                            @endif
                        </div>
                        <ul class="pricing-features">
                            @foreach($plan['highlights'] as $highlight)
                                <li>{{ $highlight }}</li>
                            @endforeach
                        </ul>
                        @if($plan['is_contact_sales'])
                            <a class="pricing-cta" href="mailto:{{ config('mail.from.address') }}">{{ __('landing.pricing_cta_contact') }}</a>
                        @else
                            <a class="pricing-cta" href="{{ route('signup.create') }}">{{ __('landing.pricing_cta') }}</a>
                        @endif
                    </div>
                @endforeach

                {{-- PREVIEW ONLY: hardcoded, not wired to a real plan --}}
                <div class="pricing-card">
                    <span class="pricing-plan-name">Growth</span>
                    <div class="pricing-price">
                        $119
                        <span>/mes</span>
                    </div>
                    <ul class="pricing-features">
                        <li>Hasta 2 granjas</li>
                        <li>Hasta 8 ciclos activos</li>
                        <li>Hasta 6 usuarios</li>
                        <li>Dashboard operativo</li>
                        <li>Alertas automáticas</li>
                        <li>Motor de costos</li>
                    </ul>
                    <a class="pricing-cta" href="{{ route('signup.create') }}">{{ __('landing.pricing_cta') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-band">
        <div class="container">
            <h2>{{ __('landing.final_cta_title') }}</h2>
            <p>{{ __('landing.final_cta_body') }}</p>
            <a class="btn-primary" href="{{ route('signup.create') }}">{{ __('landing.final_cta_button') }}</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            &copy; {{ date('Y') }} ShrimpApp. {{ __('landing.footer_rights') }}
        </div>
    </footer>
</body>
</html>
