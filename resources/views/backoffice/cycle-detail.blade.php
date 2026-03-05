@extends('backoffice.layout')

@section('title', 'Backoffice · Ciclo #'.$vm['header']['cycle_id'])

@push('head')
<style>
    .actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:10px; margin-bottom:14px; }
    .action-btn {
        background: linear-gradient(140deg, #0f8d79, #0a6f95);
        color:#fff; border:none; border-radius:12px; padding:11px 12px; font-size:.92rem; font-weight:700;
        text-align:left; box-shadow: var(--shadow); text-decoration:none; display:flex; align-items:center; justify-content:space-between;
        min-height:52px;
    }
    .action-btn:after { content:"›"; font-size:1.2rem; opacity:.9; }
    .action-btn.disabled { background:#d3dae2; color:#6a7682; box-shadow:none; pointer-events:none; }
    .kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:14px; }
    .kpi { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:10px; }
    .kpi .label { font-size:.76rem; color:var(--muted); font-weight:600; margin-bottom:4px; text-transform:uppercase; letter-spacing:.05em; }
    .kpi .value { font-size:1.34rem; font-weight:750; }
    .grid { display:grid; gap:12px; grid-template-columns:1.5fr 1fr; }
    .row-2 { display:grid; gap:12px; grid-template-columns:1fr 1fr; margin-top:12px; }
    .chart-box { position:relative; height:250px; }
    canvas { width:100%; height:100%; }
    .alert-list { display:grid; gap:8px; max-height:290px; overflow:auto; }
    .alert-item { border:1px solid var(--border); border-left-width:5px; border-radius:10px; padding:8px; font-size:.86rem; }
    .sev-critical { border-left-color:#c63636; }
    .sev-warning { border-left-color:#db8d1b; }
    .sev-info { border-left-color:#2f8fff; }
    .water-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
    .water-box { border:1px solid var(--border); border-radius:10px; padding:8px; text-align:center; }
    .water-box .big { font-size:1.2rem; font-weight:700; }
    .legend { display:flex; gap:10px; flex-wrap:wrap; font-size:.8rem; color:var(--muted); }
    .dot { width:9px; height:9px; border-radius:999px; display:inline-block; margin-right:5px; }
    @media (max-width:980px){ .grid,.row-2{grid-template-columns:1fr;} .chart-box{height:230px;} }
</style>
@endpush

@section('content')
    <div class="card" style="margin-bottom:12px;">
        <h1 style="margin:0 0 4px;font-size:1.35rem;">Detalle Operativo de Ciclo #{{ $vm['header']['cycle_id'] }}</h1>
        <div class="muted">Finca {{ $vm['header']['farm_name'] }} · Piscina {{ $vm['header']['pond_code'] }} · Inicio {{ $vm['header']['started_at'] }}</div>
    </div>

    <div class="actions">
        @foreach($vm['actions'] as $action)
            <a class="action-btn {{ $action['disabled'] ? 'disabled' : '' }}"
               href="{{ $action['disabled'] ? '#' : $action['href'] }}"
               title="{{ $action['disabled'] ? $action['disabled_reason'] : 'Acción rápida operativa' }}">
                {{ $action['label'] }}
            </a>
        @endforeach
    </div>

    <div class="kpis">
        <div class="kpi"><div class="label">Biomasa Estimada</div><div class="value">{{ number_format($vm['kpis']['biomass_kg'],2) }} kg</div></div>
        <div class="kpi"><div class="label">Peso Promedio</div><div class="value">{{ $vm['kpis']['latest_pp_grams'] !== null ? number_format($vm['kpis']['latest_pp_grams'],2).' g' : 'N/D' }}</div></div>
        <div class="kpi"><div class="label">FCR</div><div class="value">{{ number_format($vm['kpis']['fcr'],3) }}</div></div>
        <div class="kpi"><div class="label">Alimento Acum.</div><div class="value">{{ number_format($vm['kpis']['total_feed_kg'],2) }} kg</div></div>
        <div class="kpi"><div class="label">Costo Acum.</div><div class="value">${{ number_format($vm['kpis']['total_cost_usd'],2) }}</div></div>
        <div class="kpi"><div class="label">Alertas Abiertas</div><div class="value">{{ $vm['kpis']['open_alerts'] }}</div></div>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin:0 0 8px;">Biomasa vs Tiempo</h3>
            <div class="chart-box"><canvas id="biomassChart"></canvas></div>
        </div>
        <div class="card">
            <h3 style="margin:0 0 8px;">FCR Semanal</h3>
            <div class="chart-box"><canvas id="fcrChart"></canvas></div>
        </div>
    </div>

    <div class="row-2">
        <div class="card">
            <h3 style="margin:0 0 8px;">Distribución de Costos</h3>
            <div class="chart-box"><canvas id="costChart"></canvas></div>
            <div class="legend">
                <span><span class="dot" style="background:#2f8fff;"></span>Alimento</span>
                <span><span class="dot" style="background:#0c7a6a;"></span>Operación</span>
            </div>
        </div>
        <div class="card">
            <h3 style="margin:0 0 8px;">Calidad de Agua (Últimos 3 días)</h3>
            <div class="water-grid">
                <div class="water-box"><div class="muted">DO</div><div class="big">{{ $vm['water_quality_latest']['avg_do'] !== null ? number_format($vm['water_quality_latest']['avg_do'],2) : 'N/D' }}</div></div>
                <div class="water-box"><div class="muted">pH</div><div class="big">{{ $vm['water_quality_latest']['avg_ph'] !== null ? number_format($vm['water_quality_latest']['avg_ph'],2) : 'N/D' }}</div></div>
                <div class="water-box"><div class="muted">Temp °C</div><div class="big">{{ $vm['water_quality_latest']['avg_temp'] !== null ? number_format($vm['water_quality_latest']['avg_temp'],2) : 'N/D' }}</div></div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:12px;">
        <h3 style="margin:0 0 8px;">Timeline de Alertas</h3>
        <div class="alert-list">
            @forelse($vm['charts']['alerts_timeline'] as $alert)
                <div class="alert-item sev-{{ $alert['severity'] }}">
                    <div style="display:flex;justify-content:space-between;gap:10px;">
                        <strong>{{ $alert['title'] }}</strong>
                        <span class="muted">{{ $alert['detected_at'] }}</span>
                    </div>
                    <div>{{ $alert['message'] }}</div>
                </div>
            @empty
                <div class="muted">Sin alertas recientes para este ciclo.</div>
            @endforelse
        </div>
    </div>

    <script>
        const biomassData = @json($vm['charts']['biomass_vs_time']);
        const fcrData = @json($vm['charts']['fcr_weekly']);
        const costData = @json($vm['charts']['cost_distribution']);

        function setupCanvas(id) {
            const c = document.getElementById(id);
            const ctx = c.getContext('2d');
            c.width = c.clientWidth * devicePixelRatio;
            c.height = c.clientHeight * devicePixelRatio;
            ctx.scale(devicePixelRatio, devicePixelRatio);
            return { c, ctx };
        }

        function drawLine(id, points, key, color) {
            const { c, ctx } = setupCanvas(id);
            const w = c.clientWidth, h = c.clientHeight, pad = 24;
            const vals = points.map(p => Number(p[key]) || 0);
            const max = Math.max(...vals, 1), min = Math.min(...vals, 0);
            const iw = w - pad * 2, ih = h - pad * 2;
            ctx.clearRect(0, 0, w, h);
            ctx.strokeStyle = '#dce7f1';
            for (let i = 0; i < 4; i++) {
                const y = pad + (ih / 3) * i;
                ctx.beginPath(); ctx.moveTo(pad, y); ctx.lineTo(w - pad, y); ctx.stroke();
            }
            if (points.length < 2) return;
            ctx.strokeStyle = color; ctx.lineWidth = 2.5; ctx.beginPath();
            points.forEach((p, i) => {
                const x = pad + (iw / (points.length - 1)) * i;
                const y = h - pad - (((Number(p[key]) || 0) - min) / (max - min || 1)) * ih;
                if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
            });
            ctx.stroke();
        }

        function drawDonut(id, parts) {
            const { c, ctx } = setupCanvas(id);
            const w = c.clientWidth, h = c.clientHeight;
            const total = parts.reduce((a, p) => a + Number(p.value || 0), 0);
            if (total <= 0) return;
            const colors = ['#2f8fff', '#0c7a6a', '#db8d1b', '#c63636'];
            const r = Math.min(w, h) * .34, cx = w / 2, cy = h / 2;
            let start = -Math.PI / 2;
            parts.forEach((p, i) => {
                const sweep = (Number(p.value || 0) / total) * Math.PI * 2;
                ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, start, start + sweep); ctx.closePath();
                ctx.fillStyle = colors[i % colors.length]; ctx.fill();
                start += sweep;
            });
            ctx.beginPath(); ctx.fillStyle = '#fff'; ctx.arc(cx, cy, r * .55, 0, Math.PI * 2); ctx.fill();
        }

        drawLine('biomassChart', biomassData, 'biomass_kg', '#0c7a6a');
        drawLine('fcrChart', fcrData.filter(r => r.fcr !== null), 'fcr', '#2f8fff');
        drawDonut('costChart', costData);
    </script>
@endsection

