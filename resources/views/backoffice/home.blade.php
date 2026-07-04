@extends('backoffice.layout')

@section('title', 'Backoffice · Dashboard')

@push('head')
<style>
    .hero-card {
        margin-bottom: 18px;
        background:
            linear-gradient(120deg, rgba(255, 255, 255, 0.96), rgba(247, 251, 255, 0.88)),
            radial-gradient(circle at top right, rgba(47, 143, 255, 0.1), transparent 34%);
    }
    .hero-card__row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }
    .kpi-panel {
        padding: 16px 18px;
        border-radius: 18px;
        border: 1px solid rgba(215, 226, 237, 0.92);
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        box-shadow: var(--shadow-soft);
    }
    .kpi-panel__label {
        color: var(--muted);
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .09em;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .kpi-panel__value {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -.04em;
    }
    .farms-card__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .farm-list {
        display: grid;
        gap: 10px;
    }
    .farm-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        box-shadow: var(--shadow-soft);
    }
    .farm-item__title {
        margin: 0 0 4px;
        font-size: 1rem;
        font-weight: 800;
    }
    .farm-item__meta {
        color: var(--muted);
        font-size: .86rem;
    }
    .farm-item__cta {
        white-space: nowrap;
        color: var(--primary);
        text-decoration: none;
        font-weight: 800;
        font-size: .86rem;
    }
    .empty-state {
        border: 1px dashed #c9d7e4;
        border-radius: 16px;
        padding: 22px 18px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
    }
    @media (max-width: 980px) {
        .hero-card__row,
        .farms-card__header,
        .farm-item {
            align-items: stretch;
        }
        .farm-item {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
    <div class="card card-soft hero-card animate-enter-down">
        <div class="hero-card__row">
            <div>
                <h1 class="section-heading">{{ __('dashboard.title') }}</h1>
                <div class="section-subtitle">{{ __('dashboard.subtitle') }}</div>
            </div>
            <a href="{{ $vm['cta']['href'] }}" class="cta-primary">
                {{ $vm['cta']['label'] }}
            </a>
        </div>
    </div>

    @if($vm['dashboard_enabled'] && $vm['kpis'] !== null)
        <div class="kpi-grid">
            <div class="kpi-panel animate-enter-down animate-enter-down-delay-1">
                <div class="kpi-panel__label">{{ __('dashboard.active_cycles') }}</div>
                <div class="kpi-panel__value">{{ $vm['kpis']['active_cycles'] }}</div>
            </div>
            <div class="kpi-panel animate-enter-down animate-enter-down-delay-1">
                <div class="kpi-panel__label">{{ __('dashboard.total_biomass') }}</div>
                <div class="kpi-panel__value">{{ number_format($vm['kpis']['total_biomass_kg'], 2) }} <span style="font-size:.54em;font-weight:700;">kg</span></div>
            </div>
            <div class="kpi-panel animate-enter-down animate-enter-down-delay-2">
                <div class="kpi-panel__label">{{ __('dashboard.average_fcr') }}</div>
                <div class="kpi-panel__value">{{ $vm['kpis']['average_fcr'] !== null ? number_format($vm['kpis']['average_fcr'],3) : 'N/D' }}</div>
            </div>
            <div class="kpi-panel animate-enter-down animate-enter-down-delay-2">
                <div class="kpi-panel__label">{{ __('dashboard.critical_alerts') }}</div>
                <div class="kpi-panel__value" style="color:#c63636;">{{ $vm['kpis']['critical_alerts'] }}</div>
            </div>
        </div>
    @endif

    <div class="card farms-card animate-enter-down animate-enter-down-delay-2">
        <div class="farms-card__header">
            <div>
                <h2 style="margin:0 0 4px;font-size:1.04rem;">{{ __('dashboard.farms_with_cycles') }}</h2>
                <div class="muted" style="font-size:.88rem;">{{ __('dashboard.farms_subtitle') }}</div>
            </div>
            <a href="/backoffice/cycles" class="cta-secondary">{{ __('dashboard.view_all_cycles') }}</a>
        </div>
        @if(count($vm['farms']) === 0)
            <div class="empty-state">{{ __('dashboard.no_active_cycles') }}</div>
        @else
            <div class="farm-list">
                @foreach($vm['farms'] as $farm)
                    <div class="farm-item animate-enter-down animate-enter-down-delay-3">
                        <div>
                            <div class="farm-item__title">{{ $farm['farm_name'] }}</div>
                            <div class="farm-item__meta">{{ $farm['active_cycles'] }} {{ __('dashboard.active_cycle_meta') }}</div>
                        </div>
                        <a href="/backoffice/cycles?farm={{ $farm['farm_id'] }}" class="farm-item__cta">
                            {{ __('dashboard.view_cycles') }}
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
