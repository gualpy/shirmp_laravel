@extends('backoffice.layout')

@section('title', 'Backoffice · Fincas')

@section('content')
@php($canManageProduction = ($shell['permissions']['production.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Fincas</h1>
        <div class="section-subtitle">Base operativa del tenant para organizar piscinas y sembrar sin perder estructura. Diseñada para soporte, gerencia y producción.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <span class="chip">{{ $vm['stats']['farms'] }} fincas</span>
        <span class="chip">{{ $vm['stats']['ponds'] }} piscinas</span>
        <a class="cta-secondary" href="/backoffice/ponds">Ver piscinas</a>
        <a class="cta-secondary" href="/backoffice/stocking/create">Nueva siembra</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(340px,420px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">Nueva finca</h3>
        <form method="POST" action="/backoffice/farms" style="display:grid;gap:12px;">
            @csrf
            <label><span class="metric-label">Nombre</span><input class="input" name="name" value="{{ old('name') }}" placeholder="Finca Camaronera Norte"></label>
            <label><span class="metric-label">Ubicación</span><input class="input" name="location" value="{{ old('location') }}" placeholder="Guayas"></label>
            <label><span class="metric-label">Notas</span><textarea class="input" name="notes" rows="4" placeholder="Observaciones operativas o geográficas">{{ old('notes') }}</textarea></label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageProduction ? '' : 'disabled' }} title="{{ $canManageProduction ? 'Crear finca' : (($shell['permissions']['production.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Crear finca</button>
                <a class="cta-secondary" href="/backoffice/farms">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">Fincas registradas</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">Todavía no hay fincas registradas en este tenant.</div>
        @else
            <table class="data-table">
                <thead>
                <tr><th>Finca</th><th>Ubicación</th><th>Piscinas</th><th>Contexto</th></tr>
                </thead>
                <tbody>
                @foreach($vm['rows'] as $row)
                    <tr>
                        <td><strong>{{ $row['name'] }}</strong></td>
                        <td>{{ $row['location'] ?: 'N/D' }}</td>
                        <td>{{ $row['ponds_count'] }}</td>
                        <td>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <a class="panel-link" href="/backoffice/ponds?farm={{ $row['id'] }}">Ver piscinas</a>
                                <a class="panel-link" href="/backoffice/stocking/create">Sembrar</a>
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
