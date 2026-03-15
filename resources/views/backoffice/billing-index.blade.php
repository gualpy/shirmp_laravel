@extends('backoffice.layout')

@section('title', 'Billing · '.$vm['tenant']['name'])

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
        <div>
            <h1 class="section-heading">Facturación</h1>
            <div class="section-subtitle">Historial de invoices y pagos para {{ $vm['tenant']['name'] }}.</div>
        </div>
        <a class="cta-secondary" href="/backoffice/billing/export.xlsx">Exportar Excel</a>
    </div>
</div>

<div class="card animate-enter-down animate-enter-down-delay-1" style="margin-bottom:16px;">
    <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h3 class="panel-title">Invoices</h3>
        <span class="status-badge status-na">Solo lectura</span>
    </div>
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
                        <th>Due date</th>
                        <th>Paid at</th>
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card animate-enter-down animate-enter-down-delay-2">
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
