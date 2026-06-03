@extends('backoffice.layout')

@section('title', 'Backoffice · Ciclo #'.$vm['header']['cycle_id'])

@push('head')
<style>
    .detail-shell { display: grid; gap: 18px; }
    .detail-hero {
        background:
            linear-gradient(120deg, rgba(255, 255, 255, 0.97), rgba(247, 251, 255, 0.92)),
            radial-gradient(circle at top right, rgba(47, 143, 255, 0.12), transparent 32%);
    }
    .detail-hero__row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    .detail-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        font-weight: 800;
        font-size: .76rem;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .detail-status::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
    }
    .detail-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 8px;
        color: var(--muted);
        font-size: .9rem;
    }
    .actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }
    .action-btn {
        background: linear-gradient(145deg, #0f8d79, #0a6f95);
        color: #fff;
        border: none;
        border-radius: 18px;
        padding: 14px 16px;
        font-size: .94rem;
        font-weight: 800;
        box-shadow: 0 14px 28px rgba(11, 98, 109, 0.2);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 62px;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(11, 98, 109, 0.24);
    }
    .action-btn:after {
        content: "›";
        font-size: 1.2rem;
        opacity: .95;
    }
    .action-btn.disabled {
        background: linear-gradient(180deg, #dce3ea, #cfd6de);
        color: #61707f;
        box-shadow: none;
        pointer-events: none;
    }
    .kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
        gap: 12px;
    }
    .kpi {
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 16px;
        box-shadow: var(--shadow-soft);
    }
    .kpi .label {
        font-size: .74rem;
        color: var(--muted);
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .kpi .value {
        font-size: 1.46rem;
        font-weight: 800;
        letter-spacing: -.04em;
    }
    .chart-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: minmax(0, 1.22fr) minmax(320px, 1fr);
        align-items: stretch;
    }
    .chart-stack {
        display: grid;
        gap: 14px;
        min-width: 0;
    }
    .chart-card {
        padding: 18px;
        min-width: 0;
        overflow: hidden;
    }
    .chart-card--hero {
        min-height: 340px;
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .chart-title {
        margin: 0 0 4px;
        font-size: 1.06rem;
        font-weight: 800;
    }
    .chart-subtitle {
        color: var(--muted);
        font-size: .86rem;
    }
    .chart-box {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 230px;
        overflow: hidden;
    }
    .chart-card--hero .chart-box {
        min-height: 0;
        height: auto;
        aspect-ratio: 16 / 8.8;
        max-height: 360px;
    }
    .chart-box--compact {
        height: 220px;
        min-height: 220px;
    }
    canvas {
        width: 100% !important;
        max-width: 100%;
        height: 100% !important;
        display: block;
    }
    .support-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: minmax(0, 1.05fr) minmax(290px, .95fr);
    }
    .projection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
    }
    .projection-box {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px;
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        box-shadow: var(--shadow-soft);
    }
    .projection-box__label {
        color: var(--muted);
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 6px;
    }
    .projection-box__value {
        font-size: 1.18rem;
        font-weight: 800;
        letter-spacing: -.03em;
    }
    .projection-summary {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .projection-assumptions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .panel-title {
        margin: 0 0 12px;
        font-size: 1rem;
        font-weight: 800;
    }
    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .panel-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 800;
        font-size: .84rem;
    }
    .panel-link::after {
        content: "›";
        font-size: 1rem;
    }
    .secondary-action {
        border: 1px solid rgba(47, 143, 255, 0.16);
        background: rgba(47, 143, 255, 0.08);
        color: #1f5ea8;
    }
    .water-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .water-box {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px 10px;
        text-align: center;
        background: linear-gradient(180deg, #ffffff, #fbfdff);
    }
    .water-box .mini {
        color: var(--muted);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 8px;
    }
    .water-box .big {
        font-size: 1.22rem;
        font-weight: 800;
        letter-spacing: -.03em;
    }
    .legend {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        font-size: .8rem;
        color: var(--muted);
        margin-top: 10px;
    }
    .dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        display: inline-block;
        margin-right: 5px;
    }
    .alert-list {
        display: grid;
        gap: 10px;
        max-height: 340px;
        overflow: auto;
    }
    .alert-item {
        border: 1px solid var(--border);
        border-left-width: 5px;
        border-radius: 14px;
        padding: 12px;
        font-size: .88rem;
        background: linear-gradient(180deg, #ffffff, #fcfdff);
    }
    .alert-item__head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
        align-items: baseline;
    }
    .sev-critical { border-left-color: #c63636; }
    .sev-warning { border-left-color: #db8d1b; }
    .sev-info { border-left-color: #2f8fff; }
    .empty-copy {
        border: 1px dashed #c9d7e4;
        border-radius: 16px;
        padding: 18px;
        color: var(--muted);
        background: rgba(255, 255, 255, 0.58);
    }
    .exports-panel {
        display: grid;
        gap: 12px;
    }
    .exports-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 10px;
    }
    .export-btn {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(47, 143, 255, 0.16);
        background: rgba(47, 143, 255, 0.06);
        color: #1f5ea8;
        text-decoration: none;
        font-weight: 800;
        font-size: .86rem;
    }
    .export-btn::after {
        content: "›";
        font-size: 1rem;
    }
    .legend--growth {
        margin-top: 10px;
        justify-content: flex-start;
    }
    @media (max-width: 980px) {
        .chart-grid,
        .support-grid,
        .water-grid {
            grid-template-columns: 1fr;
        }
        .chart-card--hero {
            min-height: auto;
        }
        .chart-card--hero .chart-box {
            aspect-ratio: 16 / 10;
            max-height: none;
            min-height: 240px;
        }
    }
</style>
@endpush

@section('content')
    @php($canManageMortality = ($shell['permissions']['mortality.manage'] ?? false) && ! $shell['read_only_mode'])
    @php($canViewCosts = $shell['permissions']['costs.view'] ?? false)
    @php($canManageWater = ($shell['permissions']['water.manage'] ?? false) && ! $shell['read_only_mode'])
    @php($canViewReports = $shell['permissions']['reports.view'] ?? false)
    <div class="detail-shell">
        <div class="card card-soft detail-hero animate-enter-down">
            <div class="detail-hero__row">
                <div>
                    <h1 class="section-heading" style="margin-bottom:4px;">Detalle Operativo del Ciclo #{{ $vm['header']['cycle_id'] }}</h1>
                    <div class="section-subtitle">Vista central para control biológico, eficiencia operativa y seguimiento diario del ciclo.</div>
                    <div class="detail-meta">
                        <span>Finca {{ $vm['header']['farm_name'] }}</span>
                        <span>Piscina {{ $vm['header']['pond_code'] }}</span>
                        <span>Inicio {{ $vm['header']['started_at'] }}</span>
                    </div>
                </div>
                <span class="detail-status">{{ $vm['header']['status'] }}</span>
            </div>
        </div>

        <div class="actions animate-enter-down animate-enter-down-delay-1">
            @if($shell['permissions']['mortality.view'] ?? false)
                <a class="action-btn secondary-action" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/mortalities" title="{{ $canManageMortality ? 'Registrar y revisar mortalidad diaria' : 'Ver historial de mortalidad' }}">
                    {{ $canManageMortality ? 'Registrar mortalidad' : 'Ver mortalidad' }}
                </a>
            @endif
            @if($canViewCosts)
                <a class="action-btn secondary-action" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/costs" title="Ver costos del ciclo">
                    Ver costos
                </a>
            @endif
            @if($shell['permissions']['water.view'] ?? false)
                <a class="action-btn secondary-action" href="/backoffice/water?cycle={{ $vm['header']['cycle_id'] }}{{ $canManageWater ? '#register-water' : '' }}" title="{{ $canManageWater ? 'Registrar calidad de agua para este ciclo' : 'Ver registros de calidad de agua' }}">
                    {{ $canManageWater ? 'Registrar calidad de agua' : 'Ver calidad de agua' }}
                </a>
            @endif
            @foreach($vm['actions'] as $action)
                <a class="action-btn {{ $action['disabled'] ? 'disabled' : '' }}"
                   href="{{ $action['disabled'] ? '#' : $action['href'] }}"
                   title="{{ $action['disabled'] ? $action['disabled_reason'] : 'Acción rápida operativa' }}">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>

        @if($canViewReports)
            <div class="card exports-panel animate-enter-down animate-enter-down-delay-1">
                <div>
                    <h3 class="panel-title" style="margin-bottom:4px;">Reportes y Exportes</h3>
                    <div class="section-subtitle" style="font-size:.88rem;">Datasets listos para Excel o CSV, más acceso directo al reporte ejecutivo.</div>
                </div>
                <div class="exports-actions">
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/samplings.csv">Muestreos CSV</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/samplings.xlsx">Muestreos Excel</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/feed.csv">Alimentación CSV</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/feed.xlsx">Alimentación Excel</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/mortalities.csv">Mortalidad CSV</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/mortalities.xlsx">Mortalidad Excel</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/water.csv">Agua CSV</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/water.xlsx">Agua Excel</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/report">Reporte Ejecutivo</a>
                    <a class="export-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/report" target="_blank" rel="noopener">Imprimir</a>
                </div>
            </div>
        @endif

        <div class="kpis animate-enter-down animate-enter-down-delay-2">
            <div class="kpi"><div class="label">Biomasa estimada</div><div class="value">{{ number_format($vm['kpis']['biomass_kg'],2) }} kg</div></div>
            <div class="kpi"><div class="label">Peso promedio</div><div class="value">{{ $vm['kpis']['latest_pp_grams'] !== null ? number_format($vm['kpis']['latest_pp_grams'],2).' g' : 'N/D' }}</div></div>
            <div class="kpi"><div class="label">FCR</div><div class="value">{{ number_format($vm['kpis']['fcr'],3) }}</div></div>
            <div class="kpi"><div class="label">Alimento acum.</div><div class="value">{{ number_format($vm['kpis']['total_feed_kg'],2) }} kg</div></div>
            <div class="kpi"><div class="label">Costo acum.</div><div class="value">${{ number_format($vm['kpis']['total_cost_usd'],2) }}</div></div>
            <div class="kpi"><div class="label">Alertas abiertas</div><div class="value">{{ $vm['kpis']['open_alerts'] }}</div></div>
        </div>

        <div class="chart-grid">
            <div class="card chart-card chart-card--hero animate-enter-down animate-enter-down-delay-2">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">Crecimiento del Ciclo</h3>
                        <div class="chart-subtitle">Biomasa estimada y peso promedio en una sola lectura para entender avance biológico real.</div>
                    </div>
                </div>
                <div class="chart-box animate-enter-down animate-enter-down-delay-3"><canvas id="biomassChart"></canvas></div>
                <div class="legend legend--growth">
                    <span><span class="dot" style="background:#0c7a6a;"></span>Biomasa estimada</span>
                    <span><span class="dot" style="background:#2f8fff;"></span>Peso promedio</span>
                </div>
            </div>

            <div class="chart-stack">
                <div class="card chart-card animate-enter-down animate-enter-down-delay-2">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">Alimentación Semanal</h3>
                            <div class="chart-subtitle">Carga de alimento por semana para conectar consumo, biomasa y costo operativo.</div>
                        </div>
                    </div>
                    <div class="chart-box animate-enter-down animate-enter-down-delay-3"><canvas id="feedChart"></canvas></div>
                </div>

                <div class="card chart-card animate-enter-down animate-enter-down-delay-3">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">Distribución de Costos</h3>
                            <div class="chart-subtitle">Peso relativo entre alimento y operación acumulada.</div>
                        </div>
                    </div>
                    <div class="chart-box chart-box--compact animate-enter-down animate-enter-down-delay-3"><canvas id="costChart"></canvas></div>
                    <div class="legend">
                        <span><span class="dot" style="background:#2f8fff;"></span>Alimento</span>
                        <span><span class="dot" style="background:#0c7a6a;"></span>Operación</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card animate-enter-down animate-enter-down-delay-2">
            <div class="projection-summary">
                <div>
                    <h3 class="panel-title" style="margin-bottom:4px;">Proyección de Cosecha</h3>
                    <div class="section-subtitle" style="font-size:.88rem;">Escenario base explicable para decisión operativa y económica con los datos actuales del ciclo.</div>
                </div>
                <div class="projection-assumptions">
                    <span class="chip">Meta {{ number_format($vm['projection']['projection']['target_pp_grams'], 1) }} g</span>
                    <span class="chip">Venta ${{ number_format($vm['projection']['assumptions']['sale_price_per_lb'], 2) }}/lb</span>
                    <span class="chip">Growth {{ $vm['projection']['assumptions']['growth_g_per_week'] !== null ? number_format($vm['projection']['assumptions']['growth_g_per_week'], 2).' g/sem' : 'N/D' }}</span>
                </div>
            </div>

            @if(
                $vm['projection']['projection']['projected_harvest_date'] === null &&
                $vm['projection']['projection']['projected_biomass_kg'] === null &&
                $vm['projection']['projection']['projected_profit'] === null
            )
                <div class="empty-copy">Aún no hay datos suficientes para una proyección confiable.</div>
            @else
                <div class="projection-grid">
                    <div class="projection-box">
                        <div class="projection-box__label">Fecha estimada</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_harvest_date'] ?? 'N/D' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">Biomasa proyectada</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_biomass_kg'] !== null ? number_format($vm['projection']['projection']['projected_biomass_kg'], 2).' kg' : 'N/D' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">Libras proyectadas</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_total_lbs'] !== null ? number_format($vm['projection']['projection']['projected_total_lbs'], 2).' lb' : 'N/D' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">Ingreso proyectado</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_revenue'] !== null ? '$'.number_format($vm['projection']['projection']['projected_revenue'], 2) : 'N/D' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">Costo proyectado</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_cost'] !== null ? '$'.number_format($vm['projection']['projection']['projected_cost'], 2) : 'N/D' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">Utilidad proyectada</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_profit'] !== null ? '$'.number_format($vm['projection']['projection']['projected_profit'], 2) : 'N/D' }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="support-grid">
            <div class="card animate-enter-down animate-enter-down-delay-3">
                <div class="panel-head">
                    <h3 class="panel-title" style="margin:0;">Timeline de Alertas</h3>
                    <a href="/backoffice/alerts?cycle={{ $vm['header']['cycle_id'] }}" class="panel-link">Ver todas las alertas</a>
                </div>
                <div class="alert-list">
                    @forelse($vm['charts']['alerts_timeline'] as $alert)
                        <div class="alert-item sev-{{ $alert['severity'] }}">
                            <div class="alert-item__head">
                                <strong>{{ $alert['title'] }}</strong>
                                <span class="muted">{{ $alert['detected_at'] }}</span>
                            </div>
                            <div>{{ $alert['message'] }}</div>
                        </div>
                    @empty
                        <div class="empty-copy">No hay alertas activas para este ciclo.</div>
                    @endforelse
                </div>
            </div>

            <div class="card animate-enter-down animate-enter-down-delay-3">
                <h3 class="panel-title">Calidad de Agua Reciente</h3>
                @if($vm['water_quality_latest']['avg_do'] === null && $vm['water_quality_latest']['avg_ph'] === null && $vm['water_quality_latest']['avg_temp'] === null)
                    <div class="empty-copy">No hay registros de calidad de agua.</div>
                @else
                    <div class="water-grid">
                        <div class="water-box"><div class="mini">DO</div><div class="big">{{ $vm['water_quality_latest']['avg_do'] !== null ? number_format($vm['water_quality_latest']['avg_do'],2) : 'N/D' }}</div></div>
                        <div class="water-box"><div class="mini">pH</div><div class="big">{{ $vm['water_quality_latest']['avg_ph'] !== null ? number_format($vm['water_quality_latest']['avg_ph'],2) : 'N/D' }}</div></div>
                        <div class="water-box"><div class="mini">Temp °C</div><div class="big">{{ $vm['water_quality_latest']['avg_temp'] !== null ? number_format($vm['water_quality_latest']['avg_temp'],2) : 'N/D' }}</div></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const biomassData = @json($vm['charts']['biomass_vs_time']);
        const feedData = @json($vm['charts']['feed_weekly']);
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

        function drawGrowthChart(id, points) {
            const { c, ctx } = setupCanvas(id);
            const w = c.clientWidth;
            const h = c.clientHeight;
            const padTop = 18;
            const padRight = 54;
            const padBottom = 38;
            const padLeft = 52;
            const iw = w - padLeft - padRight;
            const ih = h - padTop - padBottom;

            ctx.clearRect(0, 0, w, h);

            if (points.length < 2) {
                return;
            }

            const biomass = points.map(point => Number(point.biomass_kg) || 0);
            const pp = points.map(point => Number(point.pp_grams) || 0);
            const maxBiomass = Math.max(...biomass, 1);
            const maxPp = Math.max(...pp, 1);
            const gridLines = 4;

            ctx.strokeStyle = '#dce7f1';
            ctx.fillStyle = '#70859a';
            ctx.font = '12px "IBM Plex Sans", sans-serif';

            for (let i = 0; i <= gridLines; i++) {
                const ratio = i / gridLines;
                const y = padTop + ih - (ih * ratio);
                ctx.beginPath();
                ctx.moveTo(padLeft, y);
                ctx.lineTo(w - padRight, y);
                ctx.stroke();

                const leftLabel = Math.round((maxBiomass * ratio) / 250) * 250;
                const rightLabel = ((maxPp * ratio)).toFixed(1);
                ctx.fillText(String(leftLabel), 8, y + 4);
                ctx.fillText(rightLabel, w - padRight + 10, y + 4);
            }

            const xStep = iw / (points.length - 1);
            const coords = points.map((point, index) => ({
                x: padLeft + (xStep * index),
                biomassY: padTop + ih - ((Number(point.biomass_kg) || 0) / maxBiomass) * ih,
                ppY: padTop + ih - ((Number(point.pp_grams) || 0) / maxPp) * ih,
                label: point.date.slice(5),
            }));

            ctx.strokeStyle = '#0c7a6a';
            ctx.lineWidth = 2.5;
            ctx.beginPath();
            coords.forEach((point, index) => {
                if (index === 0) {
                    ctx.moveTo(point.x, point.biomassY);
                } else {
                    ctx.lineTo(point.x, point.biomassY);
                }
            });
            ctx.stroke();

            ctx.fillStyle = '#0c7a6a';
            coords.forEach(point => {
                ctx.beginPath();
                ctx.arc(point.x, point.biomassY, 3.5, 0, Math.PI * 2);
                ctx.fill();
            });

            ctx.strokeStyle = '#2f8fff';
            ctx.lineWidth = 2;
            ctx.setLineDash([6, 4]);
            ctx.beginPath();
            coords.forEach((point, index) => {
                if (index === 0) {
                    ctx.moveTo(point.x, point.ppY);
                } else {
                    ctx.lineTo(point.x, point.ppY);
                }
            });
            ctx.stroke();
            ctx.setLineDash([]);

            ctx.fillStyle = '#2f8fff';
            coords.forEach(point => {
                ctx.beginPath();
                ctx.arc(point.x, point.ppY, 3, 0, Math.PI * 2);
                ctx.fill();
            });

            ctx.fillStyle = '#70859a';
            ctx.textAlign = 'center';
            coords.forEach((point, index) => {
                if (index === 0 || index === coords.length - 1 || index % 2 === 1) {
                    ctx.fillText(point.label, point.x, h - 12);
                }
            });
            ctx.textAlign = 'start';
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

        function drawBars(id, points, key, color) {
            const { c, ctx } = setupCanvas(id);
            const w = c.clientWidth;
            const h = c.clientHeight;
            const padTop = 18;
            const padRight = 18;
            const padBottom = 34;
            const padLeft = 42;
            const iw = w - padLeft - padRight;
            const ih = h - padTop - padBottom;
            const values = points.map(point => Number(point[key]) || 0);
            const max = Math.max(...values, 1);

            ctx.clearRect(0, 0, w, h);
            ctx.strokeStyle = '#dce7f1';
            ctx.fillStyle = '#70859a';
            ctx.font = '12px "IBM Plex Sans", sans-serif';

            for (let i = 0; i < 4; i++) {
                const ratio = i / 3;
                const y = padTop + ih - (ih * ratio);
                ctx.beginPath();
                ctx.moveTo(padLeft, y);
                ctx.lineTo(w - padRight, y);
                ctx.stroke();
                ctx.fillText(String(Math.round(max * ratio)), 8, y + 4);
            }

            if (points.length === 0) {
                return;
            }

            const slot = iw / points.length;
            const barWidth = Math.min(34, slot * 0.58);

            points.forEach((point, index) => {
                const value = Number(point[key]) || 0;
                const barHeight = (value / max) * ih;
                const x = padLeft + (slot * index) + ((slot - barWidth) / 2);
                const y = padTop + ih - barHeight;

                ctx.fillStyle = color;
                ctx.fillRect(x, y, barWidth, barHeight);

                ctx.fillStyle = '#70859a';
                ctx.textAlign = 'center';
                ctx.fillText(point.week.slice(-3), x + (barWidth / 2), h - 10);
                ctx.textAlign = 'start';
            });
        }

        drawGrowthChart('biomassChart', biomassData);
        drawBars('feedChart', feedData, 'feed_kg', '#2f8fff');
        drawDonut('costChart', costData);
    </script>
@endsection
