@extends('backoffice.layout')

@section('title', 'Backoffice · Cycle costs #'.$vm['header']['cycle_id'])

@push('head')
<style>
    .costs-shell { display: grid; gap: 16px; }
    .costs-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }
    .kpi-card {
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 16px;
        box-shadow: var(--shadow-soft);
    }
    .kpi-card__label {
        color: var(--muted);
        font-size: .74rem;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .kpi-card__value {
        font-size: 1.34rem;
        font-weight: 800;
        letter-spacing: -.04em;
    }
    .costs-grid {
        display: grid;
        grid-template-columns: minmax(320px, 380px) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
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
    textarea.field-control {
        min-height: 92px;
        padding: 12px;
        resize: vertical;
    }
    .entry-form { display: grid; gap: 12px; }
    .form-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .primary-btn, .secondary-btn, .inline-link {
        min-height: 44px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid var(--border);
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .primary-btn {
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        border-color: rgba(12, 122, 106, 0.18);
    }
    .primary-btn[disabled] { opacity: .45; cursor: not-allowed; }
    .secondary-btn, .inline-link { background: #fff; color: var(--text); }
    .warning-list {
        display: grid;
        gap: 10px;
    }
    .warning-card {
        border: 1px solid #f1dbc0;
        background: linear-gradient(180deg, #fff8ee, #fffdf8);
        border-radius: 14px;
        padding: 12px 14px;
        color: #8d5b05;
    }
    .empty-state {
        border: 1px dashed #c9d7e4;
        border-radius: 18px;
        padding: 24px 18px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
    }
    .status-flash {
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        border: 1px solid rgba(12, 122, 106, 0.12);
        font-weight: 700;
    }
    .table-wrap { overflow: auto; }
    .costs-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }
    .costs-table th {
        text-align: left;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--muted);
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
    }
    .costs-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #ebf1f6;
        vertical-align: top;
        font-size: .9rem;
    }
    .type-chip {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 999px;
        background: #eaf1f8;
        color: #2d4257;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    @media (max-width: 1180px) {
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .costs-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 720px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
    @php($canManageCosts = ($shell['permissions']['costs.manage'] ?? false) && ! $vm['read_only_mode'])
    <div class="costs-shell">
        <div class="card card-soft">
            <div class="costs-hero">
                <div>
                    <h1 class="section-heading" style="margin-bottom:4px;">{{ __('cycle.costs_title') }} #{{ $vm['header']['cycle_id'] }}</h1>
                    <div class="section-subtitle">{{ __('cycle.costs_subtitle') }}</div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;color:var(--muted);font-size:.9rem;">
                        <span>{{ __('cycle.farm') }} {{ $vm['header']['farm_name'] }}</span>
                        <span>{{ __('cycle.pond') }} {{ $vm['header']['pond_code'] }}</span>
                        <span>{{ __('cycle.started') }} {{ $vm['header']['started_at'] }}</span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/exports/costs.xlsx" class="inline-link">{{ __('cycle.export_excel') }}</a>
                    <a href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}" class="inline-link">{{ __('cycle.back_to_cycle') }}</a>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="status-flash">{{ session('status') }}</div>
        @endif

        <div class="kpi-grid">
            <div class="kpi-card"><div class="kpi-card__label">{{ __('cycle.feed_cost') }}</div><div class="kpi-card__value">${{ number_format($vm['summary']['totals']['feed_cost'], 2) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">{{ __('cycle.operational_cost') }}</div><div class="kpi-card__value">${{ number_format($vm['summary']['totals']['operational_cost'], 2) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">{{ __('cycle.total_cost') }}</div><div class="kpi-card__value">${{ number_format($vm['summary']['totals']['total_cost'], 2) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">{{ __('cycle.cost_per_lb') }}</div><div class="kpi-card__value">${{ number_format($vm['summary']['metrics']['cost_per_lb'], 4) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">{{ __('cycle.cost_per_ha') }}</div><div class="kpi-card__value">${{ number_format($vm['summary']['metrics']['cost_per_ha'], 4) }}</div></div>
        </div>

        @if($vm['summary']['missing_cost_inputs'] !== [])
            <div class="warning-list">
                @foreach($vm['summary']['missing_cost_inputs'] as $warning)
                    <div class="warning-card">{{ __('cycle.missing_cost', ['name' => $warning['name']]) }}</div>
                @endforeach
            </div>
        @endif

        <div class="costs-grid">
            <div class="card">
                <h2 class="section-heading" style="font-size:1.18rem;margin-bottom:4px;">{{ __('cycle.record_cost') }}</h2>
                <div class="section-subtitle" style="font-size:.9rem;margin-bottom:14px;">{{ __('cycle.record_cost_subtitle') }}</div>

                <form method="POST" action="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/costs" class="entry-form">
                    @csrf
                    <label>
                        <span class="field-label">{{ __('cycle.cost_type') }}</span>
                        <select name="cost_type" class="field-control">
                            @foreach($vm['cost_type_options'] as $option)
                                <option value="{{ $option['value'] }}" {{ old('cost_type') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="field-label">{{ __('cycle.amount') }}</span>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="field-control">
                    </label>
                    <label>
                        <span class="field-label">{{ __('cycle.date') }}</span>
                        <input type="date" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d')) }}" class="field-control">
                    </label>
                    <label>
                        <span class="field-label">{{ __('cycle.notes') }}</span>
                        <textarea name="notes" class="field-control">{{ old('notes') }}</textarea>
                    </label>
                    @if($errors->any())
                        <div class="empty-state" style="padding:14px 16px;color:#9a1f1f;background:#fff4f4;border-color:#f1caca;">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="form-actions">
                        <button type="submit" class="primary-btn" {{ $canManageCosts ? '' : 'disabled' }} title="{{ $canManageCosts ? __('cycle.record_cost') : (($shell['permissions']['costs.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('cycle.save_cost') }}</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2 class="section-heading" style="font-size:1.18rem;margin-bottom:4px;">{{ __('cycle.recorded_costs') }}</h2>
                <div class="section-subtitle" style="font-size:.9rem;margin-bottom:14px;">{{ __('cycle.recorded_costs_sub') }}</div>

                @if($vm['rows'] === [])
                    <div class="empty-state">{{ __('cycle.no_costs') }}</div>
                @else
                    <div class="table-wrap">
                        <table class="costs-table">
                            <thead>
                                <tr>
                                    <th>{{ __('cycle.date') }}</th>
                                    <th>{{ __('cycle.type') }}</th>
                                    <th>{{ __('cycle.amount') }}</th>
                                    <th>{{ __('cycle.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vm['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['occurred_at'] }}</td>
                                        <td><span class="type-chip">{{ $row['cost_type'] }}</span></td>
                                        <td>${{ number_format($row['amount'], 2) }}</td>
                                        <td>{{ $row['notes'] ?: __('cycle.no_notes') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
