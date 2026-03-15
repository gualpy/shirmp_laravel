@extends('backoffice.layout')

@section('title', 'Superadmin · Billing')

@section('content')
<div class="card card-soft" style="margin-bottom:16px;">
    <h1 class="section-heading">Billing global</h1>
    <div class="section-subtitle">Vista consolidada de invoices y pagos del SaaS.</div>
</div>

<div class="card" style="margin-bottom:16px;">
    <h3 class="panel-title">Invoices</h3>
    @if($vm['invoices'] === [])
        <div class="section-subtitle">No hay invoices registradas.</div>
    @else
        <div style="overflow:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tenant</th>
                        <th>Periodo</th>
                        <th>Monto</th>
                        <th>Status</th>
                        <th>Due at</th>
                        <th>Paid at</th>
                        <th>Provider</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vm['invoices'] as $invoice)
                        <tr>
                            <td><strong>{{ $invoice['invoice_number'] }}</strong></td>
                            <td>
                                {{ $invoice['tenant_name'] }}<br>
                                @if($invoice['tenant_id'])
                                    <a href="/backoffice/admin/tenants/{{ $invoice['tenant_id'] }}/billing" class="muted" style="text-decoration:none;">{{ $invoice['tenant_slug'] }}</a>
                                @else
                                    <span class="muted">{{ $invoice['tenant_slug'] }}</span>
                                @endif
                            </td>
                            <td>{{ $invoice['period'] }}</td>
                            <td>${{ $invoice['amount_usd'] }}</td>
                            <td><span class="status-badge status-{{ $invoice['status'] }}">{{ $invoice['status'] }}</span></td>
                            <td>{{ $invoice['due_at'] ?? 'N/A' }}</td>
                            <td>{{ $invoice['paid_at'] ?? 'N/A' }}</td>
                            <td>{{ $invoice['provider'] }}</td>
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
        <div class="section-subtitle">No hay pagos registrados.</div>
    @else
        <div style="overflow:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tenant</th>
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
                            <td>{{ $payment['tenant_name'] ?? 'N/A' }}</td>
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
