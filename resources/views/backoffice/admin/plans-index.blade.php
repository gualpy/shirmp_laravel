@extends('backoffice.layout')

@section('title', 'Superadmin · Plans')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">Planes SaaS</h1>
    <div class="section-subtitle">Lectura clara de planes, billing, límites y features disponibles.</div>
</div>

<div style="display:grid;gap:14px;">
@foreach($vm['rows'] as $row)
    <div class="card">
        <div class="panel-head">
            <div>
                <h3 class="panel-title" style="margin-bottom:4px;">{{ $row['name'] }}</h3>
                <div class="section-subtitle">{{ $row['code'] }} · {{ $row['billing_type'] }} · ${{ number_format((float) $row['price_usd'], 2) }}</div>
            </div>
            <span class="status-badge status-{{ $row['is_active'] ? 'active' : 'expired' }}">{{ $row['is_active'] ? __('app.active') : __('app.inactive') }}</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
                <strong>Límites</strong>
                <ul>
                    @foreach($row['limits'] as $limit)
                        <li>{{ $limit }}</li>
                    @endforeach
                </ul>
            </div>
            <div>
                <strong>{{ __('admin.features') }}</strong>
                <ul>
                    @foreach($row['features'] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endforeach
</div>
@endsection
