@extends('backoffice.layout')

@section('title', 'Backoffice · Warehouses')

@section('content')
@php($canManageInventory = ($shell['permissions']['inventory.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Bodegas</h1>
        <div class="section-subtitle">Controla ubicaciones de almacenamiento para alimento, químicos, combustibles y repuestos.</div>
    </div>
    <a class="cta-secondary" href="/backoffice/inventory">Ver inventario</a>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(320px,380px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card">
        <h3 class="panel-title">Nueva bodega</h3>
        <form method="POST" action="/backoffice/warehouses" style="display:grid;gap:12px;">
            @csrf
            <label><span class="metric-label">Nombre</span><input class="input" name="name" value="{{ old('name') }}"></label>
            <label><span class="metric-label">Ubicación</span><input class="input" name="location" value="{{ old('location') }}"></label>
            <label><span class="metric-label">Notas</span><textarea class="input" name="notes" rows="4">{{ old('notes') }}</textarea></label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageInventory ? '' : 'disabled' }} title="{{ $canManageInventory ? 'Crear bodega' : (($shell['permissions']['inventory.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Crear bodega</button>
                <a class="cta-secondary" href="/backoffice/warehouses">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="panel-title">Listado</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">Aún no hay bodegas registradas.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr><th>Nombre</th><th>Ubicación</th><th>Items</th><th>Notas</th></tr>
                </thead>
                <tbody>
                    @foreach($vm['rows'] as $row)
                        <tr>
                            <td><strong>{{ $row['name'] }}</strong></td>
                            <td>{{ $row['location'] ?? 'N/A' }}</td>
                            <td>{{ $row['items_count'] }}</td>
                            <td>{{ $row['notes'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
