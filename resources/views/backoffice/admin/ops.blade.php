@extends('backoffice.layout')

@section('title', 'Superadmin · Ops')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">Operaciones</h1>
    <div class="section-subtitle">Señales básicas para validar despliegue, runtime y configuración operativa antes de staging o producción.</div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">
    <div class="card"><div class="metric-label">App Env</div><div class="metric-value">{{ $vm['app_env'] }}</div></div>
    <div class="card"><div class="metric-label">App Debug</div><div class="metric-value">{{ $vm['app_debug'] ? 'true' : 'false' }}</div></div>
    <div class="card"><div class="metric-label">Queue</div><div class="metric-value">{{ $vm['queue_connection'] }}</div></div>
    <div class="card"><div class="metric-label">Cache</div><div class="metric-value">{{ $vm['cache_store'] }}</div></div>
    <div class="card"><div class="metric-label">DB Driver</div><div class="metric-value">{{ $vm['db_driver'] }}</div></div>
    <div class="card"><div class="metric-label">Ready</div><div class="metric-value">{{ $vm['readiness']['status'] }}</div></div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="section-heading" style="font-size:1.1rem;">Readiness</div>
    <div class="section-subtitle" style="margin-bottom:14px;">Chequeo simple de `app key`, base de datos y cache. No expone secretos.</div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        @foreach ($vm['readiness']['checks'] as $check => $ok)
            <span class="chip {{ $ok ? '' : 'read-only-badge' }}">{{ $check }}: {{ $ok ? 'ok' : 'error' }}</span>
        @endforeach
    </div>
    @if (($vm['readiness']['message'] ?? null) !== null)
        <div style="margin-top:12px;color:var(--critical);font-weight:700;">{{ $vm['readiness']['message'] }}</div>
    @endif
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="section-heading" style="font-size:1.1rem;">Backups</div>
    <div class="section-subtitle" style="margin-bottom:14px;">Ultimos backups detectados en almacenamiento no publico de Laravel.</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <span class="chip">DB backups: {{ $vm['backup_counts']['database'] }}</span>
        <span class="chip">Files backups: {{ $vm['backup_counts']['files'] }}</span>
        <span class="chip">Keep DB: {{ $vm['backup_policy']['database'] }}</span>
        <span class="chip">Keep Files: {{ $vm['backup_policy']['files'] }}</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
        <div style="padding:12px 14px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.78);">
            <strong>Database</strong>
            <div class="muted" style="margin-top:8px;">
                @if($vm['backups']['database'])
                    {{ $vm['backups']['database']['name'] }}<br>
                    {{ $vm['backups']['database']['modified_at'] }}
                @else
                    No detectado
                @endif
            </div>
        </div>
        <div style="padding:12px 14px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.78);">
            <strong>Files</strong>
            <div class="muted" style="margin-top:8px;">
                @if($vm['backups']['files'])
                    {{ $vm['backups']['files']['name'] }}<br>
                    {{ $vm['backups']['files']['modified_at'] }}
                @else
                    No detectado
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="section-heading" style="font-size:1.1rem;">Deployment Docs</div>
    <div class="section-subtitle" style="margin-bottom:14px;">Guías operativas para `.env`, logs, queues, optimize, permisos y backup.</div>
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
