@extends('backoffice.layout')

@section('title', 'Backoffice · Inventario '.$vm['item']['name'])

@section('content')
@php($canManageInventory = ($shell['permissions']['inventory.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">{{ $vm['item']['name'] }}</h1>
        <div class="section-subtitle">{{ $vm['item']['category'] }} · {{ $vm['item']['warehouse'] }} · {{ $vm['item']['current_stock'] }} {{ $vm['item']['unit'] }}</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="cta-secondary" href="/backoffice/inventory/{{ $vm['item']['id'] }}/movements.xlsx">{{ __('cycle.export_movements') }}</a>
        <a class="cta-secondary" href="/backoffice/inventory">{{ __('cycle.back_to_inventory') }}</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(320px,380px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">{{ __('cycle.summary') }}</h3>
        <p><strong>{{ __('cycle.warehouse') }}:</strong> {{ $vm['item']['warehouse'] }}</p>
        <p><strong>{{ __('cycle.current_stock') }}:</strong> {{ $vm['item']['current_stock'] }} {{ $vm['item']['unit'] }}</p>
        <p><strong>{{ __('cycle.min_stock') }}:</strong> {{ $vm['item']['min_stock'] ? $vm['item']['min_stock'].' '.$vm['item']['unit'] : 'N/A' }}</p>
        <p><strong>{{ __('cycle.cost_per_unit') }}:</strong> {{ $vm['item']['cost_per_unit'] ? '$'.$vm['item']['cost_per_unit'] : 'N/A' }}</p>
        <p><strong>{{ __('inventory.status') }}:</strong> @if($vm['item']['low_stock'])<span class="status-badge status-warning">{{ __('inventory.low_stock') }}</span>@else<span class="status-badge status-active">{{ __('inventory.ok') }}</span>@endif</p>
        <p><strong>{{ __('cycle.notes') }}:</strong> {{ $vm['item']['notes'] ?? 'N/A' }}</p>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">{{ __('cycle.record_movement') }}</h3>
        <form method="POST" action="/backoffice/inventory/{{ $vm['item']['id'] }}/movements" style="display:grid;gap:12px;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label>
                    <span class="metric-label">{{ __('cycle.movement_type') }}</span>
                    <select class="input" name="movement_type">
                        @foreach($vm['movement_type_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected(old('movement_type', 'in') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="metric-label">{{ __('cycle.quantity') }}</span><input class="input" type="number" step="0.01" name="quantity" value="{{ old('quantity') }}"></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">{{ __('cycle.date') }}</span><input class="input" type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"></label>
                <label>
                    <span class="metric-label">{{ __('cycle.reference') }}</span>
                    <select class="input" name="reference_type">
                        @foreach($vm['reference_type_options'] as $option)
                            <option value="{{ $option['value'] }}" @selected(old('reference_type', 'manual') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label><span class="metric-label">{{ __('cycle.notes') }}</span><textarea class="input" name="notes" rows="3">{{ old('notes') }}</textarea></label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageInventory ? '' : 'disabled' }} title="{{ $canManageInventory ? __('cycle.record_movement') : (($shell['permissions']['inventory.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('cycle.save_movement') }}</button>
                <a class="cta-secondary" href="/backoffice/inventory/{{ $vm['item']['id'] }}">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card animate-enter-down animate-enter-down-delay-3" style="margin-top:16px;">
    <h3 class="panel-title">{{ __('cycle.movements') }}</h3>
    @if($vm['movements'] === [])
        <div class="section-subtitle">{{ __('cycle.no_movements') }}</div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>{{ __('cycle.date') }}</th><th>{{ __('cycle.movement_type') }}</th><th>{{ __('cycle.quantity') }}</th><th>{{ __('cycle.reference') }}</th><th>{{ __('cycle.user') }}</th><th>{{ __('cycle.notes') }}</th></tr>
            </thead>
            <tbody>
                @foreach($vm['movements'] as $movement)
                    <tr>
                        <td>{{ $movement['occurred_at'] }}</td>
                        <td><span class="status-badge status-{{ $movement['movement_type'] === 'out' ? 'warning' : 'active' }}">{{ $movement['movement_type'] }}</span></td>
                        <td>{{ $movement['quantity'] }}</td>
                        <td>{{ $movement['reference_type'] ?? 'N/A' }}</td>
                        <td>{{ $movement['created_by'] ?? 'N/A' }}</td>
                        <td>{{ $movement['notes'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
