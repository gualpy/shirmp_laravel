@extends('backoffice.layout')

@section('title', 'Superadmin · Dashboard')

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('admin.dashboard_title') }}</h1>
    <div class="section-subtitle">{{ __('admin.dashboard_subtitle') }}</div>
    <div style="margin-top:14px;">
        <a class="cta-secondary" href="{{ route('backoffice.admin.ops') }}">{{ __('admin.view_ops') }}</a>
    </div>
</div>

<div class="animate-enter-down animate-enter-down-delay-1" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
    <div class="card"><div class="metric-label">{{ __('admin.total_tenants') }}</div><div class="metric-value">{{ $vm['total_tenants'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.active') }}</div><div class="metric-value">{{ $vm['active_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.trial') }}</div><div class="metric-value">{{ $vm['trial_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.suspended') }}</div><div class="metric-value">{{ $vm['suspended_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.expired') }}</div><div class="metric-value">{{ $vm['expired_subscriptions'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.onprem') }}</div><div class="metric-value">{{ $vm['onprem_tenants'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.read_only') }}</div><div class="metric-value">{{ $vm['read_only_tenants'] }}</div></div>
</div>
@endsection
