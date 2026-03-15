@extends('backoffice.layout')

@section('title', 'Backoffice · Inventory')

@section('content')
@php($canManageInventory = ($shell['permissions']['inventory.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Inventario</h1>
        <div class="section-subtitle">Vista operativa y administrativa de stock por bodega. La base de costo unitario queda lista para integración más profunda con CostEngine.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/inventory/export.xlsx">Exportar Excel</a>
        <a class="cta-secondary" href="/backoffice/warehouses">Bodegas</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(340px,420px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">Nuevo item</h3>
        <form method="POST" action="/backoffice/inventory" style="display:grid;gap:12px;">
            @csrf
            <label>
                <span class="metric-label">Bodega</span>
                <select class="input" name="warehouse_id">
                    <option value="">Selecciona una bodega</option>
                    @foreach($vm['warehouse_options'] as $warehouse)
                        <option value="{{ $warehouse['id'] }}" @selected((string) old('warehouse_id') === (string) $warehouse['id'])>{{ $warehouse['name'] }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="metric-label">Nombre</span><input class="input" name="name" value="{{ old('name') }}"></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label>
                    <span class="metric-label">Categoría</span>
                    <select class="input" name="category">
                        @foreach($vm['category_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected(old('category') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="metric-label">Unidad</span>
                    <select class="input" name="unit">
                        @foreach($vm['unit_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected(old('unit') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                <label><span class="metric-label">Stock actual</span><input class="input" type="number" step="0.01" min="0" name="current_stock" value="{{ old('current_stock', '0') }}"></label>
                <label><span class="metric-label">Stock mínimo</span><input class="input" type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock') }}"></label>
                <label><span class="metric-label">Costo unitario</span><input class="input" type="number" step="0.01" min="0" name="cost_per_unit" value="{{ old('cost_per_unit') }}"></label>
            </div>
            <label><span class="metric-label">Notas</span><textarea class="input" name="notes" rows="3">{{ old('notes') }}</textarea></label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageInventory ? '' : 'disabled' }} title="{{ $canManageInventory ? 'Crear item' : (($shell['permissions']['inventory.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Crear item</button>
                <a class="cta-secondary" href="/backoffice/inventory">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">Items</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">No hay items registrados todavía.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr><th>Nombre</th><th>Categoría</th><th>Bodega</th><th>Stock</th><th>Mínimo</th><th>Costo unitario</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @foreach($vm['rows'] as $row)
                        <tr>
                            <td><a class="panel-link" href="/backoffice/inventory/{{ $row['id'] }}">{{ $row['name'] }}</a></td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['warehouse'] }}</td>
                            <td>{{ $row['current_stock'] }} {{ $row['unit'] }}</td>
                            <td>{{ $row['min_stock'] ? $row['min_stock'].' '.$row['unit'] : 'N/A' }}</td>
                            <td>{{ $row['cost_per_unit'] ? '$'.$row['cost_per_unit'] : 'N/A' }}</td>
                            <td>
                                @if($row['low_stock'])
                                    <span class="status-badge status-warning">low stock</span>
                                @else
                                    <span class="status-badge status-active">ok</span>
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
