@extends('backoffice.layout')

@section('title', 'Superadmin · Billing')

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <h1 class="section-heading">{{ __('admin.billing_title') }}</h1>
    <div class="section-subtitle">{{ __('admin.billing_subtitle') }}</div>
</div>

<div class="card animate-enter-down animate-enter-down-delay-1" style="margin-bottom:16px;">
    <h3 class="panel-title">{{ __('admin.invoices') }}</h3>
    @if($vm['invoices'] === [])
        <div class="section-subtitle">{{ __('admin.no_invoices') }}</div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.invoice') }}</th>
                        <th>{{ __('admin.tenant') }}</th>
                        <th>{{ __('admin.period') }}</th>
                        <th>{{ __('admin.amount') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th>{{ __('admin.due_at') }}</th>
                        <th>{{ __('admin.paid_at') }}</th>
                        <th>{{ __('admin.provider') }}</th>
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

<div class="card animate-enter-down animate-enter-down-delay-2">
    <h3 class="panel-title">{{ __('admin.payments') }}</h3>
    @if($vm['payments'] === [])
        <div class="section-subtitle">{{ __('admin.no_payments') }}</div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.date') }}</th>
                        <th>{{ __('admin.tenant') }}</th>
                        <th>{{ __('admin.invoice') }}</th>
                        <th>{{ __('admin.amount') }}</th>
                        <th>{{ __('admin.provider') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th>{{ __('admin.reference') }}</th>
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
