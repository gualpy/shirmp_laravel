@extends('backoffice.layout')

@section('title', 'Backoffice · Bodegas')

@section('content')
@php($canManageInventory = ($shell['permissions']['inventory.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">{{ __('inventory.warehouses_title') }}</h1>
        <div class="section-subtitle">{{ __('inventory.warehouses_subtitle') }}</div>
    </div>
    <a class="cta-secondary" href="/backoffice/inventory">{{ __('inventory.view_inventory') }}</a>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(320px,380px) minmax(0,1fr);gap:16px;align-items:start;">
    <div class="card">
        <h3 class="panel-title">{{ __('inventory.new_warehouse') }}</h3>
        <form method="POST" action="/backoffice/warehouses" style="display:grid;gap:12px;">
            @csrf
            <label><span class="metric-label">{{ __('inventory.name') }}</span><input class="input{{ $errors->has('name') ? ' input--error' : '' }}" name="name" value="{{ old('name') }}">@error('name') <span class="field-error">{{ $message }}</span> @enderror</label>
            <label><span class="metric-label">{{ __('inventory.location') }}</span><input class="input{{ $errors->has('location') ? ' input--error' : '' }}" name="location" value="{{ old('location') }}">@error('location') <span class="field-error">{{ $message }}</span> @enderror</label>
            <label><span class="metric-label">{{ __('inventory.notes') }}</span><textarea class="input{{ $errors->has('notes') ? ' input--error' : '' }}" name="notes" rows="4">{{ old('notes') }}</textarea>@error('notes') <span class="field-error">{{ $message }}</span> @enderror</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageInventory ? '' : 'disabled' }} title="{{ $canManageInventory ? __('inventory.create_warehouse') : (($shell['permissions']['inventory.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('inventory.create_warehouse') }}</button>
                <a class="cta-secondary" href="/backoffice/warehouses">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="panel-title">{{ __('inventory.warehouses_list') }}</h3>
        @if($vm['rows'] === [])
            <div class="section-subtitle">{{ __('inventory.no_warehouses') }}</div>
        @else
            <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr><th>{{ __('inventory.name') }}</th><th>{{ __('inventory.location') }}</th><th>{{ __('inventory.items_count') }}</th><th>{{ __('inventory.notes') }}</th></tr>
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
            </div>
        @endif
    </div>
</div>
@endsection
