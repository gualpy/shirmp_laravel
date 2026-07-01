@extends('backoffice.layout')

@section('title', 'Backoffice · Farms')

@section('content')
@php($canManageProduction = ($shell['permissions']['production.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Farms</h1>
        <div class="section-subtitle">Tenant operating base to organize ponds and stocking without losing structure. Designed for support, management and production.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <span class="chip">{{ $vm['stats']['farms'] }} farms</span>
        <span class="chip">{{ $vm['stats']['ponds'] }} ponds</span>
        <a class="cta-secondary" href="/backoffice/ponds">Ver ponds</a>
        <a class="cta-secondary" href="/backoffice/stocking/create">New Stocking</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(340px,420px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">New farm</h3>
        <form method="POST" action="/backoffice/farms" style="display:grid;gap:12px;">
            @csrf
            <label><span class="metric-label">Name</span><input class="input" name="name" value="{{ old('name') }}" placeholder="Farm Camaronera Norte"></label>
            <label><span class="metric-label">Location</span><input class="input" name="location" value="{{ old('location') }}" placeholder="Guayas"></label>
            <label><span class="metric-label">Notes</span><textarea class="input" name="notes" rows="4" placeholder="Operational or geographic notes">{{ old('notes') }}</textarea></label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageProduction ? '' : 'disabled' }} title="{{ $canManageProduction ? 'Create farm' : (($shell['permissions']['production.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Create farm</button>
                <a class="cta-secondary" href="/backoffice/farms">Reset</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">Farms registradas</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">Todavía no hay farms registradas en este tenant.</div>
        @else
            <table class="data-table">
                <thead>
                <tr><th>Farm</th><th>Location</th><th>Ponds</th><th>Context</th></tr>
                </thead>
                <tbody>
                @foreach($vm['rows'] as $row)
                    <tr>
                        <td><strong>{{ $row['name'] }}</strong></td>
                        <td>{{ $row['location'] ?: 'N/D' }}</td>
                        <td>{{ $row['ponds_count'] }}</td>
                        <td>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <a class="panel-link" href="/backoffice/ponds?farm={{ $row['id'] }}">Ver ponds</a>
                                <a class="panel-link" href="/backoffice/stocking/create">Stock</a>
                            </div>
                            @if($row['notes'])
                                <div class="section-subtitle" style="font-size:.84rem;margin-top:6px;">{{ $row['notes'] }}</div>
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
