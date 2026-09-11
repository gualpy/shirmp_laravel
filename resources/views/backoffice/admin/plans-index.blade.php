@extends('backoffice.layout')

@section('title', 'Superadmin · Planes')

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('admin.plans_title') }}</h1>
    <div class="section-subtitle">{{ __('admin.plans_subtitle') }}</div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;gap:14px;">
@foreach($vm['rows'] as $row)
    <div class="card">
        <div class="panel-head">
            <div>
                <h3 class="panel-title" style="margin-bottom:4px;">{{ $row['name'] }}</h3>
                <div class="section-subtitle">{{ $row['code'] }} · {{ $row['billing_type'] }} · ${{ number_format((float) $row['price_usd'], 2) }}</div>
            </div>
            <span class="status-badge status-{{ $row['is_active'] ? 'active' : 'expired' }}">{{ $row['is_active'] ? __('app.active') : __('app.inactive') }}</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
                <strong>{{ __('admin.limits') }}</strong>
                <ul>
                    @foreach($row['limits'] as $limit)
                        <li>{{ $limit }}</li>
                    @endforeach
                </ul>
            </div>
            <div>
                <strong>{{ __('admin.features') }}</strong>
                <ul>
                    @foreach($row['features'] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <form method="POST" action="{{ route('backoffice.admin.plans.update', $row['id']) }}" style="display:grid;grid-template-columns:1fr 160px auto auto;gap:10px;align-items:end;">
            @csrf
            <label>
                <span class="metric-label">{{ __('admin.plan_name') }}</span>
                <input class="input" name="name" value="{{ $row['name'] }}">
            </label>
            <label>
                <span class="metric-label">{{ __('admin.plan_price') }}</span>
                <input class="input" type="number" min="0" step="0.01" name="price_usd" value="{{ $row['price_usd'] }}">
            </label>
            <label style="display:flex;align-items:center;gap:8px;white-space:nowrap;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked($row['is_active'])>
                <span class="metric-label" style="margin:0;">{{ __('admin.plan_is_active') }}</span>
            </label>
            <button class="cta-primary" type="submit">{{ __('admin.plan_save') }}</button>
        </form>
    </div>
@endforeach
</div>
@endsection
