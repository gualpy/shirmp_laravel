@extends('backoffice.layout')

@section('title', 'Superadmin · Billing '.$vm['tenant']['name'])

@section('content')
<div class="card card-soft" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">Billing · {{ $vm['tenant']['name'] }}</h1>
        <div class="section-subtitle">Slug: {{ $vm['tenant']['slug'] }} · Plan: {{ $vm['subscription']['plan_code'] ?? 'N/A' }} · Status: {{ $vm['subscription']['status'] ?? 'N/A' }}</div>
    </div>
    <a class="cta-secondary" href="/backoffice/admin/tenants/{{ $vm['tenant']['id'] }}">Volver al tenant</a>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-bottom:16px;">
    <div class="card">
        <h3 class="panel-title">Crear invoice</h3>
        <form method="POST" action="/backoffice/admin/tenants/{{ $vm['tenant']['id'] }}/billing/invoices" style="display:grid;gap:10px;">
            @csrf
            <label>
                <div class="metric-label">Periodo inicio</div>
                <input class="input" type="date" name="billing_period_start" value="{{ old('billing_period_start', $vm['invoice_defaults']['billing_period_start']) }}">
            </label>
            <label>
                <div class="metric-label">Periodo fin</div>
                <input class="input" type="date" name="billing_period_end" value="{{ old('billing_period_end', $vm['invoice_defaults']['billing_period_end']) }}">
            </label>
            <label>
                <div class="metric-label">Monto USD</div>
                <input class="input" type="number" step="0.01" min="0" name="amount_usd" value="{{ old('amount_usd', '120.00') }}">
            </label>
            <label>
                <div class="metric-label">Issued at</div>
                <input class="input" type="datetime-local" name="issued_at" value="{{ old('issued_at', $vm['invoice_defaults']['issued_at']) }}">
            </label>
            <label>
                <div class="metric-label">Due at</div>
                <input class="input" type="datetime-local" name="due_at" value="{{ old('due_at', $vm['invoice_defaults']['due_at']) }}">
            </label>
            <label>
                <div class="metric-label">Notas</div>
                <textarea class="input" name="notes" rows="3">{{ old('notes') }}</textarea>
            </label>
            <button class="cta-primary" type="submit">Crear invoice</button>
        </form>
    </div>

    <div class="card">
        <h3 class="panel-title">Pagos manuales</h3>
        <div class="section-subtitle">Registra pagos por invoice directamente desde la tabla inferior para mantener contexto y evitar errores de asignación.</div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <h3 class="panel-title">Invoices</h3>
    @if($vm['invoices'] === [])
        <div class="section-subtitle">No hay invoices registradas para este tenant.</div>
    @else
        <div style="overflow:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Periodo</th>
                        <th>Monto</th>
                        <th>Status</th>
                        <th>Due at</th>
                        <th>Paid at</th>
                        <th>Provider</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vm['invoices'] as $invoice)
                        <tr>
                            <td><strong>{{ $invoice['invoice_number'] }}</strong></td>
                            <td>{{ $invoice['period'] }}</td>
                            <td>${{ $invoice['amount_usd'] }}</td>
                            <td><span class="status-badge status-{{ $invoice['status'] }}">{{ $invoice['status'] }}</span></td>
                            <td>{{ $invoice['due_at'] ?? 'N/A' }}</td>
                            <td>{{ $invoice['paid_at'] ?? 'N/A' }}</td>
                            <td>{{ $invoice['provider'] }}</td>
                            <td>
                                <div style="display:grid;gap:8px;min-width:220px;">
                                    @if($invoice['status'] !== 'paid')
                                        <form method="POST" action="/backoffice/admin/billing/invoices/{{ $invoice['id'] }}/mark-paid" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                            @csrf
                                            <input class="input" type="hidden" name="payment_method" value="manual">
                                            <button class="cta-secondary" type="submit">Marcar pagada</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="/backoffice/admin/billing/invoices/{{ $invoice['id'] }}/payments" style="display:grid;gap:8px;">
                                        @csrf
                                        <input class="input" type="number" step="0.01" min="0" name="amount_usd" value="{{ $invoice['amount_usd'] }}">
                                        <input class="input" type="text" name="provider_reference" placeholder="Referencia">
                                        <button class="cta-secondary" type="submit">Registrar pago manual</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card">
    <h3 class="panel-title">Payments</h3>
    @if($vm['payments'] === [])
        <div class="section-subtitle">No hay pagos registrados todavía.</div>
    @else
        <div style="overflow:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Invoice</th>
                        <th>Monto</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vm['payments'] as $payment)
                        <tr>
                            <td>{{ $payment['paid_at'] ?? 'N/A' }}</td>
                            <td>{{ $payment['invoice_number'] }}</td>
                            <td>${{ $payment['amount_usd'] }}</td>
                            <td>{{ $payment['provider'] }}</td>
                            <td><span class="status-badge status-{{ $payment['status'] }}">{{ $payment['status'] }}</span></td>
                            <td>{{ $payment['provider_reference'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
