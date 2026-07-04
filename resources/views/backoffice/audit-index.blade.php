@extends('backoffice.layout')

@section('title', 'Backoffice · Audit')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('audit.title') }}</h1>
    <div class="section-subtitle">{{ __('audit.subtitle') }}</div>
</div>

<form method="GET" class="card" style="margin-bottom:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
    <label><span class="metric-label">{{ __('audit.user') }}</span><input class="input" type="text" name="user" value="{{ $vm['filters']['user'] ?? '' }}"></label>
    <label><span class="metric-label">{{ __('audit.action') }}</span><select class="input" name="action_key"><option value="">{{ __('audit.all') }}</option>@foreach($vm['action_keys'] as $actionKey)<option value="{{ $actionKey }}" @selected(($vm['filters']['action_key'] ?? '')===$actionKey)>{{ $actionKey }}</option>@endforeach</select></label>
    <label><span class="metric-label">{{ __('audit.date_from') }}</span><input class="input" type="date" name="date_from" value="{{ $vm['filters']['date_from'] ?? '' }}"></label>
    <label><span class="metric-label">{{ __('audit.date_to') }}</span><input class="input" type="date" name="date_to" value="{{ $vm['filters']['date_to'] ?? '' }}"></label>
    <div style="display:flex;gap:8px;"><button class="logout-btn" type="submit">{{ __('audit.filter') }}</button><a class="logout-btn" href="/backoffice/audit" style="text-decoration:none;display:inline-flex;align-items:center;">{{ __('audit.reset') }}</a></div>
</form>

<div class="card">
    <table class="data-table">
        <thead><tr><th>{{ __('audit.date') }}</th><th>{{ __('audit.user') }}</th><th>{{ __('audit.action') }}</th><th>{{ __('audit.entity') }}</th><th>{{ __('audit.id') }}</th><th>{{ __('audit.context') }}</th></tr></thead>
        <tbody>
        @forelse($vm['rows'] as $row)
            <tr>
                <td>{{ $row['created_at'] }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td><span class="status-badge status-na">{{ $row['action_key'] }}</span></td>
                <td>{{ $row['entity_type'] }}</td>
                <td>{{ $row['entity_id'] ?? 'N/A' }}</td>
                <td>{{ $row['context_summary'] }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="section-subtitle">{{ __('audit.no_logs') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
