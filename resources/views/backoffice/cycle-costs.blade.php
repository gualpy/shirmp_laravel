@extends('backoffice.layout')

@section('title', 'Backoffice · Costos ciclo #'.$vm['header']['cycle_id'])

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
    <div class="costs-shell">
        <div class="card card-soft">
            <div class="costs-hero">
                <div>
                    <h1 class="section-heading" style="margin-bottom:4px;">Costos del Ciclo #{{ $vm['header']['cycle_id'] }}</h1>
                    <div class="section-subtitle">Visión financiera clara para controlar costo acumulado, rentabilidad y registro operativo del ciclo.</div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;color:var(--muted);font-size:.9rem;">
                        <span>Finca {{ $vm['header']['farm_name'] }}</span>
                        <span>Piscina {{ $vm['header']['pond_code'] }}</span>
                        <span>Inicio {{ $vm['header']['started_at'] }}</span>
                    </div>
                </div>
                <a href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}" class="inline-link">Volver al ciclo</a>
            </div>
        </div>

        @if(session('status'))
            <div class="status-flash">{{ session('status') }}</div>
        @endif

        <div class="kpi-grid">
            <div class="kpi-card"><div class="kpi-card__label">Feed cost</div><div class="kpi-card__value">${{ number_format($vm['summary']['totals']['feed_cost'], 2) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">Operational cost</div><div class="kpi-card__value">${{ number_format($vm['summary']['totals']['operational_cost'], 2) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">Total cost</div><div class="kpi-card__value">${{ number_format($vm['summary']['totals']['total_cost'], 2) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">Cost per lb</div><div class="kpi-card__value">${{ number_format($vm['summary']['metrics']['cost_per_lb'], 4) }}</div></div>
            <div class="kpi-card"><div class="kpi-card__label">Cost per ha</div><div class="kpi-card__value">${{ number_format($vm['summary']['metrics']['cost_per_ha'], 4) }}</div></div>
        </div>

        @if($vm['summary']['missing_cost_inputs'] !== [])
            <div class="warning-list">
                @foreach($vm['summary']['missing_cost_inputs'] as $warning)
                    <div class="warning-card">Falta `cost_per_kg` para el alimento <strong>{{ $warning['name'] }}</strong>. El costo total puede estar subestimado.</div>
                @endforeach
            </div>
        @endif

        <div class="costs-grid">
            <div class="card">
                <h2 class="section-heading" style="font-size:1.18rem;margin-bottom:4px;">Registrar costo operativo</h2>
                <div class="section-subtitle" style="font-size:.9rem;margin-bottom:14px;">Captura rápida para administración y control económico del ciclo.</div>

                <form method="POST" action="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/costs" class="entry-form">
                    @csrf
                    <label>
                        <span class="field-label">Tipo de costo</span>
                        <select name="cost_type" class="field-control">
                            @foreach($vm['cost_type_options'] as $option)
                                <option value="{{ $option['value'] }}" {{ old('cost_type') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="field-label">Monto</span>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="field-control">
                    </label>
                    <label>
                        <span class="field-label">Fecha</span>
                        <input type="date" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d')) }}" class="field-control">
                    </label>
                    <label>
                        <span class="field-label">Notas</span>
                        <textarea name="notes" class="field-control">{{ old('notes') }}</textarea>
                    </label>
                    @if($errors->any())
                        <div class="empty-state" style="padding:14px 16px;color:#9a1f1f;background:#fff4f4;border-color:#f1caca;">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="form-actions">
                        <button type="submit" class="primary-btn" {{ $vm['read_only_mode'] ? 'disabled' : '' }} title="{{ $vm['read_only_mode'] ? $shell['write_block_tooltip'] : 'Registrar costo operativo' }}">Guardar costo</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2 class="section-heading" style="font-size:1.18rem;margin-bottom:4px;">Costos operativos registrados</h2>
                <div class="section-subtitle" style="font-size:.9rem;margin-bottom:14px;">Detalle cronológico para revisión gerencial y soporte administrativo.</div>

                @if($vm['rows'] === [])
                    <div class="empty-state">Aún no se han cargado costos operativos.</div>
                @else
                    <div class="table-wrap">
                        <table class="costs-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vm['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['occurred_at'] }}</td>
                                        <td><span class="type-chip">{{ $row['cost_type'] }}</span></td>
                                        <td>${{ number_format($row['amount'], 2) }}</td>
                                        <td>{{ $row['notes'] ?: 'Sin notas' }}</td>
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
