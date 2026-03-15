@extends('backoffice.layout')

@section('title', 'Superadmin · Tenant '.$vm['tenant']['name'])

@section('content')
@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ $vm['tenant']['name'] }}</h1>
    <div class="section-subtitle">Slug: {{ $vm['tenant']['slug'] }}</div>
    <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/admin/tenants/{{ $vm['tenant']['id'] }}/billing">Ver billing</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;">
    <div class="card">
        <h3 class="panel-title">Información general</h3>
        <p><strong>Nombre:</strong> {{ $vm['tenant']['name'] }}</p>
        <p><strong>Slug:</strong> {{ $vm['tenant']['slug'] }}</p>
        <p><strong>Branding:</strong> {{ $vm['tenant']['branding'] ?: 'N/A' }}</p>
        <p><strong>Logo:</strong> {{ $vm['tenant']['logo_path'] ?: 'N/A' }}</p>
        <p><strong>Contacto:</strong> {{ $vm['tenant']['contact'] ? implode(' · ', $vm['tenant']['contact']) : 'N/A' }}</p>
    </div>

    <div class="card">
        <h3 class="panel-title">Suscripción</h3>
        <p><strong>Plan:</strong> {{ $vm['subscription']['plan'] ?? 'N/A' }} @if(!empty($vm['subscription']['plan_code'])) ({{ $vm['subscription']['plan_code'] }}) @endif</p>
        <p><strong>Status:</strong> <span class="status-badge status-{{ $vm['subscription']['status'] ?? 'na' }}">{{ $vm['subscription']['status'] ?? 'none' }}</span></p>
        <p><strong>Billing:</strong> {{ $vm['subscription']['billing_type'] ?? 'N/A' }}</p>
        <p><strong>Starts at:</strong> {{ $vm['subscription']['starts_at'] ?? 'N/A' }}</p>
        <p><strong>Ends at:</strong> {{ $vm['subscription']['ends_at'] ?? 'N/A' }}</p>
        <p><strong>Offline grace:</strong> {{ $vm['subscription']['offline_grace_days'] ?? 'N/A' }}</p>
        <p><strong>Last verified:</strong> {{ $vm['subscription']['last_verified_at'] ?? 'N/A' }}</p>
        <p><strong>Verification source:</strong> {{ $vm['subscription']['verification_source'] ?? 'N/A' }}</p>
        <p><strong>License key:</strong> {{ $vm['subscription']['license_key_masked'] ?? 'N/A' }}</p>
        <p><strong>Read-only:</strong> @if($vm['subscription']['read_only_mode'])<span class="status-badge status-warning">Sí</span>@else<span class="status-badge status-active">No</span>@endif</p>
    </div>

    <div class="card">
        <h3 class="panel-title">Onboarding actual</h3>
        <p><strong>Usuario admin:</strong> {{ $vm['admin_user']['name'] ?? 'N/A' }}</p>
        <p><strong>Email admin:</strong> {{ $vm['admin_user']['email'] ?? 'N/A' }}</p>
        <p><strong>Rol:</strong> {{ $vm['admin_user']['role'] ?? 'N/A' }}</p>
        <p><strong>Farm inicial:</strong> {{ $vm['structure']['farm_name'] ?? 'N/A' }}</p>
        <p><strong>Ponds creados:</strong> {{ $vm['structure']['pond_count'] }}</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;margin-top:14px;">
    <div class="card">
        <div class="panel-head"><h3 class="panel-title">Features habilitadas</h3></div>
        @if($vm['features']===[])
            <div class="section-subtitle">No hay features configuradas.</div>
        @else
            <ul>
                @foreach($vm['features'] as $feature)
                    <li><strong>{{ $feature['feature_key'] }}</strong>: {{ $feature['is_enabled'] ? 'enabled' : 'disabled' }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="card">
        <div class="panel-head"><h3 class="panel-title">Límites del plan</h3></div>
        @if($vm['limits']===[])
            <div class="section-subtitle">No hay límites configurados.</div>
        @else
            <ul>
                @foreach($vm['limits'] as $limit)
                    <li><strong>{{ $limit['key'] }}</strong>: {{ $limit['value'] ?? 'unlimited' }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
