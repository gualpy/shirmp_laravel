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
    .detail-hero { padding: 14px 18px; }
    .detail-hero__row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    .detail-hero__title {
        margin: 0;
        font-size: 1.28rem;
        font-weight: 800;
        letter-spacing: -.03em;
    }
    .detail-hero__dot { color: var(--muted); font-weight: 600; margin: 0 2px; }
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
    .detail-status--harvested { background: rgba(100, 116, 133, 0.12); color: #4d5c6b; }
    .detail-status--cancelled { background: rgba(198, 54, 54, 0.1); color: #c63636; }
    .detail-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 6px;
        color: var(--muted);
        font-size: .87rem;
    }
    .ops-section-title {
        margin: 0 0 10px;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--muted);
    }
    .actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }
    .action-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 12px 14px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 56px;
        box-shadow: var(--shadow-soft);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .action-card:hover {
        transform: translateY(-1px);
        border-color: rgba(12, 122, 106, 0.35);
        box-shadow: 0 10px 22px rgba(20, 40, 50, 0.1);
    }
    .action-card:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    .action-card__icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        background: rgba(12, 122, 106, 0.09);
    }
    .action-card--secondary .action-card__icon {
        background: rgba(47, 143, 255, 0.12);
    }
    .action-card__body {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }
    .action-card__name {
        font-weight: 800;
        font-size: .94rem;
        color: var(--text);
        line-height: 1.2;
    }
    .action-card__cta {
        font-weight: 700;
        font-size: .74rem;
        color: var(--muted);
    }
    .action-card__cta::after {
        content: "›";
        margin-left: 3px;
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
    .kpi__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .kpi .label {
        font-size: .74rem;
        color: var(--muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .kpi__icon {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
    }
    .kpi__icon svg { width: 15px; height: 15px; }
    .kpi--alert .kpi__icon {
        background: rgba(198, 54, 54, 0.1);
        color: #c63636;
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
        height: 230px;
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
    .reports-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 14px 18px;
    }
    .reports-panel__title { margin: 0; font-size: 1.02rem; font-weight: 800; }
    .reports-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .reports-select-wrap { position: relative; display: inline-flex; }
    .reports-select-wrap::after {
        content: "▾";
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: .68rem;
        color: #1f5ea8;
        pointer-events: none;
    }
    .reports-select,
    .reports-btn {
        height: 38px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid rgba(47, 143, 255, 0.18);
        background: rgba(47, 143, 255, 0.06);
        color: #1f5ea8;
        font: inherit;
        font-weight: 800;
        font-size: .84rem;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: background .12s ease;
    }
    .reports-select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 28px;
    }
    .reports-select:hover,
    .reports-btn:hover { background: rgba(47, 143, 255, 0.13); }
    .reports-select:focus-visible,
    .reports-btn:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
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
    .tab-nav {
        display: flex;
        gap: 4px;
        padding: 4px;
        background: var(--surface, #f4f7fb);
        border-radius: 14px;
        margin-bottom: 16px;
        border: 1px solid var(--border, #dce7f1);
        flex-wrap: wrap;
    }
    .tab-btn {
        padding: 8px 22px;
        border-radius: 10px;
        border: none;
        background: transparent;
        color: var(--muted, #647485);
        font: inherit;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, color .15s, box-shadow .15s;
    }
    .tab-btn.active {
        background: #fff;
        color: var(--text, #1a2636);
        box-shadow: 0 1px 6px rgba(0,0,0,.08);
    }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    @php
        $canManageMortality = ($shell['permissions']['mortality.manage'] ?? false) && ! $shell['read_only_mode'];
        $canManageFeeding = ($shell['permissions']['feeding.manage'] ?? false) && ! $shell['read_only_mode'];
        $canManageSampling = ($shell['permissions']['sampling.manage'] ?? false) && ! $shell['read_only_mode'];
        $canManageHarvest = ($shell['permissions']['harvest.manage'] ?? false) && ! $shell['read_only_mode'];
        $canViewCosts = $shell['permissions']['costs.view'] ?? false;
        $canManageWater = ($shell['permissions']['water.manage'] ?? false) && ! $shell['read_only_mode'];
        $canViewReports = $shell['permissions']['reports.view'] ?? false;

        $statusLabels = [
            'active' => __('cycle.status_active'),
            'harvested' => __('cycle.status_harvested'),
            'cancelled' => __('cycle.status_cancelled'),
        ];
        $statusValue = $vm['header']['status'];
        $statusLabel = $statusLabels[$statusValue] ?? strtoupper($statusValue);

        $opsActions = [];
        if ($shell['permissions']['mortality.view'] ?? false) {
            $opsActions[] = [
                'href' => "/backoffice/cycles/{$vm['header']['cycle_id']}/mortalities",
                'icon' => '💀',
                'name' => __('cycle.action_name_mortality'),
                'cta' => $canManageMortality ? __('cycle.action_cta_register') : __('cycle.action_cta_view'),
                'tooltip' => $canManageMortality ? __('cycle.record_mortality_tooltip') : __('cycle.view_mortality_tooltip'),
                'group' => 'primary',
            ];
        }
        if ($shell['permissions']['feeding.view'] ?? false) {
            $opsActions[] = [
                'href' => "/backoffice/cycles/{$vm['header']['cycle_id']}/feeding",
                'icon' => '🍽️',
                'name' => __('cycle.action_name_feeding'),
                'cta' => $canManageFeeding ? __('cycle.action_cta_register') : __('cycle.action_cta_view'),
                'tooltip' => $canManageFeeding ? __('cycle.record_feeding_tooltip') : __('cycle.view_feeding_tooltip'),
                'group' => 'primary',
            ];
        }
        if ($shell['permissions']['sampling.view'] ?? false) {
            $opsActions[] = [
                'href' => "/backoffice/cycles/{$vm['header']['cycle_id']}/sampling",
                'icon' => '📏',
                'name' => __('cycle.action_name_sampling'),
                'cta' => $canManageSampling ? __('cycle.action_cta_register') : __('cycle.action_cta_view'),
                'tooltip' => $canManageSampling ? __('cycle.record_sampling_tooltip') : __('cycle.view_sampling_tooltip'),
                'group' => 'primary',
            ];
        }
        if ($shell['permissions']['water.view'] ?? false) {
            $opsActions[] = [
                'href' => "/backoffice/water?cycle={$vm['header']['cycle_id']}".($canManageWater ? '#register-water' : ''),
                'icon' => '💧',
                'name' => __('cycle.action_name_water'),
                'cta' => $canManageWater ? __('cycle.action_cta_register') : __('cycle.action_cta_view'),
                'tooltip' => $canManageWater ? __('cycle.record_water_tooltip') : __('cycle.view_water_tooltip'),
                'group' => 'primary',
            ];
        }
        if ($shell['permissions']['harvest.view'] ?? false) {
            $opsActions[] = [
                'href' => "/backoffice/cycles/{$vm['header']['cycle_id']}/harvest",
                'icon' => '🦐',
                'name' => __('cycle.action_name_harvest'),
                'cta' => $canManageHarvest ? __('cycle.action_cta_register') : __('cycle.action_cta_view'),
                'tooltip' => $canManageHarvest ? __('cycle.record_harvest_tooltip') : __('cycle.view_harvest_tooltip'),
                'group' => 'secondary',
            ];
        }
        if ($canViewCosts) {
            $opsActions[] = [
                'href' => "/backoffice/cycles/{$vm['header']['cycle_id']}/costs",
                'icon' => '💰',
                'name' => __('cycle.action_name_costs'),
                'cta' => __('cycle.action_cta_view_costs'),
                'tooltip' => __('cycle.view_costs_tooltip'),
                'group' => 'secondary',
            ];
        }
    @endphp
    <div class="detail-shell">
        <div class="card card-soft detail-hero animate-enter-down">
            <div class="detail-hero__row">
                <div>
                    <h1 class="detail-hero__title">{{ __('cycle.cycle_short') }} #{{ $vm['header']['cycle_id'] }} <span class="detail-hero__dot">·</span> {{ __('cycle.pond') }} {{ $vm['header']['pond_code'] }}</h1>
                    <div class="detail-meta">
                        <span>{{ __('cycle.farm') }} {{ $vm['header']['farm_name'] }}</span>
                        <span>{{ __('cycle.started') }} {{ $vm['header']['started_at'] }}</span>
                        @if($vm['header']['density_pl_m2'] !== null)
                            <span>{{ __('cycle.density') }} {{ number_format($vm['header']['density_pl_m2'], 2) }} PL/m²</span>
                        @endif
                        @if($vm['header']['supplier_name'] !== null)
                            <span>{{ __('cycle.hatchery') }} {{ $vm['header']['supplier_name'] }}</span>
                        @endif
                    </div>
                </div>
                <span class="detail-status detail-status--{{ $statusValue }}">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="animate-enter-down animate-enter-down-delay-1">
            <h2 class="ops-section-title">{{ __('cycle.ops_section_title') }}</h2>
            <div class="actions">
                @foreach($opsActions as $action)
                    <a class="action-card action-card--{{ $action['group'] }}" href="{{ $action['href'] }}" title="{{ $action['tooltip'] }}">
                        <span class="action-card__icon" aria-hidden="true">{{ $action['icon'] }}</span>
                        <span class="action-card__body">
                            <span class="action-card__name">{{ $action['name'] }}</span>
                            <span class="action-card__cta">{{ $action['cta'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        @if($canViewReports)
            <div class="card reports-panel animate-enter-down animate-enter-down-delay-1">
                <h3 class="reports-panel__title">{{ __('cycle.reports_title') }}</h3>
                <div class="reports-toolbar">
                    <span class="reports-select-wrap">
                        <select id="reportsExcelSelect" class="reports-select" aria-label="{{ __('cycle.reports_excel_placeholder') }}">
                            <option value="" selected>📗 {{ __('cycle.reports_excel_placeholder') }}</option>
                            <option value="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/samplings.xlsx">{{ __('cycle.report_row_samplings') }}</option>
                            <option value="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/feed.xlsx">{{ __('cycle.report_row_feeding') }}</option>
                            <option value="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/mortalities.xlsx">{{ __('cycle.report_row_mortality') }}</option>
                            <option value="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/water.xlsx">{{ __('cycle.report_row_water') }}</option>
                        </select>
                    </span>
                    <a class="reports-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/report">📊 {{ __('cycle.executive_report') }}</a>
                    <a class="reports-btn" href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/report" target="_blank" rel="noopener">🖨 {{ __('cycle.print') }}</a>
                </div>
            </div>
        @endif

        <nav class="tab-nav" role="tablist">
            <button class="tab-btn active" data-tab="tab-resumen" role="tab">{{ __('cycle.tab_resumen') }}</button>
            <button class="tab-btn" data-tab="tab-proyeccion" role="tab">{{ __('cycle.tab_proyeccion') }}</button>
            <button class="tab-btn" data-tab="tab-soporte" role="tab">{{ __('cycle.tab_soporte') }}</button>
        </nav>

        <div id="tab-resumen" class="tab-panel active">
        <div class="kpis animate-enter-down animate-enter-down-delay-2">
            <div class="kpi">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.stocked_pl') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v-8"/><path d="M12 12c0-4 3-6 6-6 0 4-2 6-6 6z"/><path d="M12 12C12 8 9 6 6 6c0 4 2 6 6 6z"/></svg></span>
                </div>
                <div class="value">{{ $vm['kpis']['stocked_pl'] !== null ? number_format($vm['kpis']['stocked_pl']) : 'N/A' }}</div>
            </div>
            <div class="kpi">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.estimated_biomass') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><path d="M5 22l3-12h8l3 12"/><path d="M5 22h14"/></svg></span>
                </div>
                <div class="value">{{ number_format($vm['kpis']['biomass_kg'],2) }} kg</div>
            </div>
            <div class="kpi">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.average_weight') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></svg></span>
                </div>
                <div class="value">{{ $vm['kpis']['latest_pp_grams'] !== null ? number_format($vm['kpis']['latest_pp_grams'],2).' g' : 'N/A' }}</div>
            </div>
            <div class="kpi">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.fcr') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h13l-3-3M17 17H4l3 3"/></svg></span>
                </div>
                <div class="value">{{ number_format($vm['kpis']['fcr'],3) }}</div>
            </div>
            <div class="kpi">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.accum_feed') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11h16"/><path d="M5 11a7 7 0 0 0 14 0"/><path d="M8 15l-1 4M16 15l1 4M6 19h12"/></svg></span>
                </div>
                <div class="value">{{ number_format($vm['kpis']['total_feed_kg'],2) }} kg</div>
            </div>
            <div class="kpi">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.accum_cost') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5c0-1.4-1.3-2.5-3-2.5s-3 1-3 2.2c0 3 6 1.5 6 4.5 0 1.4-1.3 2.3-3 2.3s-3-1-3-2.3"/></svg></span>
                </div>
                <div class="value">${{ number_format($vm['kpis']['total_cost_usd'],2) }}</div>
            </div>
            <div class="kpi{{ $vm['kpis']['open_alerts'] > 0 ? ' kpi--alert' : '' }}">
                <div class="kpi__head">
                    <div class="label">{{ __('cycle.open_alerts') }}</div>
                    <span class="kpi__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M10 20a2 2 0 0 0 4 0"/></svg></span>
                </div>
                <div class="value">{{ $vm['kpis']['open_alerts'] }}</div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="card chart-card chart-card--hero animate-enter-down animate-enter-down-delay-2">
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">{{ __('cycle.cycle_growth') }}</h3>
                        <div class="chart-subtitle">{{ __('cycle.cycle_growth_subtitle') }}</div>
                    </div>
                </div>
                <div class="chart-box animate-enter-down animate-enter-down-delay-3"><canvas id="biomassChart"></canvas></div>
                <div class="legend legend--growth">
                    <span><span class="dot" style="background:#0c7a6a;"></span>{{ __('cycle.estimated_biomass') }}</span>
                    <span><span class="dot" style="background:#2f8fff;"></span>{{ __('cycle.average_weight') }}</span>
                </div>
            </div>

            <div class="chart-stack">
                <div class="card chart-card animate-enter-down animate-enter-down-delay-2">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">{{ __('cycle.weekly_feeding') }}</h3>
                            <div class="chart-subtitle">{{ __('cycle.weekly_feeding_subtitle') }}</div>
                        </div>
                    </div>
                    <div class="chart-box animate-enter-down animate-enter-down-delay-3"><canvas id="feedChart"></canvas></div>
                </div>

                <div class="card chart-card animate-enter-down animate-enter-down-delay-3">
                    <div class="chart-header">
                        <div>
                            <h3 class="chart-title">{{ __('cycle.cost_distribution') }}</h3>
                            <div class="chart-subtitle">{{ __('cycle.cost_distribution_subtitle') }}</div>
                        </div>
                    </div>
                    <div class="chart-box chart-box--compact animate-enter-down animate-enter-down-delay-3"><canvas id="costChart"></canvas></div>
                    <div class="legend">
                        <span><span class="dot" style="background:#2f8fff;"></span>{{ __('cycle.feed_legend') }}</span>
                        <span><span class="dot" style="background:#0c7a6a;"></span>{{ __('cycle.operations_legend') }}</span>
                    </div>
                </div>
            </div>
        </div>
        </div>{{-- /tab-resumen --}}

        <div id="tab-proyeccion" class="tab-panel">
        <div class="card animate-enter-down animate-enter-down-delay-2">
            <div class="projection-summary">
                <div>
                    <h3 class="panel-title" style="margin-bottom:4px;">{{ __('cycle.harvest_projection') }}</h3>
                    <div class="section-subtitle" style="font-size:.88rem;">{{ __('cycle.harvest_projection_subtitle') }}</div>
                </div>
                <div class="projection-assumptions">
                    <span class="chip">{{ __('cycle.target_label') }} {{ number_format($vm['projection']['projection']['target_pp_grams'], 1) }} g</span>
                    <span class="chip">{{ __('cycle.sale_label') }} ${{ number_format($vm['projection']['assumptions']['sale_price_per_lb'], 2) }}/lb</span>
                    <span class="chip">{{ __('cycle.growth_label') }} {{ $vm['projection']['assumptions']['growth_g_per_week'] !== null ? number_format($vm['projection']['assumptions']['growth_g_per_week'], 2).' g/week' : 'N/A' }}</span>
                </div>
            </div>

            @if(
                $vm['projection']['projection']['projected_harvest_date'] === null &&
                $vm['projection']['projection']['projected_biomass_kg'] === null &&
                $vm['projection']['projection']['projected_profit'] === null
            )
                <div class="empty-copy">{{ __('cycle.no_projection') }}</div>
            @else
                <div class="projection-grid">
                    <div class="projection-box">
                        <div class="projection-box__label">{{ __('cycle.projected_date') }}</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_harvest_date'] ?? 'N/A' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">{{ __('cycle.projected_biomass') }}</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_biomass_kg'] !== null ? number_format($vm['projection']['projection']['projected_biomass_kg'], 2).' kg' : 'N/A' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">{{ __('cycle.projected_pounds') }}</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_total_lbs'] !== null ? number_format($vm['projection']['projection']['projected_total_lbs'], 2).' lb' : 'N/A' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">{{ __('cycle.projected_revenue') }}</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_revenue'] !== null ? '$'.number_format($vm['projection']['projection']['projected_revenue'], 2) : 'N/A' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">{{ __('cycle.projected_cost') }}</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_cost'] !== null ? '$'.number_format($vm['projection']['projection']['projected_cost'], 2) : 'N/A' }}</div>
                    </div>
                    <div class="projection-box">
                        <div class="projection-box__label">{{ __('cycle.projected_profit') }}</div>
                        <div class="projection-box__value">{{ $vm['projection']['projection']['projected_profit'] !== null ? '$'.number_format($vm['projection']['projection']['projected_profit'], 2) : 'N/A' }}</div>
                    </div>
                </div>
            @endif
        </div>
        </div>{{-- /tab-proyeccion --}}

        <div id="tab-soporte" class="tab-panel">
        <div class="support-grid">
            <div class="card animate-enter-down animate-enter-down-delay-3">
                <div class="panel-head">
                    <h3 class="panel-title" style="margin:0;">{{ __('cycle.alert_timeline') }}</h3>
                    <a href="/backoffice/alerts?cycle={{ $vm['header']['cycle_id'] }}" class="panel-link">{{ __('cycle.view_all_alerts') }}</a>
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
                        <div class="empty-copy">{{ __('cycle.no_active_alerts') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="card animate-enter-down animate-enter-down-delay-3">
                <h3 class="panel-title">{{ __('cycle.recent_water') }}</h3>
                @if($vm['water_quality_latest']['avg_do'] === null && $vm['water_quality_latest']['avg_ph'] === null && $vm['water_quality_latest']['avg_temp'] === null)
                    <div class="empty-copy">{{ __('cycle.no_water_records') }}</div>
                @else
                    <div class="water-grid">
                        <div class="water-box"><div class="mini">DO</div><div class="big">{{ $vm['water_quality_latest']['avg_do'] !== null ? number_format($vm['water_quality_latest']['avg_do'],2) : 'N/A' }}</div></div>
                        <div class="water-box"><div class="mini">pH</div><div class="big">{{ $vm['water_quality_latest']['avg_ph'] !== null ? number_format($vm['water_quality_latest']['avg_ph'],2) : 'N/A' }}</div></div>
                        <div class="water-box"><div class="mini">Temp °C</div><div class="big">{{ $vm['water_quality_latest']['avg_temp'] !== null ? number_format($vm['water_quality_latest']['avg_temp'],2) : 'N/A' }}</div></div>
                    </div>
                @endif
            </div>
        </div>
        </div>{{-- /tab-soporte --}}
    </div>

    <script>
        const biomassData = @json($vm['charts']['biomass_vs_time']);
        const feedData    = @json($vm['charts']['feed_weekly']);
        const costData    = @json($vm['charts']['cost_distribution']);

        Chart.defaults.font.family = '"IBM Plex Sans", sans-serif';
        Chart.defaults.font.size   = 12;
        Chart.defaults.color       = '#70859a';

        // Crecimiento del ciclo — dual Y-axis line chart
        new Chart(document.getElementById('biomassChart'), {
            type: 'line',
            data: {
                labels: biomassData.map(p => p.date ? p.date.slice(5) : ''),
                datasets: [
                    {
                        label: '{{ __("cycle.estimated_biomass") }}',
                        data: biomassData.map(p => Number(p.biomass_kg) || 0),
                        borderColor: '#0c7a6a',
                        backgroundColor: 'rgba(12,122,106,0.07)',
                        borderWidth: 2.5,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#0c7a6a',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: '{{ __("cycle.average_weight") }}',
                        data: biomassData.map(p => Number(p.pp_grams) || 0),
                        borderColor: '#2f8fff',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [6, 4],
                        pointRadius: 3,
                        pointBackgroundColor: '#2f8fff',
                        tension: 0.3,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.datasetIndex === 0
                                ? `{{ __("cycle.estimated_biomass") }}: ${ctx.parsed.y.toFixed(2)} kg`
                                : `{{ __("cycle.average_weight") }}: ${ctx.parsed.y.toFixed(2)} g`,
                        },
                    },
                },
                scales: {
                    x:  { grid: { color: '#dce7f1' }, ticks: { maxTicksLimit: 10 } },
                    y:  { position: 'left',  grid: { color: '#dce7f1' }, ticks: { callback: v => v + ' kg' } },
                    y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => v + ' g' } },
                },
            },
        });

        // Alimentación semanal — bar chart
        new Chart(document.getElementById('feedChart'), {
            type: 'bar',
            data: {
                labels: feedData.map(p => p.week ? p.week.slice(-3) : ''),
                datasets: [{
                    label: '{{ __("cycle.accum_feed") }}',
                    data: feedData.map(p => Number(p.feed_kg) || 0),
                    backgroundColor: 'rgba(47,143,255,0.72)',
                    borderColor: '#2f8fff',
                    borderWidth: 1,
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#dce7f1' } },
                    y: { grid: { color: '#dce7f1' }, ticks: { callback: v => v + ' kg' } },
                },
            },
        });

        // Distribución de costos — doughnut
        new Chart(document.getElementById('costChart'), {
            type: 'doughnut',
            data: {
                labels: costData.map(p => p.label),
                datasets: [{
                    data: costData.map(p => Number(p.value) || 0),
                    backgroundColor: ['#2f8fff', '#0c7a6a', '#db8d1b', '#c63636'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: $${ctx.parsed.toFixed(2)}`,
                        },
                    },
                },
            },
        });

        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
                document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active');
            });
        });

        const reportsExcelSelect = document.getElementById('reportsExcelSelect');
        if (reportsExcelSelect) {
            reportsExcelSelect.addEventListener('change', function() {
                if (this.value) {
                    window.location.href = this.value;
                    this.value = '';
                }
            });
        }
    </script>
@endsection
