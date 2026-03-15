@extends('backoffice.layout')

@section('title', 'Backoffice · Calidad de agua')

@push('head')
<style>
    .water-shell { display: grid; gap: 16px; }
    .water-grid {
        display: grid;
        grid-template-columns: minmax(330px, 380px) minmax(0, 1fr);
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
    .entry-form {
        display: grid;
        gap: 12px;
    }
    .entry-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .form-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .primary-btn, .secondary-btn {
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
    .secondary-btn { background: #fff; color: var(--text); }
    .filters-card {
        background:
            linear-gradient(120deg, rgba(255, 255, 255, 0.96), rgba(247, 251, 255, 0.9)),
            radial-gradient(circle at top right, rgba(47, 143, 255, 0.08), transparent 34%);
    }
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        align-items: end;
    }
    .table-wrap { overflow: auto; }
    .water-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1040px;
    }
    .water-table th {
        text-align: left;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--muted);
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
    }
    .water-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #ebf1f6;
        vertical-align: top;
        font-size: .9rem;
    }
    .metric-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }
    .metric-chip--danger { background: #ffeceb; color: #c63636; }
    .metric-chip--warn { background: #fff4e8; color: #aa6a08; }
    .metric-flag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 900;
        color: #fff;
    }
    .metric-flag--danger { background: #c63636; }
    .metric-flag--warn { background: #db8d1b; }
    .value-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 700;
    }
    .status-flash {
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        border: 1px solid rgba(12, 122, 106, 0.12);
        font-weight: 700;
        margin-bottom: 12px;
    }
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
    .pager-links { display: flex; gap: 8px; flex-wrap: wrap; }
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
    @media (max-width: 1200px) {
        .water-grid { grid-template-columns: 1fr; }
        .filters-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 720px) {
        .entry-grid,
        .filters-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
    @php($canManageWater = ($shell['permissions']['water.manage'] ?? false) && ! $vm['read_only_mode'])
    <div class="water-shell">
        <div class="water-grid">
            <div class="card card-soft animate-enter-down" id="register-water">
                <h1 class="section-heading" style="font-size:1.28rem;margin-bottom:4px;">Registrar calidad de agua</h1>
                <div class="section-subtitle" style="font-size:.92rem;margin-bottom:14px;">Captura rápida para técnico de campo. Un registro, una piscina, sin navegar entre módulos.</div>

                @if(session('status'))
                    <div class="status-flash">{{ session('status') }}</div>
                @endif

                <form method="POST" action="/backoffice/water" class="entry-form">
                    @csrf
                    <label>
                        <span class="field-label">Piscina</span>
                        <select name="pond" class="field-control">
                            <option value="">Selecciona una piscina</option>
                            @foreach($vm['options']['form_ponds'] as $pond)
                                <option value="{{ $pond['id'] }}" {{ old('pond', $vm['defaults']['pond']) == $pond['id'] ? 'selected' : '' }}>{{ $pond['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="field-label">Fecha y hora</span>
                        <input type="datetime-local" name="measured_at" value="{{ old('measured_at', $vm['defaults']['measured_at']) }}" class="field-control">
                    </label>
                    <div class="entry-grid">
                        <label><span class="field-label">DO</span><input type="number" step="0.01" name="do" value="{{ old('do') }}" class="field-control"></label>
                        <label><span class="field-label">pH</span><input type="number" step="0.01" name="ph" value="{{ old('ph') }}" class="field-control"></label>
                        <label><span class="field-label">Temp</span><input type="number" step="0.01" name="temperature" value="{{ old('temperature') }}" class="field-control"></label>
                        <label><span class="field-label">Salinidad</span><input type="number" step="0.01" name="salinity" value="{{ old('salinity') }}" class="field-control"></label>
                        <label><span class="field-label">Alcalinidad</span><input type="number" step="0.01" name="alkalinity" value="{{ old('alkalinity') }}" class="field-control"></label>
                        <label><span class="field-label">Amonio</span><input type="number" step="0.001" name="ammonia" value="{{ old('ammonia') }}" class="field-control"></label>
                        <label><span class="field-label">Nitrito</span><input type="number" step="0.001" name="nitrite" value="{{ old('nitrite') }}" class="field-control"></label>
                    </div>
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
                        <button type="submit" class="primary-btn" {{ $canManageWater ? '' : 'disabled' }} title="{{ $canManageWater ? 'Guardar registro de campo' : (($shell['permissions']['water.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Guardar registro</button>
                        <a href="/backoffice/water" class="secondary-btn">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="card filters-card animate-enter-down animate-enter-down-delay-1">
                <h2 class="section-heading" style="font-size:1.2rem;margin-bottom:4px;">Registros recientes</h2>
                <div class="section-subtitle" style="font-size:.92rem;margin-bottom:14px;">Monitorea rápidamente eventos fuera de rango por fecha, finca, piscina o ciclo.</div>
                <form method="GET" action="/backoffice/water" class="filters-grid">
                    <label>
                        <span class="field-label">Farm</span>
                        <select name="farm" class="field-control">
                            <option value="">Todas</option>
                            @foreach($vm['options']['farms'] as $farm)
                                <option value="{{ $farm['id'] }}" {{ $vm['filters']['farm'] === $farm['id'] ? 'selected' : '' }}>{{ $farm['name'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="field-label">Pond</span>
                        <select name="pond" class="field-control">
                            <option value="">Todas</option>
                            @foreach($vm['options']['ponds'] as $pond)
                                <option value="{{ $pond['id'] }}" {{ $vm['filters']['pond'] === $pond['id'] ? 'selected' : '' }}>{{ $pond['code'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="field-label">Cycle</span>
                        <select name="cycle" class="field-control">
                            <option value="">Todos</option>
                            @foreach($vm['options']['cycles'] as $cycle)
                                <option value="{{ $cycle['id'] }}" {{ $vm['filters']['cycle'] === $cycle['id'] ? 'selected' : '' }}>{{ $cycle['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="field-label">Fecha desde</span>
                        <input type="date" name="date_from" value="{{ $vm['filters']['date_from'] }}" class="field-control">
                    </label>
                    <label>
                        <span class="field-label">Fecha hasta</span>
                        <input type="date" name="date_to" value="{{ $vm['filters']['date_to'] }}" class="field-control">
                    </label>
                    <div class="form-actions" style="grid-column:1 / -1;">
                        <button type="submit" class="primary-btn">Filtrar</button>
                        <a href="/backoffice/water" class="secondary-btn">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card animate-enter-down animate-enter-down-delay-2">
            @if($vm['rows'] === [])
                <div class="empty-state">No hay registros de calidad de agua.</div>
            @else
                <div class="table-wrap">
                    <table class="water-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Farm</th>
                                <th>Pond</th>
                                <th>Cycle</th>
                                <th>DO</th>
                                <th>pH</th>
                                <th>Temp</th>
                                <th>Salinidad</th>
                                <th>Amonio</th>
                                <th>Nitrito</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vm['rows'] as $row)
                                <tr>
                                    <td>{{ $row['date'] }}</td>
                                    <td>{{ $row['farm'] }}</td>
                                    <td>{{ $row['pond'] }}</td>
                                    <td><a href="{{ $row['cycle_href'] }}" class="value-link">#{{ $row['cycle'] }}</a></td>
                                    <td>
                                        @if($row['dissolved_oxygen_mg_l'] !== null)
                                            <span class="metric-chip {{ $row['do_low'] ? 'metric-chip--danger' : '' }}">
                                                {{ number_format($row['dissolved_oxygen_mg_l'], 2) }}
                                                @if($row['do_low']) <span class="metric-flag metric-flag--danger">!</span> @endif
                                            </span>
                                        @else
                                            N/D
                                        @endif
                                    </td>
                                    <td>
                                        @if($row['ph'] !== null)
                                            <span class="metric-chip {{ $row['ph_out'] ? 'metric-chip--warn' : '' }}">
                                                {{ number_format($row['ph'], 2) }}
                                                @if($row['ph_out']) <span class="metric-flag metric-flag--warn">!</span> @endif
                                            </span>
                                        @else
                                            N/D
                                        @endif
                                    </td>
                                    <td>{{ $row['temp_c'] !== null ? number_format($row['temp_c'], 2) : 'N/D' }}</td>
                                    <td>{{ $row['salinity_ppt'] !== null ? number_format($row['salinity_ppt'], 2) : 'N/D' }}</td>
                                    <td>{{ $row['ammonia_mg_l'] !== null ? number_format($row['ammonia_mg_l'], 3) : 'N/D' }}</td>
                                    <td>{{ $row['nitrite_mg_l'] !== null ? number_format($row['nitrite_mg_l'], 3) : 'N/D' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pager">
                    <div>
                        Mostrando {{ $vm['pagination']->firstItem() ?? 0 }}-{{ $vm['pagination']->lastItem() ?? 0 }} de {{ $vm['pagination']->total() }} registros
                    </div>
                    <div class="pager-links">
                        {{ $vm['pagination']->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
