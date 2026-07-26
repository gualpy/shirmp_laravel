@extends('backoffice.layout')

@section('title', 'Backoffice · Muestreo #'.$vm['header']['cycle_id'])

@push('head')
<style>
    .sampling-shell { display: grid; gap: 16px; }
    .sampling-grid {
        display: grid;
        grid-template-columns: minmax(320px, 380px) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }
    .mini-kpis {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        max-width: 420px;
    }
    .mini-kpi {
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 16px;
        box-shadow: var(--shadow-soft);
    }
    .mini-kpi__label {
        color: var(--muted);
        font-size: .74rem;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .mini-kpi__value {
        font-size: 1.24rem;
        font-weight: 800;
        letter-spacing: -.04em;
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
    .form-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .primary-btn, .inline-link {
        min-height: 44px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid var(--border);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
    }
    .primary-btn {
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        border-color: rgba(12, 122, 106, 0.18);
    }
    .primary-btn[disabled] { opacity: .45; cursor: not-allowed; }
    .inline-link { background: #fff; color: var(--text); }
    .status-flash {
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(12, 122, 106, 0.08);
        color: var(--primary);
        border: 1px solid rgba(12, 122, 106, 0.12);
        font-weight: 700;
    }
    .table-wrap { overflow:auto; }
    .sampling-table { width:100%; border-collapse: collapse; min-width: 480px; }
    .sampling-table th {
        text-align:left;
        font-size:.74rem;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:var(--muted);
        padding:12px 10px;
        border-bottom:1px solid var(--border);
    }
    .sampling-table td {
        padding:14px 10px;
        border-bottom:1px solid #ebf1f6;
        vertical-align:top;
        font-size:.9rem;
    }
    .empty-state {
        border: 1px dashed #c9d7e4;
        border-radius: 18px;
        padding: 24px 18px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
    }
    @media (max-width: 1080px) {
        .sampling-grid { grid-template-columns: 1fr; }
        .mini-kpis { max-width: none; }
    }
</style>
@endpush

@section('content')
    @php($canManageSampling = ($shell['permissions']['sampling.manage'] ?? false) && ! $vm['read_only_mode'])
    <div class="sampling-shell">
        <div class="card card-soft">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;">
                <div>
                    <h1 class="section-heading" style="margin-bottom:4px;">{{ __('cycle.sampling_title') }} · {{ __('cycles.cycle') }} #{{ $vm['header']['cycle_id'] }}</h1>
                    <div class="section-subtitle">{{ __('cycle.sampling_subtitle') }}</div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;color:var(--muted);font-size:.9rem;">
                        <span>{{ __('cycle.farm') }} {{ $vm['header']['farm_name'] }}</span>
                        <span>{{ __('cycle.pond') }} {{ $vm['header']['pond_code'] }}</span>
                        <span>{{ __('cycle.started') }} {{ $vm['header']['started_at'] }}</span>
                    </div>
                </div>
                <a href="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}" class="inline-link">{{ __('cycle.back_to_cycle') }}</a>
            </div>
        </div>

        @if(session('status'))
            <div class="status-flash">{{ session('status') }}</div>
        @endif

        <div class="mini-kpis">
            <div class="mini-kpi"><div class="mini-kpi__label">{{ __('cycle.latest_pp_grams') }}</div><div class="mini-kpi__value">{{ $vm['summary']['latest_pp_grams'] !== null ? number_format($vm['summary']['latest_pp_grams'],2).' g' : 'N/A' }}</div></div>
            <div class="mini-kpi"><div class="mini-kpi__label">{{ __('cycle.growth_g_per_week') }}</div><div class="mini-kpi__value">{{ $vm['summary']['growth_g_per_week'] !== null ? number_format($vm['summary']['growth_g_per_week'],2).' g' : 'N/A' }}</div></div>
        </div>

        <div class="sampling-grid">
            <div class="card">
                <h2 class="section-heading" style="font-size:1.18rem;margin-bottom:4px;">{{ __('cycle.record_sampling') }}</h2>
                <div class="section-subtitle" style="font-size:.9rem;margin-bottom:14px;">{{ __('cycle.record_sampling_sub') }}</div>

                <form method="POST" action="/backoffice/cycles/{{ $vm['header']['cycle_id'] }}/sampling" class="entry-form">
                    @csrf
                    <label>
                        <span class="field-label">{{ __('cycle.date') }}</span>
                        <input type="date" name="sampled_at" value="{{ old('sampled_at', $vm['defaults']['sampled_at']) }}" class="field-control{{ $errors->has('sampled_at') ? ' input--error' : '' }}">@error('sampled_at') <span class="field-error">{{ $message }}</span> @enderror
                    </label>
                    <label>
                        <span class="field-label">{{ __('cycle.pp_grams') }}</span>
                        <input type="number" min="0.01" step="0.01" name="pp_grams" value="{{ old('pp_grams') }}" class="field-control{{ $errors->has('pp_grams') ? ' input--error' : '' }}">@error('pp_grams') <span class="field-error">{{ $message }}</span> @enderror
                    </label>
                    <label>
                        <span class="field-label">{{ __('cycle.notes') }}</span>
                        <textarea name="notes" class="field-control{{ $errors->has('notes') ? ' input--error' : '' }}">{{ old('notes') }}</textarea>@error('notes') <span class="field-error">{{ $message }}</span> @enderror
                    </label>
                    @if($errors->any())
                        <div class="empty-state" style="padding:14px 16px;color:#9a1f1f;background:#fff4f4;border-color:#f1caca;">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="form-actions">
                        <button type="submit" class="primary-btn" {{ $canManageSampling ? '' : 'disabled' }} title="{{ $canManageSampling ? __('cycle.record_sampling') : (($shell['permissions']['sampling.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('cycle.save_sampling') }}</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2 class="section-heading" style="font-size:1.18rem;margin-bottom:4px;">{{ __('cycle.sampling_history') }}</h2>
                <div class="section-subtitle" style="font-size:.9rem;margin-bottom:14px;">{{ __('cycle.sampling_history_sub') }}</div>
                @if($vm['rows'] === [])
                    <div class="empty-state">{{ __('cycle.no_samplings') }}</div>
                @else
                    <div class="table-wrap">
                        <table class="sampling-table">
                            <thead>
                                <tr>
                                    <th>{{ __('cycle.date') }}</th>
                                    <th>{{ __('cycle.pp_grams') }}</th>
                                    <th>{{ __('cycle.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vm['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['sampled_at'] }}</td>
                                        <td>{{ number_format($row['pp_grams'],2) }}</td>
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
