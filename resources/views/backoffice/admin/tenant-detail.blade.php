@extends('backoffice.layout')

@section('title', 'Superadmin · Tenant '.$vm['tenant']['name'])

@section('content')
@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ $vm['tenant']['name'] }}</h1>
    <div class="section-subtitle">Slug: {{ $vm['tenant']['slug'] }}</div>
    <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/admin/tenants/{{ $vm['tenant']['id'] }}/billing">{{ __('admin.back_to_billing') }}</a>
    </div>
</div>

<div class="animate-enter-down animate-enter-down-delay-1" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;">
    <div class="card">
        <h3 class="panel-title">{{ __('admin.general_info') }}</h3>
        <p><strong>Nombre:</strong> {{ $vm['tenant']['name'] }}</p>
        <p><strong>Slug:</strong> {{ $vm['tenant']['slug'] }}</p>
        <p><strong>Branding:</strong> {{ $vm['tenant']['branding'] ?: 'N/A' }}</p>
        <p><strong>Logo:</strong> {{ $vm['tenant']['logo_path'] ?: 'N/A' }}</p>
        <p><strong>Contacto:</strong> {{ $vm['tenant']['contact'] ? implode(' · ', $vm['tenant']['contact']) : 'N/A' }}</p>
    </div>

    <div class="card">
        <h3 class="panel-title">{{ __('admin.subscription') }}</h3>
        <p><strong>Plan:</strong> {{ $vm['subscription']['plan'] ?? 'N/A' }} @if(!empty($vm['subscription']['plan_code'])) ({{ $vm['subscription']['plan_code'] }}) @endif</p>
        <p><strong>{{ __('admin.status') }}:</strong> <span class="status-badge status-{{ $vm['subscription']['status'] ?? 'na' }}">{{ $vm['subscription']['status'] ?? 'none' }}</span></p>
        <p><strong>{{ __('admin.billing') }}:</strong> {{ $vm['subscription']['billing_type'] ?? 'N/A' }}</p>
        <p><strong>Inicia el:</strong> {{ $vm['subscription']['starts_at'] ?? 'N/A' }}</p>
        <p><strong>Vence el:</strong> {{ $vm['subscription']['ends_at'] ?? 'N/A' }}</p>
        <p><strong>Gracia offline:</strong> {{ $vm['subscription']['offline_grace_days'] ?? 'N/A' }}</p>
        <p><strong>Última verificación:</strong> {{ $vm['subscription']['last_verified_at'] ?? 'N/A' }}</p>
        <p><strong>Fuente de verificación:</strong> {{ $vm['subscription']['verification_source'] ?? 'N/A' }}</p>
        <p><strong>Clave de licencia:</strong> {{ $vm['subscription']['license_key_masked'] ?? 'N/A' }}</p>
        <p><strong>{{ __('admin.read_only') }}:</strong> @if($vm['subscription']['read_only_mode'])<span class="status-badge status-warning">Sí</span>@else<span class="status-badge status-active">No</span>@endif</p>
    </div>

    <div class="card">
        <h3 class="panel-title">{{ __('admin.onboarding') }}</h3>
        <p><strong>Usuario admin:</strong> {{ $vm['admin_user']['name'] ?? 'N/A' }}</p>
        <p><strong>Email admin:</strong> {{ $vm['admin_user']['email'] ?? 'N/A' }}</p>
        <p><strong>Rol:</strong> {{ $vm['admin_user']['role'] ?? 'N/A' }}</p>
        <p><strong>Farm inicial:</strong> {{ $vm['structure']['farm_name'] ?? 'N/A' }}</p>
        <p><strong>Ponds creados:</strong> {{ $vm['structure']['pond_count'] }}</p>
    </div>
</div>

<div class="animate-enter-down animate-enter-down-delay-2" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;margin-top:14px;">
    <div class="card">
        <div class="panel-head"><h3 class="panel-title">{{ __('admin.enabled_features') }}</h3></div>
        @if($vm['features']===[])
            <div class="section-subtitle">{{ __('admin.no_features') }}</div>
        @else
            <ul>
                @foreach($vm['features'] as $feature)
                    <li><strong>{{ $feature['feature_key'] }}</strong>: {{ $feature['is_enabled'] ? __('admin.enabled') : __('admin.disabled_val') }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="card">
        <div class="panel-head"><h3 class="panel-title">{{ __('admin.plan_limits') }}</h3></div>
        @if($vm['limits']===[])
            <div class="section-subtitle">{{ __('admin.no_limits') }}</div>
        @else
            <ul>
                @foreach($vm['limits'] as $limit)
                    <li><strong>{{ $limit['key'] }}</strong>: {{ $limit['value'] ?? __('admin.unlimited') }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
