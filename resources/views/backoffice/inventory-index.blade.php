@extends('backoffice.layout')

@section('title', 'Backoffice · Inventario')

@section('content')
@php($canManageInventory = ($shell['permissions']['inventory.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">{{ __('inventory.title') }}</h1>
        <div class="section-subtitle">{{ __('inventory.subtitle') }}</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/inventory/export.xlsx">{{ __('inventory.export_excel') }}</a>
        <a class="cta-secondary" href="/backoffice/warehouses">{{ __('inventory.warehouses_link') }}</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

@if($vm['filter_warehouse'])
    <div class="card" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <span>{{ __('inventory.filtered_by_warehouse') }} <strong>{{ $vm['filter_warehouse'] }}</strong></span>
        <a class="cta-secondary" href="/backoffice/inventory">{{ __('inventory.clear_filter') }}</a>
    </div>
@endif

<div style="display:grid;grid-template-columns:minmax(340px,420px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">{{ __('inventory.new_item') }}</h3>
        <form method="POST" action="/backoffice/inventory" style="display:grid;gap:12px;">
            @csrf
            <label>
                <span class="metric-label">{{ __('inventory.warehouse') }}</span>
                <select class="input{{ $errors->has('warehouse_id') ? ' input--error' : '' }}" name="warehouse_id">
                    <option value="">{{ __('inventory.select_warehouse') }}</option>
                    @foreach($vm['warehouse_options'] as $warehouse)
                        <option value="{{ $warehouse['id'] }}" @selected((string) old('warehouse_id') === (string) $warehouse['id'])>{{ $warehouse['name'] }}</option>
                    @endforeach
                </select>
                @error('warehouse_id') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label><span class="metric-label">{{ __('inventory.name') }}</span><input class="input{{ $errors->has('name') ? ' input--error' : '' }}" name="name" value="{{ old('name') }}">@error('name') <span class="field-error">{{ $message }}</span> @enderror</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label>
                    <span class="metric-label">{{ __('inventory.category') }}</span>
                    <select class="input{{ $errors->has('category') ? ' input--error' : '' }}" name="category">
                        @foreach($vm['category_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected(old('category') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('category') <span class="field-error">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="metric-label">{{ __('inventory.unit') }}</span>
                    <select class="input{{ $errors->has('unit') ? ' input--error' : '' }}" name="unit">
                        @foreach($vm['unit_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected(old('unit') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('unit') <span class="field-error">{{ $message }}</span> @enderror
                </label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                <label><span class="metric-label">{{ __('inventory.current_stock') }}</span><input class="input{{ $errors->has('current_stock') ? ' input--error' : '' }}" type="number" step="0.01" min="0" name="current_stock" value="{{ old('current_stock', '0') }}">@error('current_stock') <span class="field-error">{{ $message }}</span> @enderror</label>
                <label><span class="metric-label">{{ __('inventory.min_stock') }}</span><input class="input{{ $errors->has('min_stock') ? ' input--error' : '' }}" type="number" step="0.01" min="0" name="min_stock" value="{{ old('min_stock') }}">@error('min_stock') <span class="field-error">{{ $message }}</span> @enderror</label>
                <label><span class="metric-label">{{ __('inventory.cost_per_unit') }}</span><input class="input{{ $errors->has('cost_per_unit') ? ' input--error' : '' }}" type="number" step="0.01" min="0" name="cost_per_unit" value="{{ old('cost_per_unit') }}">@error('cost_per_unit') <span class="field-error">{{ $message }}</span> @enderror</label>
            </div>
            <label><span class="metric-label">{{ __('inventory.notes') }}</span><textarea class="input{{ $errors->has('notes') ? ' input--error' : '' }}" name="notes" rows="3">{{ old('notes') }}</textarea>@error('notes') <span class="field-error">{{ $message }}</span> @enderror</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageInventory ? '' : 'disabled' }} title="{{ $canManageInventory ? __('inventory.create_item') : (($shell['permissions']['inventory.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('inventory.create_item') }}</button>
                <a class="cta-secondary" href="/backoffice/inventory">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">{{ __('inventory.items_list') }}</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">{{ __('inventory.no_items') }}</div>
        @else
            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr><th>{{ __('inventory.name') }}</th><th>{{ __('inventory.category') }}</th><th>{{ __('inventory.warehouse') }}</th><th>{{ __('inventory.current_stock') }}</th><th>{{ __('inventory.min_stock') }}</th><th>{{ __('inventory.cost_per_unit') }}</th><th>{{ __('inventory.status') }}</th></tr>
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
                                    <span class="status-badge status-warning">{{ __('inventory.low_stock') }}</span>
                                @else
                                    <span class="status-badge status-active">{{ __('inventory.ok') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>
@endsection
