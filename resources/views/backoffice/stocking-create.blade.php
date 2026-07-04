@extends('backoffice.layout')

@section('title', 'Backoffice · New Stocking')

@section('content')
@php($canManageProduction = ($shell['permissions']['production.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">{{ __('farms.stocking_title') }}</h1>
        <div class="section-subtitle">{{ __('farms.stocking_subtitle') }}</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <span class="chip">{{ $vm['available_ponds'] }} {{ __('farms.available_ponds') }}</span>
        <a class="cta-secondary" href="/backoffice/farms">{{ __('farms.farms_link') }}</a>
        <a class="cta-secondary" href="/backoffice/ponds">{{ __('farms.ponds_link') }}</a>
    </div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:minmax(0,1.3fr) minmax(280px,.7fr);gap:16px;align-items:start;">
    <div class="card animate-enter-down animate-enter-down-delay-1">
        <h3 class="panel-title">{{ __('farms.open_cycle_stock') }}</h3>
        <form method="POST" action="/backoffice/stocking" style="display:grid;gap:12px;">
            @csrf
            <label>
                <span class="metric-label">{{ __('farms.available_pond') }}</span>
                <select class="input" name="pond_id">
                    <option value="">{{ __('farms.select_pond') }}</option>
                    @foreach($vm['pond_options'] as $pond)
                        <option value="{{ $pond['id'] }}" @disabled($pond['disabled']) @selected((string) old('pond_id') === (string) $pond['id'])>
                            {{ $pond['label'] }}@if($pond['disabled']) · {{ __('farms.not_available_pond') }} @endif
                        </option>
                    @endforeach
                </select>
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <label><span class="metric-label">{{ __('farms.cycle_start') }}</span><input class="input" type="date" name="started_at" value="{{ old('started_at', now()->toDateString()) }}"></label>
                <label><span class="metric-label">{{ __('farms.stocking_date') }}</span><input class="input" type="date" name="stocked_at" value="{{ old('stocked_at', now()->toDateString()) }}"></label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                <label><span class="metric-label">{{ __('farms.stocked_pl') }}</span><input class="input" type="number" min="1" name="pl_qty" value="{{ old('pl_qty') }}" placeholder="420000"></label>
                <label><span class="metric-label">{{ __('farms.hatchery') }}</span><input class="input" name="hatchery_code" value="{{ old('hatchery_code') }}" placeholder="BC"></label>
                <label><span class="metric-label">{{ __('farms.batch') }}</span><input class="input" name="batch_code" value="{{ old('batch_code') }}" placeholder="TA-2026-003"></label>
            </div>
            <div style="display:grid;grid-template-columns:220px 1fr;gap:10px;">
                <label><span class="metric-label">{{ __('farms.initial_abw') }}</span><input class="input" type="number" min="0.01" step="0.01" name="initial_pp_grams" value="{{ old('initial_pp_grams', '0.05') }}"></label>
                <label><span class="metric-label">{{ __('farms.cycle_notes') }}</span><input class="input" name="cycle_notes" value="{{ old('cycle_notes') }}" placeholder="{{ __('farms.initial_notes_ph') }}"></label>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="cta-primary" type="submit" {{ $canManageProduction ? '' : 'disabled' }} title="{{ $canManageProduction ? __('farms.open_cycle_stock') : (($shell['permissions']['production.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('farms.open_cycle_stock') }}</button>
                <a class="cta-secondary" href="/backoffice/stocking/create">{{ __('cycles.reset') }}</a>
            </div>
        </form>
    </div>

    <div class="card animate-enter-down animate-enter-down-delay-2">
        <h3 class="panel-title">{{ __('farms.checklist') }}</h3>
        <div class="section-subtitle" style="margin-bottom:12px;">{{ __('farms.checklist_subtitle') }}</div>
        <div style="display:grid;gap:10px;">
            <div class="chip" style="border-radius:16px;padding:10px 12px;background:#eefaf4;color:#176448;border-color:#b9e2cf;">{{ __('farms.step_1') }}</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">{{ __('farms.step_2') }}</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">{{ __('farms.step_3') }}</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">{{ __('farms.step_4') }}</div>
            <div class="chip" style="border-radius:16px;padding:10px 12px;">{{ __('farms.step_5') }}</div>
        </div>
    </div>
</div>
@endsection
