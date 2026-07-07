@extends('backoffice.layout')

@section('title', 'Superadmin · Tenants')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('admin.tenants_title') }}</h1>
    <div class="section-subtitle">{{ __('admin.tenants_subtitle') }}</div>
    <div style="margin-top:12px;">
        <a class="cta-primary" href="/backoffice/admin/tenants/create">{{ __('admin.new_tenant') }}</a>
    </div>
</div>

<form method="GET" class="card" style="margin-bottom:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
    <label><span class="metric-label">{{ __('admin.plan') }}</span><select name="plan" class="input"><option value="">Todos</option>@foreach($vm['filter_options']['plans'] as $plan)<option value="{{ $plan }}" @selected(($vm['filters']['plan'] ?? '')===$plan)>{{ $plan }}</option>@endforeach</select></label>
    <label><span class="metric-label">{{ __('admin.status') }}</span><select name="status" class="input"><option value="">Todos</option>@foreach($vm['filter_options']['statuses'] as $status)<option value="{{ $status }}" @selected(($vm['filters']['status'] ?? '')===$status)>{{ $status }}</option>@endforeach</select></label>
    <label><span class="metric-label">{{ __('admin.billing') }}</span><select name="billing_type" class="input"><option value="">Todos</option>@foreach($vm['filter_options']['billing_types'] as $type)<option value="{{ $type }}" @selected(($vm['filters']['billing_type'] ?? '')===$type)>{{ $type }}</option>@endforeach</select></label>
    <label><span class="metric-label">{{ __('admin.read_only_col') }}</span><select name="read_only" class="input"><option value="">Todos</option><option value="1" @selected(($vm['filters']['read_only'] ?? '')==='1')>Sí</option><option value="0" @selected(($vm['filters']['read_only'] ?? '')==='0')>No</option></select></label>
    <div style="display:flex;gap:8px;"><button class="logout-btn" type="submit">{{ __('admin.filter') }}</button><a class="logout-btn" href="/backoffice/admin/tenants" style="text-decoration:none;display:inline-flex;align-items:center;">{{ __('admin.reset') }}</a></div>
</form>

<div class="card">
    <div class="table-scroll">
    <table class="data-table">
        <thead><tr><th>{{ __('admin.tenant_col') }}</th><th>{{ __('admin.slug') }}</th><th>{{ __('admin.plan') }}</th><th>{{ __('admin.status') }}</th><th>{{ __('admin.billing') }}</th><th>{{ __('admin.read_only_col') }}</th><th>{{ __('admin.last_verified') }}</th><th>{{ __('admin.ends_at') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($vm['rows'] as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['slug'] }}</td>
                <td>{{ $row['plan'] ?? 'N/A' }}</td>
                <td><span class="status-badge status-{{ $row['status'] ?? 'na' }}">{{ $row['status'] ?? 'none' }}</span></td>
                <td><span class="status-badge status-{{ $row['billing_type'] ?? 'na' }}">{{ $row['billing_type'] ?? 'N/A' }}</span></td>
                <td>@if($row['read_only_mode'])<span class="status-badge status-warning">read-only</span>@else<span class="status-badge status-active">normal</span>@endif</td>
                <td>{{ $row['last_verified_at'] ?? 'N/A' }}</td>
                <td>{{ $row['ends_at'] ?? 'N/A' }}</td>
                <td><a class="panel-link" href="/backoffice/admin/tenants/{{ $row['id'] }}">{{ __('admin.view_detail') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="section-subtitle">{{ __('admin.no_tenants') }}</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
