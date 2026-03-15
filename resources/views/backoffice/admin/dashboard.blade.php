@extends('backoffice.layout')

@section('title', 'Superadmin · Dashboard')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">Superadmin SaaS</h1>
    <div class="section-subtitle">Vista operativa global para soporte, suscripciones y salud comercial del sistema.</div>
    <div style="margin-top:14px;">
        <a href="{{ route('backoffice.admin.ops') }}" style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#0c7a6a;color:#fff;text-decoration:none;font-weight:700;">Ver Ops</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
    <div class="card"><div class="metric-label">Total Tenants</div><div class="metric-value">{{ $vm['total_tenants'] }}</div></div>
    <div class="card"><div class="metric-label">Active</div><div class="metric-value">{{ $vm['active_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">Trial</div><div class="metric-value">{{ $vm['trial_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">Suspended</div><div class="metric-value">{{ $vm['suspended_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">Expired</div><div class="metric-value">{{ $vm['expired_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">OnPrem</div><div class="metric-value">{{ $vm['onprem_tenants'] }}</div></div>
    <div class="card"><div class="metric-label">Read-only</div><div class="metric-value">{{ $vm['read_only_tenants'] }}</div></div>
</div>
@endsection
