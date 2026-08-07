<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('signup.title') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice-login.css') }}">
</head>
<body>
    <main class="login-page">
        <section class="login-shell" aria-label="Crear cuenta en ShrimpApp">
            <aside class="brand-panel">
                <div class="brand-top">
                    <div class="brand-logo">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ShrimpApp">
                        <div class="brand-logo-text">
                            <strong>ShrimpApp</strong>
                        </div>
                    </div>

                    <div class="brand-copy">
                        <span class="eyebrow">{{ __('signup.eyebrow') }}</span>
                        <h1>{{ __('signup.tagline') }}</h1>
                        <p>{{ __('signup.description') }}</p>
                    </div>
                </div>
            </aside>

            <section class="form-panel">
                <div class="form-card">
                    <div class="form-header">
                        <h2>{{ __('signup.section_company') }}</h2>
                    </div>

                    @if ($errors->any())
                        <div class="alert" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('signup.store') }}">
                        @csrf

                        <div class="field">
                            <label for="name">{{ __('signup.company_name') }}</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                        </div>

                        <div class="field">
                            <label for="slug">{{ __('signup.slug') }}</label>
                            <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="mi-camaronera" required>
                            <span class="form-note">{{ __('signup.slug_hint') }}</span>
                        </div>

                        <div class="form-header">
                            <h2>{{ __('signup.section_admin') }}</h2>
                        </div>

                        <div class="field">
                            <label for="admin_name">{{ __('signup.admin_name') }}</label>
                            <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name') }}" required>
                        </div>

                        <div class="field">
                            <label for="admin_email">{{ __('signup.admin_email') }}</label>
                            <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email') }}" required>
                        </div>

                        <div class="field">
                            <label for="admin_password">{{ __('signup.admin_password') }}</label>
                            <input id="admin_password" name="admin_password" type="password" required>
                        </div>

                        <div class="field">
                            <label for="admin_password_confirmation">{{ __('signup.admin_password_confirmation') }}</label>
                            <input id="admin_password_confirmation" name="admin_password_confirmation" type="password" required>
                        </div>

                        <div class="form-header">
                            <h2>{{ __('signup.section_plan') }}</h2>
                        </div>

                        <div class="field">
                            <label for="plan_id">{{ __('signup.select_plan') }}</label>
                            <select id="plan_id" name="plan_id" required>
                                <option value="">{{ __('signup.select_plan') }}</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan['id'] }}" @selected((string) old('plan_id') === (string) $plan['id'])>
                                        {{ $plan['name'] }} · {{ $plan['billing_type'] }} · ${{ $plan['price_usd'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-header">
                            <h2>{{ __('signup.section_payment') }}</h2>
                        </div>

                        <div class="field">
                            <label for="payment_provider">{{ __('signup.payment_provider') }}</label>
                            <select id="payment_provider" name="payment_provider" required>
                                <option value="stripe" @selected(old('payment_provider', 'stripe') === 'stripe')>Stripe</option>
                                <option value="paypal" @selected(old('payment_provider') === 'paypal')>PayPal</option>
                            </select>
                        </div>

                        <button class="submit-btn" type="submit">{{ __('signup.submit') }}</button>
                    </form>

                    <p class="form-note">{{ __('signup.already_have_account') }} <a href="{{ route('login') }}">{{ __('signup.go_to_login') }}</a></p>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
