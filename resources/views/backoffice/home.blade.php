@extends('backoffice.layout')

@section('title', 'Backoffice · Dashboard')

@section('content')
    <div class="card" style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0 0 4px;font-size:1.35rem;">Resumen Ejecutivo</h1>
                <div class="muted">Lectura rápida de operación para técnico y gerencia.</div>
            </div>
            <a href="{{ $vm['cta']['href'] }}" style="background:#0c7a6a;color:#fff;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:700;">
                {{ $vm['cta']['label'] }}
            </a>
        </div>
    </div>

    @if($vm['dashboard_enabled'] && $vm['kpis'] !== null)
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin-bottom:12px;">
            <div class="card"><div class="muted" style="font-size:.78rem;">Ciclos Activos</div><div style="font-size:1.3rem;font-weight:800;">{{ $vm['kpis']['active_cycles'] }}</div></div>
            <div class="card"><div class="muted" style="font-size:.78rem;">Biomasa Total</div><div style="font-size:1.3rem;font-weight:800;">{{ number_format($vm['kpis']['total_biomass_kg'], 2) }} kg</div></div>
            <div class="card"><div class="muted" style="font-size:.78rem;">FCR Promedio</div><div style="font-size:1.3rem;font-weight:800;">{{ $vm['kpis']['average_fcr'] !== null ? number_format($vm['kpis']['average_fcr'],3) : 'N/D' }}</div></div>
            <div class="card"><div class="muted" style="font-size:.78rem;">Alertas Críticas</div><div style="font-size:1.3rem;font-weight:800;color:#c63636;">{{ $vm['kpis']['critical_alerts'] }}</div></div>
        </div>
    @endif

    <div class="card">
        <h2 style="margin:0 0 8px;font-size:1rem;">Fincas con ciclos activos</h2>
        @if(count($vm['farms']) === 0)
            <div class="muted">No hay ciclos activos en este tenant.</div>
        @else
            <div style="display:grid;gap:8px;">
                @foreach($vm['farms'] as $farm)
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px;border:1px solid #d7e2ed;border-radius:10px;">
                        <div>
                            <strong>{{ $farm['farm_name'] }}</strong>
                            <div class="muted" style="font-size:.83rem;">{{ $farm['active_cycles'] }} ciclo(s) activo(s)</div>
                        </div>
                        <a href="/backoffice/cycles?farm={{ $farm['farm_id'] }}" style="font-size:.86rem;font-weight:700;color:#0c7a6a;text-decoration:none;">
                            Ver ciclos
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

