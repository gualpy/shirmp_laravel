@extends('backoffice.layout')

@section('title', 'Backoffice · Alerts')

@push('head')
<style>
    .alerts-shell { display: grid; gap: 16px; }
    .alerts-filter-card {
        background:
            linear-gradient(120deg, rgba(255, 255, 255, 0.96), rgba(247, 251, 255, 0.9)),
            radial-gradient(circle at top right, rgba(198, 54, 54, 0.08), transparent 34%);
    }
    .alerts-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
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
    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: end;
        flex-wrap: wrap;
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
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .filter-btn--primary {
        border-color: rgba(198, 54, 54, 0.14);
        background: rgba(198, 54, 54, 0.08);
        color: #9a1f1f;
    }
    .status-flash {
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        border: 1px solid rgba(12, 122, 106, 0.12);
        font-weight: 700;
    }
    .alerts-table-wrap {
        overflow: auto;
    }
    .alerts-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1120px;
    }
    .alerts-table th {
        text-align: left;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--muted);
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
    }
    .alerts-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #ebf1f6;
        vertical-align: top;
        font-size: .9rem;
    }
    .row-critical { background: rgba(255, 236, 235, 0.55); }
    .row-warning { background: rgba(255, 244, 232, 0.4); }
    .sev-badge, .state-badge {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
    }
    .sev-critical { background: #ffeceb; color: #c63636; }
    .sev-warning { background: #fff4e8; color: #aa6a08; }
    .sev-info { background: #e9f0ff; color: #2f62c8; }
    .state-open { background: rgba(198, 54, 54, 0.09); color: #9a1f1f; }
    .state-acknowledged { background: rgba(219, 141, 27, 0.12); color: #8d5b05; }
    .state-resolved { background: rgba(12, 122, 106, 0.11); color: #0c7a6a; }
    .message-copy {
        min-width: 280px;
        color: #33475b;
        line-height: 1.45;
    }
    .action-stack {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .action-btn {
        min-height: 34px;
        padding: 0 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        color: var(--text);
        font-weight: 700;
        cursor: pointer;
    }
    .action-btn--ack { color: #8d5b05; background: #fff8ee; border-color: #f1dbc0; }
    .action-btn--resolve { color: var(--primary); background: #eef8f5; border-color: #cfe7df; }
    .action-btn[disabled] { opacity: .45; cursor: not-allowed; }
    .empty-state {
        border: 1px dashed #c9d7e4;
        border-radius: 18px;
        padding: 24px 18px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
    }
    .pager {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        color: var(--muted);
        font-size: .86rem;
        flex-wrap: wrap;
    }
    .pager-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .pager-links a,
    .pager-links span {
        min-height: 34px;
        min-width: 34px;
        padding: 0 10px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        background: #fff;
        text-decoration: none;
        color: var(--text);
        font-weight: 700;
    }
    .pager-links .active span {
        background: rgba(47, 143, 255, 0.1);
        border-color: rgba(47, 143, 255, 0.2);
        color: #245fad;
    }
    @media (max-width: 1100px) {
        .alerts-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .alerts-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    @php($canManageAlerts = ($shell['permissions']['alerts.manage'] ?? false) && ! $vm['read_only_mode'])
    <div class="alerts-shell">
        <div class="card card-soft alerts-filter-card animate-enter-down">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                <div>
                    <h1 class="section-heading" style="font-size:1.34rem;margin-bottom:4px;">{{ __('alerts.title') }}</h1>
                    <div class="section-subtitle" style="font-size:.92rem;">{{ __('alerts.subtitle') }}</div>
                </div>
                <a href="/backoffice/alerts/export.xlsx" class="filter-btn">{{ __('alerts.export_excel') }}</a>
            </div>

            @if(session('status'))
                <div class="status-flash">{{ session('status') }}</div>
            @endif

            <form method="GET" action="/backoffice/alerts" class="alerts-filter-grid" style="margin-top:14px;">
                <label>
                    <span class="field-label">{{ __('alerts.farm') }}</span>
                    <select name="farm" class="field-control">
                        <option value="">{{ __('alerts.all') }}</option>
                        @foreach($vm['options']['farms'] as $farm)
                            <option value="{{ $farm['id'] }}" {{ $vm['filters']['farm'] === $farm['id'] ? 'selected' : '' }}>{{ $farm['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="field-label">{{ __('alerts.pond') }}</span>
                    <select name="pond" class="field-control">
                        <option value="">{{ __('alerts.all') }}</option>
                        @foreach($vm['options']['ponds'] as $pond)
                            <option value="{{ $pond['id'] }}" {{ $vm['filters']['pond'] === $pond['id'] ? 'selected' : '' }}>{{ $pond['code'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="field-label">{{ __('alerts.cycle') }}</span>
                    <select name="cycle" class="field-control">
                        <option value="">{{ __('alerts.all') }}</option>
                        @foreach($vm['options']['cycles'] as $cycle)
                            <option value="{{ $cycle['id'] }}" {{ $vm['filters']['cycle'] === $cycle['id'] ? 'selected' : '' }}>{{ $cycle['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="field-label">{{ __('alerts.severity') }}</span>
                    <select name="severity" class="field-control">
                        <option value="">{{ __('alerts.all') }}</option>
                        @foreach($vm['options']['severities'] as $severity)
                            <option value="{{ $severity }}" {{ $vm['filters']['severity'] === $severity ? 'selected' : '' }}>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="field-label">{{ __('alerts.state') }}</span>
                    <select name="state" class="field-control">
                        <option value="">{{ __('alerts.all') }}</option>
                        @foreach($vm['options']['states'] as $state)
                            <option value="{{ $state }}" {{ $vm['filters']['state'] === $state ? 'selected' : '' }}>{{ ucfirst($state) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="field-label">{{ __('alerts.date_from') }}</span>
                    <input type="date" name="date_from" value="{{ $vm['filters']['date_from'] }}" class="field-control">
                </label>
                <label>
                    <span class="field-label">{{ __('alerts.date_to') }}</span>
                    <input type="date" name="date_to" value="{{ $vm['filters']['date_to'] }}" class="field-control">
                </label>
                <div class="filter-actions">
                    <button type="submit" class="filter-btn filter-btn--primary">{{ __('alerts.filter') }}</button>
                    <a href="/backoffice/alerts" class="filter-btn">{{ __('alerts.reset') }}</a>
                </div>
            </form>
        </div>

        <div class="card animate-enter-down animate-enter-down-delay-1">
            @if($vm['rows'] === [])
                <div class="empty-state">{{ __('alerts.no_alerts') }}</div>
            @else
                <div class="alerts-table-wrap">
                    <table class="alerts-table">
                        <thead>
                            <tr>
                                <th>{{ __('alerts.date') }}</th>
                                <th>{{ __('alerts.farm') }}</th>
                                <th>{{ __('alerts.pond') }}</th>
                                <th>{{ __('alerts.cycle') }}</th>
                                <th>{{ __('alerts.type') }}</th>
                                <th>{{ __('alerts.severity') }}</th>
                                <th>{{ __('alerts.message') }}</th>
                                <th>{{ __('alerts.state') }}</th>
                                <th>{{ __('alerts.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vm['rows'] as $row)
                                <tr class="{{ $row['severity'] === 'critical' ? 'row-critical' : ($row['severity'] === 'warning' ? 'row-warning' : '') }}">
                                    <td>{{ $row['date'] }}</td>
                                    <td>{{ $row['farm'] }}</td>
                                    <td>{{ $row['pond'] }}</td>
                                    <td><a href="{{ $row['cycle_href'] }}" style="font-weight:700;color:var(--primary);text-decoration:none;">#{{ $row['cycle'] }}</a></td>
                                    <td>{{ $row['type'] }}</td>
                                    <td><span class="sev-badge sev-{{ $row['severity'] }}">{{ $row['severity'] }}</span></td>
                                    <td class="message-copy">{{ $row['message'] }}</td>
                                    <td><span class="state-badge state-{{ $row['state'] }}">{{ $row['state'] }}</span></td>
                                    <td>
                                        <div class="action-stack">
                                            @if($canManageAlerts)
                                                <form method="POST" action="/backoffice/alerts/{{ $row['id'] }}/acknowledge">
                                                    @csrf
                                                    <button type="submit" class="action-btn action-btn--ack" {{ $row['can_acknowledge'] ? '' : 'disabled' }}>
                                                        {{ __('alerts.acknowledge') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="/backoffice/alerts/{{ $row['id'] }}/resolve">
                                                    @csrf
                                                    <button type="submit" class="action-btn action-btn--resolve" {{ $row['can_resolve'] ? '' : 'disabled' }}>
                                                        {{ __('alerts.resolve') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="muted">{{ __('alerts.read_only') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pager">
                    <div>
                        {{ __('alerts.showing') }} {{ $vm['pagination']->firstItem() ?? 0 }}-{{ $vm['pagination']->lastItem() ?? 0 }} {{ __('alerts.of') }} {{ $vm['pagination']->total() }} {{ __('alerts.alerts') }}
                    </div>
                    <div class="pager-links">
                        {{ $vm['pagination']->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
