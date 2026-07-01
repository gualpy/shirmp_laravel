@extends('backoffice.layout')

@section('title', 'Backoffice · New Stocking')

@section('content')
@php($canManageProduction = ($shell['permissions']['production.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">New Stocking</h1>
        <div class="section-subtitle">Guided flow to open a cycle and register stocking in a single operation. Built for support, management and field operations.</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <span class="chip">{{ $vm['available_ponds'] }} available ponds</span>
        <a class="cta-secondary" href="/backoffice/farms">Farms</a>
        <a class="cta-secondary" href="/backoffice/ponds">Ponds</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(0,1.3fr) minmax(280px,.7fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">Open cycle and stock</h3>
        <form method="POST" action="/backoffice/stocking" style="display:grid;gap:12px;">
            @csrf
            <label>
                <span class="metric-label">Available pond</span>
                <select class="input" name="pond_id">
                    <option value="">Select a pond</option>
                    @foreach($vm['pond_options'] as $pond)
                        <option value="{{ $pond['id'] }}" @disabled($pond['disabled']) @selected((string) old('pond_id') === (string) $pond['id'])>
                            {{ $pond['label'] }}@if($pond['disabled']) · Not available @endif
                        </option>
                    @endforeach
                </select>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">Cycle start</span><input class="input" type="date" name="started_at" value="{{ old('started_at', now()->toDateString()) }}"></label>
                <label><span class="metric-label">Stocking date</span><input class="input" type="date" name="stocked_at" value="{{ old('stocked_at', now()->toDateString()) }}"></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                <label><span class="metric-label">Stocked PL</span><input class="input" type="number" min="1" name="pl_qty" value="{{ old('pl_qty') }}" placeholder="420000"></label>
                <label><span class="metric-label">Hatchery</span><input class="input" name="hatchery_code" value="{{ old('hatchery_code') }}" placeholder="BC"></label>
                <label><span class="metric-label">Batch</span><input class="input" name="batch_code" value="{{ old('batch_code') }}" placeholder="TA-2026-003"></label>
            </div>
            <div style="display:grid;grid-template-columns:220px 1fr;gap:10px;">
                <label><span class="metric-label">Initial ABW (g)</span><input class="input" type="number" min="0.01" step="0.01" name="initial_pp_grams" value="{{ old('initial_pp_grams', '0.05') }}"></label>
                <label><span class="metric-label">Cycle notes</span><input class="input" name="cycle_notes" value="{{ old('cycle_notes') }}" placeholder="Initial operational notes"></label>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageProduction ? '' : 'disabled' }} title="{{ $canManageProduction ? 'Open cycle and stock' : (($shell['permissions']['production.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">Open cycle and stock</button>
                <a class="cta-secondary" href="/backoffice/stocking/create">Reset</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">Operational checklist</h3>
        <div class="section-subtitle" style="margin-bottom:12px;">This flow creates the active cycle and initial stocking in one step.</div>
        <div style="display:grid;gap:10px;">
            <div class="chip" style="border-radius:16px;padding:10px 12px;background:#eefaf4;color:#176448;border-color:#b9e2cf;">1. Select a pond sin ciclo activo</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">2. Set the cycle start and stocking dates</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">3. Register PL, hatchery and batch when applicable</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">4. The system calculates density automatically</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">5. You are redirected to the cycle detail view</div>
        </div>
    </div>
</div>
@endsection
