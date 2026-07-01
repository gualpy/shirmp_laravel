@extends('backoffice.layout')

@section('title', 'Backoffice · Active Cycles')

@push('head')
<style>
    .page-shell { display: grid; gap: 16px; }
    .filter-card {
        background:
            linear-gradient(120deg, rgba(255, 255, 255, 0.96), rgba(247, 251, 255, 0.9)),
            radial-gradient(circle at top right, rgba(47, 143, 255, 0.08), transparent 34%);
    }
    .filter-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto auto;
        gap: 10px;
        align-items: end;
    }
    .field-label {
        display: block;
        font-size: .78rem;
        color: var(--muted);
        margin-bottom: 6px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .field-control {
        width: 100%;
        min-height: 44px;
        padding: 0 12px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        color: var(--text);
        font: inherit;
        box-shadow: var(--shadow-soft);
    }
    .filter-btn {
        min-height: 44px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
        font-weight: 700;
        cursor: pointer;
    }
    .filter-btn--primary {
        border-color: rgba(12, 122, 106, 0.18);
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
    }
    .cycles-grid { display: grid; gap: 12px; }
    .cycle-card {
        text-decoration: none;
        color: inherit;
        display: block;
        padding: 18px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .cycle-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 34px rgba(15, 34, 51, 0.1);
        border-color: #bfd6e8;
    }
    .cycle-card__row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
    }
    .cycle-card__title {
        margin: 0 0 6px;
        font-size: 1.08rem;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .cycle-card__meta {
        color: var(--muted);
        font-size: .88rem;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(12, 122, 106, 0.09);
        color: var(--primary);
        font-weight: 700;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .cycle-card__right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .badge-stack {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .cycle-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 11px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: -.01em;
    }
    .cycle-badge--bio { background: #e7f5f1; color: #0c7a6a; }
    .cycle-badge--pp { background: #e9f0ff; color: #2f62c8; }
    .cycle-badge--critical { background: #ffeceb; color: #c63636; }
    .cycle-badge--warning { background: #fff4e8; color: #aa6a08; }
    .detail-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--primary);
        font-weight: 800;
        font-size: .85rem;
        white-space: nowrap;
    }
    .detail-cta::after {
        content: "›";
        font-size: 1rem;
    }
    .empty-state {
        border: 1px dashed #c9d7e4;
        border-radius: 18px;
        padding: 24px 18px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
    }
    @media (max-width: 980px) {
        .filter-grid { grid-template-columns: 1fr; }
        .cycle-card__row { grid-template-columns: 1fr; }
        .cycle-card__right,
        .badge-stack { justify-content: flex-start; }
    }
</style>
@endpush

@section('content')
    <div class="page-shell">
        <div class="card card-soft filter-card animate-enter-down">
            <div class="filter-head">
                <div>
                    <h1 class="section-heading" style="font-size:1.34rem;margin-bottom:4px;">Active Cycles</h1>
                    <div class="section-subtitle" style="font-size:.92rem;">Operational view to quickly identify biomass, average weight and alerts per pond.</div>
                </div>
            </div>

            <form method="GET" action="/backoffice/cycles" class="filter-grid">
                <label>
                    <span class="field-label">Farm</span>
                    <select name="farm" class="field-control">
                        <option value="">All</option>
                        @foreach($vm['options']['farms'] as $farm)
                            <option value="{{ $farm['id'] }}" {{ $vm['filters']['farm'] === $farm['id'] ? 'selected' : '' }}>
                                {{ $farm['name'] }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="field-label">Pond</span>
                    <select name="pond" class="field-control">
                        <option value="">All</option>
                        @foreach($vm['options']['ponds'] as $pond)
                            <option value="{{ $pond['id'] }}" {{ $vm['filters']['pond'] === $pond['id'] ? 'selected' : '' }}>
                                {{ $pond['code'] }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="filter-btn filter-btn--primary">Filter</button>
                <a href="/backoffice/cycles" class="filter-btn" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Reset</a>
            </form>
        </div>

        <div class="cycles-grid">
            @forelse($vm['rows'] as $row)
                <a href="{{ $row['detail_href'] }}" class="card cycle-card animate-enter-down animate-enter-down-delay-1">
                    <div class="cycle-card__row">
                        <div>
                            <div class="cycle-card__title">{{ $row['pond_code'] }} · {{ $row['farm'] }}</div>
                            <div class="cycle-card__meta">
                                <span>Started {{ $row['started_at'] }}</span>
                                <span class="status-pill">Active</span>
                            </div>
                        </div>
                        <div class="cycle-card__right">
                            <div class="badge-stack">
                                <span class="cycle-badge cycle-badge--bio">Biomass {{ number_format($row['biomass_kg'],2) }} kg</span>
                                <span class="cycle-badge cycle-badge--pp">PP {{ $row['latest_pp'] !== null ? number_format($row['latest_pp'],2).' g' : 'N/A' }}</span>
                                <span class="cycle-badge cycle-badge--critical">Critical {{ $row['alerts_critical'] }}</span>
                                <span class="cycle-badge cycle-badge--warning">Warning {{ $row['alerts_warning'] }}</span>
                            </div>
                            <span class="detail-cta">View detail</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state animate-enter-down animate-enter-down-delay-1">There are no active cycles for the selected filters.</div>
            @endforelse
        </div>
    </div>
@endsection
