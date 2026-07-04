@extends('backoffice.layout')

@section('title', 'Superadmin · Audit')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">Audit Global</h1>
    <div class="section-subtitle">Trazabilidad global para soporte, troubleshooting y auditoría operativa del SaaS.</div>
</div>

<form method="GET" class="card" style="margin-bottom:16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
    <label><span class="metric-label">Tenant</span><select class="input" name="tenant_id"><option value="">Todos</option>@foreach($vm['tenants'] as $tenant)<option value="{{ $tenant['id'] }}" @selected((string)($vm['filters']['tenant_id'] ?? '')===(string)$tenant['id'])>{{ $tenant['label'] }}</option>@endforeach</select></label>
    <label><span class="metric-label">Usuario</span><input class="input" type="text" name="user" value="{{ $vm['filters']['user'] ?? '' }}"></label>
    <label><span class="metric-label">{{ __('audit.action') }}</span><select class="input" name="action_key"><option value="">Todas</option>@foreach($vm['action_keys'] as $actionKey)<option value="{{ $actionKey }}" @selected(($vm['filters']['action_key'] ?? '')===$actionKey)>{{ $actionKey }}</option>@endforeach</select></label>
    <label><span class="metric-label">Desde</span><input class="input" type="date" name="date_from" value="{{ $vm['filters']['date_from'] ?? '' }}"></label>
    <label><span class="metric-label">Hasta</span><input class="input" type="date" name="date_to" value="{{ $vm['filters']['date_to'] ?? '' }}"></label>
    <div style="display:flex;gap:8px;"><button class="logout-btn" type="submit">Filtrar</button><a class="logout-btn" href="/backoffice/admin/audit" style="text-decoration:none;display:inline-flex;align-items:center;">Limpiar</a></div>
</form>

<div class="card">
    <table class="data-table">
        <thead><tr><th>Fecha</th><th>Tenant</th><th>Usuario</th><th>Acción</th><th>Entidad</th><th>ID</th><th>Contexto</th></tr></thead>
        <tbody>
        @forelse($vm['rows'] as $row)
            <tr>
                <td>{{ $row['created_at'] }}</td>
                <td>{{ $row['tenant'] }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td><span class="status-badge status-na">{{ $row['action_key'] }}</span></td>
                <td>{{ $row['entity_type'] }}</td>
                <td>{{ $row['entity_id'] ?? 'N/A' }}</td>
                <td>{{ $row['context_summary'] }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="section-subtitle">No hay logs para los filtros seleccionados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
