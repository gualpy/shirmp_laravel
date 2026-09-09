<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte Ejecutivo · Ciclo #{{ $vm['header']['cycle_id'] }}</title>
    <style>
        :root {
            --text:#0f2233; --muted:#5f6f7f; --border:#e1e8ef; --bg:#f6f8fb; --card:#fff;
            --primary:#0c7a6a; --primary-soft:rgba(12,122,106,.08);
            --gain:#0c7a6a; --gain-soft:rgba(12,122,106,.08);
            --loss:#c63636; --loss-soft:rgba(198,54,54,.08);
            --warn:#db8d1b; --warn-soft:rgba(219,141,27,.1);
            --critical:#c63636; --critical-soft:rgba(198,54,54,.08);
            --info:#2f8fff; --info-soft:rgba(47,143,255,.08);
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; background:var(--bg); color:var(--text); -webkit-font-smoothing:antialiased; }
        .report { max-width: 1000px; margin: 0 auto; padding: 24px; }

        .toolbar { display:flex; justify-content:flex-end; margin-bottom:14px; }
        .print-btn { padding:9px 16px; border-radius:10px; border:1px solid var(--border); background:#fff; cursor:pointer; font-weight:700; font-size:.86rem; }
        .print-btn:hover { background:var(--primary-soft); }

        .card { border:1px solid var(--border); border-radius:14px; background:var(--card); padding:16px; }
        .section { margin-top:14px; }
        .section-title {
            display:flex; align-items:center; gap:8px;
            margin:0 0 10px; font-size:.76rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:var(--muted);
        }

        /* ---- Hero / header ---- */
        .hero { padding:16px 18px; }
        .hero__row { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
        .brand-block { display:flex; gap:14px; align-items:center; }
        .brand-logo { width:56px; height:56px; border-radius:12px; object-fit:contain; flex-shrink:0; }
        .brand-logo--fallback {
            display:flex; align-items:center; justify-content:center; width:56px; height:56px; flex-shrink:0;
            border-radius:12px; border:1px solid var(--border); background:var(--primary-soft); color:var(--primary);
            font-weight:800; font-size:.85rem;
        }
        .doc-kicker { color:var(--muted); font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; font-weight:800; margin-bottom:3px; }
        .doc-title { margin:0 0 4px; font-size:1.28rem; font-weight:800; letter-spacing:-.02em; }
        .doc-meta-line { color:var(--muted); font-size:.86rem; }
        .status-badge {
            display:inline-flex; align-items:center; gap:7px; padding:6px 12px; border-radius:999px;
            background:var(--primary-soft); color:var(--primary); font-weight:800; font-size:.76rem;
            letter-spacing:.06em; text-transform:uppercase; white-space:nowrap;
        }
        .status-badge::before { content:""; width:8px; height:8px; border-radius:999px; background:currentColor; }
        .status-badge--harvested { background:rgba(100,116,133,.12); color:#4d5c6b; }
        .status-badge--cancelled { background:var(--loss-soft); color:var(--loss); }
        .hero__footer-line {
            margin-top:12px; padding-top:10px; border-top:1px solid var(--border);
            display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px;
            color:var(--muted); font-size:.78rem;
        }

        /* ---- Alerts summary banner (page 1) ---- */
        .alert-banner {
            margin-top:12px; padding:10px 14px; border-radius:12px;
            display:flex; align-items:center; gap:9px; font-weight:700; font-size:.86rem;
        }
        .alert-banner--attention { background:var(--warn-soft); color:#8d5b05; border:1px solid rgba(219,141,27,.25); }
        .alert-banner--ok { background:var(--gain-soft); color:var(--gain); border:1px solid rgba(12,122,106,.2); }

        /* ---- KPI grids ---- */
        .kpi-grid { display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:10px; }
        .kpi-grid--secondary { grid-template-columns: repeat(2, minmax(0,1fr)); margin-top:8px; }
        .kpi { padding:14px; }
        .kpi .label { font-size:.7rem; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); font-weight:700; margin-bottom:6px; }
        .kpi .value { font-size:1.5rem; font-weight:800; letter-spacing:-.02em; }
        .kpi--secondary .value { font-size:1.1rem; }

        /* ---- Financial summary (highest hierarchy) ---- */
        .finance-card { padding:18px 20px; }
        .finance-top { display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
        .finance-cost-label { font-size:.74rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); font-weight:700; }
        .finance-cost-value { font-size:1.3rem; font-weight:800; }
        .finance-row { display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px; }
        .finance-mini .label { font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); font-weight:700; margin-bottom:4px; }
        .finance-mini .value { font-size:1.15rem; font-weight:800; }
        .profit-block {
            border-radius:14px; padding:18px 20px; text-align:center;
        }
        .profit-block--gain { background:var(--gain-soft); border:1px solid rgba(12,122,106,.22); }
        .profit-block--loss { background:var(--loss-soft); border:1px solid rgba(198,54,54,.22); }
        .profit-block--na { background:#f3f5f7; border:1px solid var(--border); }
        .profit-label { font-size:.76rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; margin-bottom:6px; }
        .profit-block--gain .profit-label { color:var(--gain); }
        .profit-block--loss .profit-label { color:var(--loss); }
        .profit-block--na .profit-label { color:var(--muted); }
        .profit-value { font-size:2.2rem; font-weight:800; letter-spacing:-.03em; }
        .profit-block--gain .profit-value { color:var(--gain); }
        .profit-block--loss .profit-value { color:var(--loss); }
        .profit-block--na .profit-value { color:var(--muted); font-size:1.1rem; font-weight:700; }
        .finance-extra { display:flex; justify-content:center; gap:18px; margin-top:10px; font-size:.8rem; color:var(--muted); flex-wrap:wrap; }
        .finance-extra strong { color:var(--text); }

        /* ---- Projection ---- */
        .projection-grid { display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:10px; }
        .projection-note {
            padding:10px 14px; border-radius:12px; background:#f3f5f7; color:var(--muted);
            font-size:.85rem; border:1px dashed var(--border);
        }

        /* ---- Page 2: alerts & water ---- */
        .page-break { }
        @media screen {
            .page-marker {
                margin: 26px 0 14px; padding-top:16px; border-top:2px solid var(--border);
                font-size:.76rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:var(--muted);
            }
        }
        .alert-list { display:grid; gap:8px; }
        .alert-item {
            display:flex; justify-content:space-between; align-items:flex-start; gap:12px;
            padding:10px 12px; border-radius:10px; border:1px solid var(--border); border-left-width:4px;
            font-size:.88rem; background:#fff;
        }
        .alert-item--critical { border-left-color:var(--critical); background:var(--critical-soft); }
        .alert-item--warning { border-left-color:var(--warn); background:var(--warn-soft); }
        .alert-item--info { border-left-color:var(--info); background:var(--info-soft); }
        .alert-item__sev {
            flex-shrink:0; font-weight:800; font-size:.68rem; text-transform:uppercase; letter-spacing:.06em;
            padding:3px 8px; border-radius:999px; background:rgba(255,255,255,.6);
        }
        .alert-item--critical .alert-item__sev { color:var(--critical); }
        .alert-item--warning .alert-item__sev { color:#8d5b05; }
        .alert-item--info .alert-item__sev { color:var(--info); }
        .alert-item__body { flex:1; min-width:0; }
        .alert-item__date { color:var(--muted); font-size:.76rem; white-space:nowrap; }

        .mini-table { width:100%; border-collapse:collapse; font-size:.86rem; }
        .mini-table th {
            text-align:left; padding:8px; font-size:.68rem; text-transform:uppercase; letter-spacing:.06em;
            color:var(--muted); border-bottom:1px solid var(--border); font-weight:800;
        }
        .mini-table td { padding:9px 8px; border-bottom:1px solid var(--border); vertical-align:top; }
        .empty { color:var(--muted); padding:14px; border:1px dashed var(--border); border-radius:12px; font-size:.88rem; }

        .report-footer {
            margin-top:20px; border-top:1px solid var(--border); padding-top:12px;
            color:var(--muted); font-size:.8rem; display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px;
        }

        @media print {
            @page { margin: 14mm 12mm; }
            .toolbar { display:none; }
            body { background:#fff; }
            .report { max-width:none; padding:0; }
            .card { box-shadow:none; }
            .card, .kpi, table, .alert-item { break-inside:avoid; }
            .page-break { break-before: page; }
        }
    </style>
</head>
<body>
<div class="report">
    <div class="toolbar">
        <button class="print-btn" onclick="window.print()">🖨 Imprimir</button>
    </div>

    @php
        $statusValue = $vm['header']['status'];
        $statusLabels = ['active' => 'Activo', 'harvested' => 'Cosechado', 'cancelled' => 'Cancelado'];
        $statusLabel = $statusLabels[$statusValue] ?? strtoupper($statusValue);

        $severityLabels = ['info' => 'Informativa', 'warning' => 'Advertencia', 'critical' => 'Crítica'];
        $attentionAlerts = collect($vm['recent_alerts'])->whereIn('severity', ['critical', 'warning']);

        $proj = $vm['projection']['projection'];
        $isHarvested = $statusValue === 'harvested' || $statusValue === 'cancelled';
        $hasNoProjectionData = $proj['projected_harvest_date'] === null
            && $proj['projected_biomass_kg'] === null
            && $proj['projected_profit'] === null;

        $marginPct = ($proj['projected_revenue'] !== null && $proj['projected_revenue'] > 0 && $proj['projected_profit'] !== null)
            ? round(($proj['projected_profit'] / $proj['projected_revenue']) * 100, 1)
            : null;
        $costPerLbProjected = ($proj['projected_total_lbs'] !== null && $proj['projected_total_lbs'] > 0 && $proj['projected_cost'] !== null)
            ? round($proj['projected_cost'] / $proj['projected_total_lbs'], 4)
            : null;
    @endphp

    {{-- ============ PÁGINA 1 — ¿CÓMO ESTÁ MI INVERSIÓN? ============ --}}

    <section class="card hero">
        <div class="hero__row">
            <div class="brand-block">
                @if (!empty($vm['branding']['branding_logo']))
                    <img src="{{ $vm['branding']['branding_logo'] }}" alt="Logo" class="brand-logo">
                @else
                    <div class="brand-logo--fallback">{{ strtoupper(substr($vm['branding']['branding_name'], 0, 2)) }}</div>
                @endif
                <div>
                    <div class="doc-kicker">Reporte Ejecutivo de Ciclo</div>
                    <h1 class="doc-title">{{ $vm['branding']['branding_name'] }}</h1>
                    <div class="doc-meta-line">{{ $vm['header']['farm_name'] }} · Piscina {{ $vm['header']['pond_code'] }} · Ciclo #{{ $vm['header']['cycle_id'] }} · Inicio: {{ $vm['header']['started_at'] ?? 'N/D' }}</div>
                </div>
            </div>
            <span class="status-badge status-badge--{{ $statusValue }}">{{ $statusLabel }}</span>
        </div>
        <div class="hero__footer-line">
            <span>Generado: {{ $vm['header']['generated_at'] }}</span>
            @if (!empty($vm['branding']['branding_legal_name']))
                <span>{{ $vm['branding']['branding_legal_name'] }}</span>
            @endif
        </div>

        @if ($attentionAlerts->count() > 0)
            <div class="alert-banner alert-banner--attention">
                ⚠ {{ $attentionAlerts->count() }} {{ $attentionAlerts->count() === 1 ? 'alerta requiere' : 'alertas requieren' }} atención — ver detalle en página 2
            </div>
        @else
            <div class="alert-banner alert-banner--ok">
                ✓ Sin alertas críticas ni de advertencia recientes
            </div>
        @endif
    </section>

    <section class="section">
        <h2 class="section-title">KPIs Productivos</h2>
        <div class="kpi-grid">
            <div class="card kpi"><div class="label">Peso actual</div><div class="value">{{ $vm['kpis']['latest_pp_grams'] !== null ? number_format((float) $vm['kpis']['latest_pp_grams'], 2) . ' g' : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Biomasa</div><div class="value">{{ $vm['kpis']['biomass_kg'] !== null ? number_format((float) $vm['kpis']['biomass_kg'], 2) . ' kg' : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">Supervivencia</div><div class="value">{{ $vm['kpis']['estimated_survival_pct'] !== null ? number_format((float) $vm['kpis']['estimated_survival_pct'], 2) . '%' : 'N/D' }}</div></div>
            <div class="card kpi"><div class="label">FCR</div><div class="value">{{ $vm['kpis']['fcr'] > 0 ? number_format((float) $vm['kpis']['fcr'], 3) : 'N/D' }}</div></div>
        </div>
        <div class="kpi-grid kpi-grid--secondary">
            <div class="card kpi kpi--secondary"><div class="label">Alimento acumulado</div><div class="value">{{ number_format((float) $vm['kpis']['total_feed_kg'], 2) }} kg</div></div>
            <div class="card kpi kpi--secondary"><div class="label">Mortalidad total</div><div class="value">{{ number_format((int) $vm['kpis']['total_mortality']) }}</div></div>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Resumen Financiero</h2>
        <div class="card finance-card">
            <div class="finance-top">
                <span class="finance-cost-label">Costo acumulado</span>
                <span class="finance-cost-value">${{ number_format((float) $vm['kpis']['total_cost'], 2) }}</span>
            </div>
            <div class="finance-row">
                <div class="finance-mini"><div class="label">Ingreso proyectado</div><div class="value">{{ (! $isHarvested && $proj['projected_revenue'] !== null) ? '$' . number_format((float) $proj['projected_revenue'], 2) : 'N/D' }}</div></div>
                <div class="finance-mini"><div class="label">Costo proyectado</div><div class="value">{{ (! $isHarvested && $proj['projected_cost'] !== null) ? '$' . number_format((float) $proj['projected_cost'], 2) : 'N/D' }}</div></div>
            </div>

            @if ($isHarvested || $proj['projected_profit'] === null)
                <div class="profit-block profit-block--na">
                    <div class="profit-label">Utilidad / Pérdida proyectada</div>
                    <div class="profit-value">{{ $isHarvested ? 'No aplica (ciclo ya finalizado)' : 'Datos insuficientes para proyectar' }}</div>
                </div>
            @else
                @php($isGain = (float) $proj['projected_profit'] >= 0)
                <div class="profit-block {{ $isGain ? 'profit-block--gain' : 'profit-block--loss' }}">
                    <div class="profit-label">{{ $isGain ? 'Utilidad Proyectada' : 'Pérdida Proyectada' }}</div>
                    <div class="profit-value">{{ $isGain ? '+' : '-' }}${{ number_format(abs((float) $proj['projected_profit']), 2) }}</div>
                </div>
            @endif

            @if (! $isHarvested && ($marginPct !== null || $costPerLbProjected !== null))
                <div class="finance-extra">
                    @if ($marginPct !== null)
                        <span>Margen proyectado: <strong>{{ number_format($marginPct, 1) }}%</strong></span>
                    @endif
                    @if ($costPerLbProjected !== null)
                        <span>Costo/lb proyectado: <strong>${{ number_format($costPerLbProjected, 4) }}</strong></span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Proyección de Cosecha</h2>
        @if ($isHarvested)
            <div class="projection-note">Este ciclo ya fue {{ $statusLabel === 'Cosechado' ? 'cosechado' : 'cerrado' }} — la proyección a futuro ya no aplica. Los valores de cosecha real están en el detalle del ciclo.</div>
        @elseif ($hasNoProjectionData)
            <div class="projection-note">Datos insuficientes para proyectar (falta muestreo de peso y/o estimación de supervivencia reciente).</div>
        @else
            <div class="projection-grid">
                <div class="card kpi"><div class="label">Peso meta</div><div class="value">{{ $proj['target_pp_grams'] !== null ? number_format((float) $proj['target_pp_grams'], 2) . ' g' : 'N/D' }}</div></div>
                <div class="card kpi"><div class="label">Fecha estimada</div><div class="value">{{ $proj['projected_harvest_date'] ?? 'N/D' }}</div></div>
                <div class="card kpi"><div class="label">Libras proyectadas</div><div class="value">{{ $proj['projected_total_lbs'] !== null ? number_format((float) $proj['projected_total_lbs'], 2) : 'N/D' }}</div></div>
                <div class="card kpi"><div class="label">Ingreso proyectado</div><div class="value">{{ $proj['projected_revenue'] !== null ? '$' . number_format((float) $proj['projected_revenue'], 2) : 'N/D' }}</div></div>
            </div>
        @endif
    </section>

    {{-- ============ PÁGINA 2 — ¿POR QUÉ ESTÁ ASÍ? ============ --}}

    <div class="page-break"></div>
    <div class="page-marker">Página 2 — ¿Por qué está así?</div>

    <section class="section card">
        <h2 class="section-title">Alertas Recientes</h2>
        @if ($vm['recent_alerts'] === [])
            <div class="empty">No hay alertas recientes para este ciclo.</div>
        @else
            <div class="alert-list">
                @foreach ($vm['recent_alerts'] as $alert)
                    @php($sev = strtolower($alert['severity']))
                    <div class="alert-item alert-item--{{ $sev }}">
                        <div class="alert-item__body">
                            <span class="alert-item__sev">{{ $severityLabels[$sev] ?? strtoupper($alert['severity']) }}</span>
                            <div style="margin-top:5px;">{{ $alert['message'] }}</div>
                        </div>
                        <span class="alert-item__date">{{ $alert['detected_at'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="section card">
        <h2 class="section-title">Calidad de Agua Reciente</h2>
        @if ($vm['recent_water'] === [])
            <div class="empty">No hay registros recientes de calidad de agua.</div>
        @else
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>OD (mg/L)</th>
                        <th>pH</th>
                        <th>Temp (°C)</th>
                        <th>Salinidad (ppt)</th>
                    </tr>
                </thead>
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
        <div>
            @if (!empty($vm['footer']['footer_text']))
                {{ $vm['footer']['footer_text'] }} ·
            @endif
            {{ $vm['footer']['system_signature'] }}
        </div>
        <div>Generado: {{ $vm['footer']['generated_at'] }}</div>
    </footer>
</div>
</body>
</html>
