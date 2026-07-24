@extends('backoffice.layout')

@section('title', 'Superadmin · Ops')

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('admin.ops_title') }}</h1>
    <div class="section-subtitle">{{ __('admin.ops_subtitle') }}</div>
</div>

<div class="animate-enter-down animate-enter-down-delay-1" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
    <div class="card"><div class="metric-label">{{ __('admin.app_env') }}</div><div class="metric-value">{{ $vm['app_env'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.app_debug') }}</div><div class="metric-value">{{ $vm['app_debug'] ? 'true' : 'false' }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.queue') }}</div><div class="metric-value">{{ $vm['queue_connection'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.cache') }}</div><div class="metric-value">{{ $vm['cache_store'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.db_driver') }}</div><div class="metric-value">{{ $vm['db_driver'] }}</div></div>
    <div class="card"><div class="metric-label">{{ __('admin.ready') }}</div><div class="metric-value">{{ $vm['readiness']['status'] }}</div></div>
</div>

<div class="card animate-enter-down animate-enter-down-delay-2" style="margin-bottom:16px;">
    <h3 class="panel-title">{{ __('admin.readiness') }}</h3>
    <div class="section-subtitle" style="margin-bottom:14px;">{{ __('admin.readiness_sub') }}</div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        @foreach ($vm['readiness']['checks'] as $check => $ok)
            <span class="chip {{ $ok ? '' : 'read-only-badge' }}">{{ $check }}: {{ $ok ? 'ok' : 'error' }}</span>
        @endforeach
    </div>
    @if (($vm['readiness']['message'] ?? null) !== null)
        <div style="margin-top:12px;color:var(--critical);font-weight:700;">{{ $vm['readiness']['message'] }}</div>
    @endif
</div>

<div class="card animate-enter-down animate-enter-down-delay-3" style="margin-bottom:16px;">
    <h3 class="panel-title">{{ __('admin.backups') }}</h3>
    <div class="section-subtitle" style="margin-bottom:14px;">{{ __('admin.backups_sub') }}</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <span class="chip">DB backups: {{ $vm['backup_counts']['database'] }}</span>
        <span class="chip">Files backups: {{ $vm['backup_counts']['files'] }}</span>
        <span class="chip">Keep DB: {{ $vm['backup_policy']['database'] }}</span>
        <span class="chip">Keep Files: {{ $vm['backup_policy']['files'] }}</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
        <div style="padding:12px 14px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.78);">
            <strong>{{ __('admin.database_backup') }}</strong>
            <div class="muted" style="margin-top:8px;">
                @if($vm['backups']['database'])
                    {{ $vm['backups']['database']['name'] }}<br>
                    {{ $vm['backups']['database']['modified_at'] }}
                @else
                    {{ __('admin.not_detected') }}
                @endif
            </div>
        </div>
        <div style="padding:12px 14px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.78);">
            <strong>{{ __('admin.files_backup') }}</strong>
            <div class="muted" style="margin-top:8px;">
                @if($vm['backups']['files'])
                    {{ $vm['backups']['files']['name'] }}<br>
                    {{ $vm['backups']['files']['modified_at'] }}
                @else
                    {{ __('admin.not_detected') }}
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card animate-enter-down">
    <h3 class="panel-title">{{ __('admin.deployment_docs') }}</h3>
    <div class="section-subtitle" style="margin-bottom:14px;">{{ __('admin.deployment_docs_sub') }}</div>
    <div style="display:grid;gap:10px;">
        @foreach ($vm['docs'] as $doc)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.78);">
                <strong>{{ $doc['label'] }}</strong>
                <code>{{ $doc['href'] }}</code>
            </div>
        @endforeach
    </div>
</div>
@endsection
