@extends('backoffice.layout')

@section('title', 'Superadmin · Nuevo Tenant')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Onboarding de Tenant</h1>
        <div class="section-subtitle">Alta rápida de cliente con tenant, usuario inicial, suscripción y estructura opcional.</div>
    </div>
    <a class="cta-secondary" href="/backoffice/admin/tenants">Volver a tenants</a>
</div>

@if($errors->any())
    <div class="card" style="margin-bottom:16px;border-color:#f2c0b7;background:#fff3f1;color:#9a3626;">
        <strong>Revisa el formulario.</strong>
        <ul style="margin:10px 0 0 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/backoffice/admin/tenants" class="card" style="display:grid;gap:18px;">
    @csrf

    <section>
        <h3 class="panel-title">A) Tenant</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
            <label><span class="metric-label">Company name</span><input class="input" name="name" value="{{ old('name') }}"></label>
            <label><span class="metric-label">Slug</span><input class="input" name="slug" value="{{ old('slug') }}"></label>
            <label><span class="metric-label">Branding name</span><input class="input" name="company_display_name" value="{{ old('company_display_name') }}"></label>
            <label><span class="metric-label">Company email</span><input class="input" type="email" name="company_email" value="{{ old('company_email') }}"></label>
            <label><span class="metric-label">Phone</span><input class="input" name="company_phone" value="{{ old('company_phone') }}"></label>
            <label><span class="metric-label">Address</span><input class="input" name="company_address" value="{{ old('company_address') }}"></label>
        </div>
    </section>

    <section>
        <h3 class="panel-title">B) Admin user</h3>
        <div class="section-subtitle" style="margin-bottom:10px;">El rol inicial se crea como <strong>Owner</strong>.</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
            <label><span class="metric-label">Full name</span><input class="input" name="admin_name" value="{{ old('admin_name') }}"></label>
            <label><span class="metric-label">Email</span><input class="input" type="email" name="admin_email" value="{{ old('admin_email') }}"></label>
            <label><span class="metric-label">Password</span><input class="input" type="password" name="admin_password"></label>
        </div>
    </section>

    <section>
        <h3 class="panel-title">C) Subscription</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
            <label>
                <span class="metric-label">Plan</span>
                <select class="input" name="plan_id">
                    <option value="">Selecciona un plan</option>
                    @foreach($vm['plans'] as $plan)
                        <option value="{{ $plan['id'] }}" @selected((string) old('plan_id') === (string) $plan['id'])>{{ $plan['name'] }} · {{ $plan['code'] }} · {{ $plan['billing_type'] }} · ${{ $plan['price_usd'] }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="metric-label">Status</span>
                <select class="input" name="subscription_status">
                    <option value="active" @selected(old('subscription_status', $vm['defaults']['subscription_status']) === 'active')>active</option>
                    <option value="trial" @selected(old('subscription_status', $vm['defaults']['subscription_status']) === 'trial')>trial</option>
                </select>
            </label>
            <label><span class="metric-label">Starts at</span><input class="input" type="datetime-local" name="starts_at" value="{{ old('starts_at', $vm['defaults']['starts_at']) }}"></label>
            <label><span class="metric-label">Ends at</span><input class="input" type="datetime-local" name="ends_at" value="{{ old('ends_at', $vm['defaults']['ends_at']) }}"></label>
        </div>
    </section>

    <section>
        <h3 class="panel-title">D) Initial setup</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;align-items:end;">
            <label>
                <span class="metric-label">Create initial farm</span>
                <select class="input" name="create_initial_farm">
                    <option value="0" @selected(!old('create_initial_farm', false))>No</option>
                    <option value="1" @selected((string) old('create_initial_farm') === '1')>Sí</option>
                </select>
            </label>
            <label><span class="metric-label">Farm name</span><input class="input" name="farm_name" value="{{ old('farm_name') }}"></label>
            <label>
                <span class="metric-label">Create ponds</span>
                <select class="input" name="create_ponds">
                    <option value="0" @selected(!old('create_ponds', false))>No</option>
                    <option value="1" @selected((string) old('create_ponds') === '1')>Sí</option>
                </select>
            </label>
            <label><span class="metric-label">Number of ponds</span><input class="input" type="number" min="0" max="50" name="pond_count" value="{{ old('pond_count', $vm['defaults']['pond_count']) }}"></label>
        </div>
    </section>

    <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/admin/tenants">Cancelar</a>
        <button class="cta-primary" type="submit">Crear tenant</button>
    </div>
</form>
@endsection
