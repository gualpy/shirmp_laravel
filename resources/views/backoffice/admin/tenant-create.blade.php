@extends('backoffice.layout')

@section('title', 'Superadmin · Nuevo Tenant')

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">{{ __('admin.create_tenant_title') }}</h1>
        <div class="section-subtitle">{{ __('admin.create_tenant_subtitle') }}</div>
    </div>
    <a class="cta-secondary" href="/backoffice/admin/tenants">{{ __('admin.back_to_tenants') }}</a>
</div>

@if($errors->any())
    <div class="card" style="margin-bottom:16px;border-color:#f2c0b7;background:#fff3f1;color:#9a3626;">
        <strong>{{ __('admin.form_errors') }}</strong>
        <ul style="margin:10px 0 0 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/backoffice/admin/tenants" class="card animate-enter-down animate-enter-down-delay-1" style="display:grid;gap:18px;">
    @csrf

    <section>
        <h3 class="panel-title">{{ __('admin.section_tenant') }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
            <label><span class="metric-label">{{ __('admin.company_name') }}</span><input class="input" name="name" value="{{ old('name') }}"></label>
            <label><span class="metric-label">Slug</span><input class="input" name="slug" value="{{ old('slug') }}"></label>
            <label><span class="metric-label">{{ __('admin.branding_name') }}</span><input class="input" name="company_display_name" value="{{ old('company_display_name') }}"></label>
            <label><span class="metric-label">{{ __('admin.company_email') }}</span><input class="input" type="email" name="company_email" value="{{ old('company_email') }}"></label>
            <label><span class="metric-label">{{ __('admin.phone') }}</span><input class="input" name="company_phone" value="{{ old('company_phone') }}"></label>
            <label><span class="metric-label">{{ __('admin.address') }}</span><input class="input" name="company_address" value="{{ old('company_address') }}"></label>
        </div>
    </section>

    <section>
        <h3 class="panel-title">{{ __('admin.section_user') }}</h3>
        <div class="section-subtitle" style="margin-bottom:10px;">{!! __('admin.owner_note') !!}</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
            <label><span class="metric-label">{{ __('admin.full_name') }}</span><input class="input" name="admin_name" value="{{ old('admin_name') }}"></label>
            <label><span class="metric-label">Email</span><input class="input" type="email" name="admin_email" value="{{ old('admin_email') }}"></label>
            <label><span class="metric-label">{{ __('admin.password') }}</span><input class="input" type="password" name="admin_password"></label>
        </div>
    </section>

    <section>
        <h3 class="panel-title">{{ __('admin.section_subscription') }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
            <label>
                <span class="metric-label">{{ __('admin.plan') }}</span>
                <select class="input" name="plan_id">
                    <option value="">{{ __('admin.select_plan') }}</option>
                    @foreach($vm['plans'] as $plan)
                        <option value="{{ $plan['id'] }}" @selected((string) old('plan_id') === (string) $plan['id'])>{{ $plan['name'] }} · {{ $plan['code'] }} · {{ $plan['billing_type'] }} · ${{ $plan['price_usd'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="metric-label">{{ __('admin.status') }}</span>
                <select class="input" name="subscription_status">
                    <option value="active" @selected(old('subscription_status', $vm['defaults']['subscription_status']) === 'active')>active</option>
                    <option value="trial" @selected(old('subscription_status', $vm['defaults']['subscription_status']) === 'trial')>trial</option>
                </select>
            </label>
            <label><span class="metric-label">{{ __('admin.starts_at') }}</span><input class="input" type="datetime-local" name="starts_at" value="{{ old('starts_at', $vm['defaults']['starts_at']) }}"></label>
            <label><span class="metric-label">{{ __('admin.ends_at') }}</span><input class="input" type="datetime-local" name="ends_at" value="{{ old('ends_at', $vm['defaults']['ends_at']) }}"></label>
        </div>
    </section>

    <section>
        <h3 class="panel-title">{{ __('admin.section_initial') }}</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;align-items:end;">
            <label>
                <span class="metric-label">{{ __('admin.create_initial_farm') }}</span>
                <select class="input" name="create_initial_farm">
                    <option value="0" @selected(!old('create_initial_farm', false))>No</option>
                    <option value="1" @selected((string) old('create_initial_farm') === '1')>Sí</option>
                </select>
            </label>
            <label><span class="metric-label">{{ __('admin.farm_name') }}</span><input class="input" name="farm_name" value="{{ old('farm_name') }}"></label>
            <label>
                <span class="metric-label">{{ __('admin.create_ponds') }}</span>
                <select class="input" name="create_ponds">
                    <option value="0" @selected(!old('create_ponds', false))>No</option>
                    <option value="1" @selected((string) old('create_ponds') === '1')>Sí</option>
                </select>
            </label>
            <label><span class="metric-label">{{ __('admin.pond_count') }}</span><input class="input" type="number" min="0" max="50" name="pond_count" value="{{ old('pond_count', $vm['defaults']['pond_count']) }}"></label>
        </div>
    </section>

    <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/admin/tenants">{{ __('app.cancel') }}</a>
        <button class="cta-primary" type="submit">{{ __('admin.create_btn') }}</button>
    </div>
</form>
@endsection
