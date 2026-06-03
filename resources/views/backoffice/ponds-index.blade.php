@extends('backoffice.layout')

@section('title', 'Backoffice · Piscinas')

@section('content')
@php($canManageProduction = ($shell['permissions']['production.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Piscinas</h1>
        <div class="section-subtitle">Infraestructura operativa lista para abrir ciclos y sembrar. Filtra por finca para trabajar más rápido.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/farms">Fincas</a>
        <a class="cta-secondary" href="/backoffice/stocking/create">Nueva siembra</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(360px,440px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">Nueva piscina</h3>
        <form method="GET" action="/backoffice/ponds" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-bottom:14px;">
            <label style="flex:1 1 220px;">
                <span class="metric-label">Filtrar por finca</span>
                <select class="input" name="farm">
                    <option value="">Todas las fincas</option>
                    @foreach($vm['farm_options'] as $farm)
                        <option value="{{ $farm['id'] }}" @selected((string) $vm['selected_farm_id'] === (string) $farm['id'])>{{ $farm['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <button class="cta-secondary" type="submit">Filtrar</button>
        </form>

        <form method="POST" action="/backoffice/ponds" style="display:grid;gap:12px;">
            @csrf
            <label>
                <span class="metric-label">Finca</span>
                <select class="input" name="farm_id">
                    <option value="">Selecciona una finca</option>
                    @foreach($vm['farm_options'] as $farm)
                        <option value="{{ $farm['id'] }}" @selected((string) old('farm_id', $vm['selected_farm_id']) === (string) $farm['id'])>{{ $farm['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">Código</span><input class="input" name="code" value="{{ old('code') }}" placeholder="P04"></label>
                <label><span class="metric-label">Nombre</span><input class="input" name="name" value="{{ old('name') }}" placeholder="Piscina Este"></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">Área (ha)</span><input class="input" type="number" step="0.01" min="0.01" name="area_ha" value="{{ old('area_ha') }}"></label>
                <label><span class="metric-label">Prof. prom. (m)</span><input class="input" type="number" step="0.01" min="0.01" name="avg_depth_m" value="{{ old('avg_depth_m', '1.40') }}"></label>
            </div>
            <label style="display:inline-flex;align-items:center;gap:10px;font-size:.92rem;color:var(--muted);">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Piscina activa y disponible para producción
            </label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageProduction ? '' : 'disabled' }} title="{{ $canManageProduction ? 'Crear piscina' : (($shell['permissions']['production.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Crear piscina</button>
                <a class="cta-secondary" href="/backoffice/ponds">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">Piscinas registradas</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">No hay piscinas para el filtro actual.</div>
        @else
            <table class="data-table">
                <thead>
                <tr><th>Código</th><th>Finca</th><th>Área</th><th>Prof.</th><th>Estado</th><th>Operación</th></tr>
                </thead>
                <tbody>
                @foreach($vm['rows'] as $row)
                    <tr>
                        <td><strong>{{ $row['code'] }}</strong><div class="section-subtitle" style="font-size:.82rem;">{{ $row['name'] ?: 'Sin nombre' }}</div></td>
                        <td>{{ $row['farm'] }}</td>
                        <td>{{ $row['area_ha'] }} ha</td>
                        <td>{{ $row['avg_depth_m'] ? $row['avg_depth_m'].' m' : 'N/D' }}</td>
                        <td>
                            @if(!$row['is_active'])
                                <span class="status-badge status-na">inactiva</span>
                            @elseif($row['has_active_cycle'])
                                <span class="status-badge status-warning">ciclo activo</span>
                            @else
                                <span class="status-badge status-active">lista para siembra</span>
                            @endif
                        </td>
                        <td>
                            @if($row['active_cycle_id'])
                                <a class="panel-link" href="/backoffice/cycles/{{ $row['active_cycle_id'] }}">Ver ciclo</a>
                            @elseif($row['is_active'])
                                <a class="panel-link" href="/backoffice/stocking/create">Abrir siembra</a>
                            @else
                                <span class="muted">No disponible</span>
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
