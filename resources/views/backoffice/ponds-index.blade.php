@extends('backoffice.layout')

@section('title', 'Backoffice · Ponds')

@section('content')
@php($canManageProduction = ($shell['permissions']['production.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Ponds</h1>
        <div class="section-subtitle">Operational infrastructure ready to open cycles and stock. Filter by farm to work faster.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/farms">Farms</a>
        <a class="cta-secondary" href="/backoffice/stocking/create">New Stocking</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(360px,440px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">New pond</h3>
        <form method="GET" action="/backoffice/ponds" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-bottom:14px;">
            <label style="flex:1 1 220px;">
                <span class="metric-label">Filter by farm</span>
                <select class="input" name="farm">
                    <option value="">All farms</option>
                    @foreach($vm['farm_options'] as $farm)
                        <option value="{{ $farm['id'] }}" @selected((string) $vm['selected_farm_id'] === (string) $farm['id'])>{{ $farm['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <button class="cta-secondary" type="submit">Filter</button>
        </form>

        <form method="POST" action="/backoffice/ponds" style="display:grid;gap:12px;">
            @csrf
            <label>
                <span class="metric-label">Farm</span>
                <select class="input" name="farm_id">
                    <option value="">Select a farm</option>
                    @foreach($vm['farm_options'] as $farm)
                        <option value="{{ $farm['id'] }}" @selected((string) old('farm_id', $vm['selected_farm_id']) === (string) $farm['id'])>{{ $farm['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">Code</span><input class="input" name="code" value="{{ old('code') }}" placeholder="P04"></label>
                <label><span class="metric-label">Name</span><input class="input" name="name" value="{{ old('name') }}" placeholder="East Pond"></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">Area (ha)</span><input class="input" type="number" step="0.01" min="0.01" name="area_ha" value="{{ old('area_ha') }}"></label>
                <label><span class="metric-label">Avg. depth (m)</span><input class="input" type="number" step="0.01" min="0.01" name="avg_depth_m" value="{{ old('avg_depth_m', '1.40') }}"></label>
            </div>
            <label style="display:inline-flex;align-items:center;gap:10px;font-size:.92rem;color:var(--muted);">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Pond is active and available for production
            </label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageProduction ? '' : 'disabled' }} title="{{ $canManageProduction ? 'Create pond' : (($shell['permissions']['production.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Create pond</button>
                <a class="cta-secondary" href="/backoffice/ponds">Reset</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">Ponds registradas</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">There are no ponds for the current filter.</div>
        @else
            <table class="data-table">
                <thead>
                <tr><th>Code</th><th>Farm</th><th>Area</th><th>Depth</th><th>Status</th><th>Operation</th></tr>
                </thead>
                <tbody>
                @foreach($vm['rows'] as $row)
                    <tr>
                        <td><strong>{{ $row['code'] }}</strong><div class="section-subtitle" style="font-size:.82rem;">{{ $row['name'] ?: 'Unnamed' }}</div></td>
                        <td>{{ $row['farm'] }}</td>
                        <td>{{ $row['area_ha'] }} ha</td>
                        <td>{{ $row['avg_depth_m'] ? $row['avg_depth_m'].' m' : 'N/D' }}</td>
                        <td>
                            @if(!$row['is_active'])
                                <span class="status-badge status-na">inactive</span>
                            @elseif($row['has_active_cycle'])
                                <span class="status-badge status-warning">active cycle</span>
                            @else
                                <span class="status-badge status-active">ready for stocking</span>
                            @endif
                        </td>
                        <td>
                            @if($row['active_cycle_id'])
                                <a class="panel-link" href="/backoffice/cycles/{{ $row['active_cycle_id'] }}">View cycle</a>
                            @elseif($row['is_active'])
                                <a class="panel-link" href="/backoffice/stocking/create">Open stocking</a>
                            @else
                                <span class="muted">Not available</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
