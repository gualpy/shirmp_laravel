@extends('backoffice.layout')

@section('title', 'Backoffice · Ciclos activos')

@section('content')
    <div class="card" style="margin-bottom:12px;">
        <h1 style="margin:0 0 8px;font-size:1.25rem;">Ciclos Activos</h1>
        <form method="GET" action="/backoffice/cycles" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;">
            <label>
                <span class="muted" style="display:block;font-size:.8rem;margin-bottom:4px;">Farm</span>
                <select name="farm" style="width:100%;padding:9px;border:1px solid #d7e2ed;border-radius:8px;">
                    <option value="">Todas</option>
                    @foreach($vm['options']['farms'] as $farm)
                        <option value="{{ $farm['id'] }}" {{ $vm['filters']['farm'] === $farm['id'] ? 'selected' : '' }}>
                            {{ $farm['name'] }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="muted" style="display:block;font-size:.8rem;margin-bottom:4px;">Pond</span>
                <select name="pond" style="width:100%;padding:9px;border:1px solid #d7e2ed;border-radius:8px;">
                    <option value="">Todas</option>
                    @foreach($vm['options']['ponds'] as $pond)
                        <option value="{{ $pond['id'] }}" {{ $vm['filters']['pond'] === $pond['id'] ? 'selected' : '' }}>
                            {{ $pond['code'] }}
                        </option>
                    @endforeach
                </select>
            </label>
            <div style="display:flex;align-items:flex-end;">
                <button type="submit" style="width:100%;padding:10px;border:none;border-radius:8px;background:#0c7a6a;color:#fff;font-weight:700;">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <div style="display:grid;gap:10px;">
        @forelse($vm['rows'] as $row)
            <a href="{{ $row['detail_href'] }}" class="card" style="text-decoration:none;color:inherit;display:block;">
                <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:1rem;font-weight:800;">{{ $row['pond_code'] }} · {{ $row['farm'] }}</div>
                        <div class="muted" style="font-size:.83rem;">Inicio {{ $row['started_at'] }}</div>
                    </div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <span style="padding:5px 8px;border-radius:999px;background:#e7f5f1;font-size:.78rem;font-weight:700;">Biomasa {{ number_format($row['biomass_kg'],2) }} kg</span>
                        <span style="padding:5px 8px;border-radius:999px;background:#e9f0ff;font-size:.78rem;font-weight:700;">PP {{ $row['latest_pp'] !== null ? number_format($row['latest_pp'],2).' g' : 'N/D' }}</span>
                        <span style="padding:5px 8px;border-radius:999px;background:#ffeceb;font-size:.78rem;font-weight:700;">C {{ $row['alerts_critical'] }}</span>
                        <span style="padding:5px 8px;border-radius:999px;background:#fff4e8;font-size:.78rem;font-weight:700;">W {{ $row['alerts_warning'] }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="card muted">No hay ciclos activos con los filtros actuales.</div>
        @endforelse
    </div>
@endsection

