<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte Ejecutivo · Ciclo #{{ $vm['header']['cycle_id'] }}</title>
    <style>
        :root { --text:#0f2233; --muted:#5f6f7f; --border:#d7e2ed; --bg:#f6f8fb; --card:#fff; --primary:#0c7a6a; --warn:#db8d1b; --critical:#c63636; --info:#2f8fff; }
        * { box-sizing:border-box; }
        body { margin:0; font-family: ui-sans-serif, system-ui, sans-serif; background:#fff; color:var(--text); }
        .report { max-width: 1080px; margin: 0 auto; padding: 28px; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
        .print-btn { padding:10px 14px; border-radius:12px; border:1px solid var(--border); background:#fff; cursor:pointer; font-weight:700; }
        .hero, .card { border:1px solid var(--border); border-radius:18px; background:var(--card); padding:18px; }
        .hero h1 { margin:0 0 8px; font-size:1.7rem; }
        .meta { display:flex; flex-wrap:wrap; gap:10px; color:var(--muted); font-size:.95rem; }
        .pill { display:inline-flex; padding:6px 10px; border-radius:999px; background:#eef5fb; font-weight:700; }
        .grid { display:grid; gap:14px; }
        .kpis { grid-template-columns: repeat(auto-fit, minmax(170px,1fr)); margin-top:16px; }
        .kpi .label { font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); font-weight:700; margin-bottom:6px; }
        .kpi .value { font-size:1.35rem; font-weight:800; }
        .section { margin-top:18px; }
        .section h2 { margin:0 0 12px; font-size:1.05rem; }
        .projection { grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); }
        .mini-table { width:100%; border-collapse:collapse; font-size:.92rem; }
        .mini-table th, .mini-table td { padding:10px 8px; border-bottom:1px solid var(--border); text-align:left; vertical-align:top; }
        .sev-critical { color: var(--critical); font-weight:800; }
        .sev-warning { color: var(--warn); font-weight:800; }
        .sev-info { color: var(--info); font-weight:800; }
        .empty { color:var(--muted); padding:16px; border:1px dashed var(--border); border-radius:14px; }
        .doc-header { display:grid; grid-template-columns: minmax(0, 1.4fr) minmax(260px, .9fr); gap:18px; align-items:start; }
        .brand-block { display:flex; gap:16px; align-items:flex-start; }
        .brand-logo { width:160px; max-width:100%; height:auto; border-radius:14px; object-fit:contain; }
        .brand-logo--fallback { display:flex; align-items:center; justify-content:center; width:160px; min-height:72px; border-radius:14px; border:1px solid var(--border); background:#f8fbfe; color:var(--primary); font-weight:800; letter-spacing:.08em; text-transform:uppercase; font-size:.76rem; }
        .brand-kicker { color:var(--muted); font-size:.76rem; text-transform:uppercase; letter-spacing:.1em; font-weight:800; margin-bottom:6px; }
        .brand-title { margin:0 0 6px; font-size:1.7rem; line-height:1.1; }
        .brand-subtitle { margin:0; color:var(--muted); font-size:.95rem; }
        .brand-contact { margin-top:10px; display:flex; flex-wrap:wrap; gap:8px; color:var(--muted); font-size:.88rem; }
        .doc-meta { border:1px solid var(--border); border-radius:16px; padding:14px; background:#fcfdff; }
        .doc-meta__title { font-size:.78rem; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); font-weight:800; margin-bottom:10px; }
        .doc-meta__grid { display:grid; grid-template-columns: 1fr; gap:8px; font-size:.92rem; }
        .doc-meta strong { display:block; font-size:.75rem; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px; }
        .report-footer { margin-top:22px; border-top:1px solid var(--border); padding-top:12px; color:var(--muted); font-size:.85rem; display:grid; gap:6px; }
        @media print {
            @page { margin: 14mm 12mm; }
            .toolbar { display:none; }
            body { background:#fff; }
            .report { max-width:none; padding:0; }
            .hero, .card { break-inside:avoid; box-shadow:none; }
            .doc-header, .section, .hero, .card, table { break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="report">
    <div class="toolbar">
        <div>
            <strong>Reporte Ejecutivo</strong>
        </div>
        <button class="print-btn" onclick="window.print()">Imprimir</button>
    </div>

    <section class="hero">
        <div class="doc-header">
            <div class="brand-block">
                @if (!empty($vm['branding']['branding_logo']))
                    <img src="{{ $vm['branding']['branding_logo'] }}" alt="Logo empresa" class="brand-logo">
                @else
                    <div class="brand-logo--fallback">Empresa</div>
                @endif
                <div>
                    <div class="brand-kicker">Reporte Ejecutivo de Ciclo</div>
                    <h1 class="brand-title">{{ $vm['branding']['branding_name'] }}</h1>
                    <p class="brand-subtitle">{{ $vm['header']['farm_name'] }} · Piscina {{ $vm['header']['pond_code'] }} · Ciclo #{{ $vm['header']['cycle_id'] }}</p>
                    @if (!empty($vm['branding']['branding_contact_info']))
                        <div class="brand-contact">
                            @foreach ($vm['branding']['branding_contact_info'] as $item)
                                <span>{{ $item }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="doc-meta">
                <div class="doc-meta__title">Datos del documento</div>
                <div class="doc-meta__grid">
                    <div><strong>Estado</strong>{{ strtoupper($vm['header']['status']) }}</div>
                    <div><strong>Inicio de ciclo</strong>{{ $vm['header']['started_at'] ?? 'N/A' }}</div>
                    <div><strong>Fecha de generación</strong>{{ $vm['header']['generated_at'] }}</div>
                    @if (!empty($vm['branding']['branding_legal_name']))
                        <div><strong>Razón social</strong>{{ $vm['branding']['branding_legal_name'] }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="meta" style="margin-top:14px;">
            <span class="pill">Ciclo #{{ $vm['header']['cycle_id'] }}</span>
            <span>Inicio: {{ $vm['header']['started_at'] ?? 'N/A' }}</span>
            <span>Estado: {{ strtoupper($vm['header']['status']) }}</span>
            <span>Generado: {{ $vm['header']['generated_at'] }}</span>
        </div>
    </section>

    <section class="grid kpis section">
        <div class="card kpi"><div class="label">Biomasa actual</div><div class="value">{{ $vm['kpis']['biomass_kg'] !== null ? number_format((float) $vm['kpis']['biomass_kg'], 2) . ' kg' : 'N/D' }}</div></div>
        <div class="card kpi"><div class="label">PP actual</div><div class="value">{{ $vm['kpis']['latest_pp_grams'] !== null ? number_format((float) $vm['kpis']['latest_pp_grams'], 2) . ' g' : 'N/D' }}</div></div>
        <div class="card kpi"><div class="label">FCR</div><div class="value">{{ $vm['kpis']['fcr'] > 0 ? number_format((float) $vm['kpis']['fcr'], 3) : 'N/D' }}</div></div>
        <div class="card kpi"><div class="label">Alimento acumulado</div><div class="value">{{ number_format((float) $vm['kpis']['total_feed_kg'], 2) }} kg</div></div>
        <div class="card kpi"><div class="label">Costo acumulado</div><div class="value">${{ number_format((float) $vm['kpis']['total_cost'], 2) }}</div></div>
        <div class="card kpi"><div class="label">Mortalidad total</div><div class="value">{{ number_format((int) $vm['kpis']['total_mortality']) }}</div></div>
        <div class="card kpi"><div class="label">Supervivencia estimada</div><div class="value">{{ $vm['kpis']['estimated_survival_pct'] !== null ? number_format((float) $vm['kpis']['estimated_survival_pct'], 2) . '%' : 'N/D' }}</div></div>
    </section>

    <section class="section">
        <h2>Proyección de Cosecha</h2>
        <div class="grid projection">
            <div class="card kpi"><div class="label">Peso meta</div><div class="value">{{ $vm['projection']['projection']['target_pp_grams'] !== null ? number_format((float) $vm['projection']['projection']['target_pp_grams'], 2) . ' g' : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Fecha estimada</div><div class="value">{{ $vm['projection']['projection']['projected_harvest_date'] ?? 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Libras proyectadas</div><div class="value">{{ $vm['projection']['projection']['projected_total_lbs'] !== null ? number_format((float) $vm['projection']['projection']['projected_total_lbs'], 2) : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Ingreso proyectado</div><div class="value">{{ $vm['projection']['projection']['projected_revenue'] !== null ? '$' . number_format((float) $vm['projection']['projection']['projected_revenue'], 2) : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Costo proyectado</div><div class="value">{{ $vm['projection']['projection']['projected_cost'] !== null ? '$' . number_format((float) $vm['projection']['projection']['projected_cost'], 2) : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Utilidad proyectada</div><div class="value">{{ $vm['projection']['projection']['projected_profit'] !== null ? '$' . number_format((float) $vm['projection']['projection']['projected_profit'], 2) : 'N/D' }}</div></div>
        </div>
    </section>

    <section class="section card">
        <h2>Alertas recientes</h2>
        @if ($vm['recent_alerts'] === [])
            <div class="empty">No hay alertas recientes para este ciclo.</div>
        @else
            <table class="mini-table">
                <thead><tr><th>Severidad</th><th>Mensaje</th><th>Fecha</th></tr></thead>
                <tbody>
                @foreach ($vm['recent_alerts'] as $alert)
                    <tr>
                        <td class="sev-{{ strtolower($alert['severity']) }}">{{ strtoupper($alert['severity']) }}</td>
                        <td>{{ $alert['message'] }}</td>
                        <td>{{ $alert['detected_at'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="section card">
        <h2>Calidad de agua reciente</h2>
        @if ($vm['recent_water'] === [])
            <div class="empty">No hay registros recientes de calidad de agua.</div>
        @else
            <table class="mini-table">
                <thead><tr><th>Fecha</th><th>DO</th><th>pH</th><th>Temp</th><th>Salinidad</th></tr></thead>
                <tbody>
                @foreach ($vm['recent_water'] as $row)
                    <tr>
                        <td>{{ $row['measured_at'] }}</td>
                        <td>{{ $row['do'] ?? 'N/D' }}</td>
                        <td>{{ $row['ph'] ?? 'N/D' }}</td>
                        <td>{{ $row['temp'] ?? 'N/D' }}</td>
                        <td>{{ $row['salinity'] ?? 'N/D' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <footer class="report-footer">
        <div>Generado: {{ $vm['footer']['generated_at'] }}</div>
        @if (!empty($vm['footer']['footer_text']))
            <div>{{ $vm['footer']['footer_text'] }}</div>
        @endif
        <div>{{ $vm['footer']['system_signature'] }}</div>
    </footer>
</div>
</body>
</html>
