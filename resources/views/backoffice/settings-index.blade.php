@extends('backoffice.layout')

@section('title', 'Backoffice · Parámetros')

@section('content')
@php($canManageSettings = ($shell['permissions']['settings.manage'] ?? false) && ! $vm['read_only_mode'])
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('settings.title') }}</h1>
    <div class="section-subtitle">{{ __('settings.subtitle') }}</div>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div class="card animate-enter-down animate-enter-down-delay-1">
    <h3 class="panel-title">{{ __('settings.company_data') }}</h3>
    <form method="POST" action="/backoffice/settings" style="display:grid;gap:12px;max-width:560px;">
        @csrf
        <label>
            <span class="metric-label">{{ __('settings.company_display_name') }}</span>
            <input class="input{{ $errors->has('company_display_name') ? ' input--error' : '' }}" name="company_display_name" value="{{ old('company_display_name', $vm['tenant']->company_display_name) }}">
            @error('company_display_name') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="metric-label">{{ __('settings.company_legal_name') }}</span>
            <input class="input{{ $errors->has('company_legal_name') ? ' input--error' : '' }}" name="company_legal_name" value="{{ old('company_legal_name', $vm['tenant']->company_legal_name) }}">
            @error('company_legal_name') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="metric-label">{{ __('settings.company_address') }}</span>
            <input class="input{{ $errors->has('company_address') ? ' input--error' : '' }}" name="company_address" value="{{ old('company_address', $vm['tenant']->company_address) }}">
            @error('company_address') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="metric-label">{{ __('settings.company_phone') }}</span>
            <input class="input{{ $errors->has('company_phone') ? ' input--error' : '' }}" name="company_phone" value="{{ old('company_phone', $vm['tenant']->company_phone) }}">
            @error('company_phone') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="metric-label">{{ __('settings.company_email') }}</span>
            <input class="input{{ $errors->has('company_email') ? ' input--error' : '' }}" type="email" name="company_email" value="{{ old('company_email', $vm['tenant']->company_email) }}">
            @error('company_email') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>
            <span class="metric-label">{{ __('settings.report_footer_text') }}</span>
            <textarea class="input{{ $errors->has('report_footer_text') ? ' input--error' : '' }}" name="report_footer_text" rows="3">{{ old('report_footer_text', $vm['tenant']->report_footer_text) }}</textarea>
            @error('report_footer_text') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <div>
            <button class="cta-primary" type="submit" {{ $canManageSettings ? '' : 'disabled' }} title="{{ $canManageSettings ? __('settings.save') : (($shell['permissions']['settings.manage'] ?? false) ? $shell['write_block_tooltip'] : $shell['permission_block_tooltip']) }}">{{ __('settings.save') }}</button>
        </div>
    </form>
</div>
@endsection
