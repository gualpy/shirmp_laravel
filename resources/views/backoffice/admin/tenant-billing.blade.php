@extends('backoffice.layout')

@section('title', 'Superadmin · Facturación '.$vm['tenant']['name'])

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
    <div>
        <h1 class="section-heading">{{ __('admin.tenant_billing_title', ['name' => $vm['tenant']['name']]) }}</h1>
        <div class="section-subtitle">Slug: {{ $vm['tenant']['slug'] }} · Plan: {{ $vm['subscription']['plan_code'] ?? 'N/A' }} · Status: {{ $vm['subscription']['status'] ?? 'N/A' }}</div>
    </div>
    <a class="cta-secondary" href="/backoffice/admin/tenants/{{ $vm['tenant']['id'] }}">{{ __('admin.back_to_tenant') }}</a>
</div>

@if(session('status'))
    <div class="card" style="margin-bottom:16px;border-color:#b9e2cf;background:#eefaf4;color:#176448;">{{ session('status') }}</div>
@endif

<div class="animate-enter-down animate-enter-down-delay-1" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-bottom:16px;">
    <div class="card">
        <h3 class="panel-title">{{ __('admin.create_invoice') }}</h3>
        <form method="POST" action="/backoffice/admin/tenants/{{ $vm['tenant']['id'] }}/billing/invoices" style="display:grid;gap:10px;">
            @csrf
            <label>
                <div class="metric-label">{{ __('admin.period_start') }}</div>
                <input class="input" type="date" name="billing_period_start" value="{{ old('billing_period_start', $vm['invoice_defaults']['billing_period_start']) }}">
            </label>
            <label>
                <div class="metric-label">{{ __('admin.period_end') }}</div>
                <input class="input" type="date" name="billing_period_end" value="{{ old('billing_period_end', $vm['invoice_defaults']['billing_period_end']) }}">
            </label>
            <label>
                <div class="metric-label">{{ __('admin.amount_usd') }}</div>
                <input class="input" type="number" step="0.01" min="0" name="amount_usd" value="{{ old('amount_usd', '120.00') }}">
            </label>
            <label>
                <div class="metric-label">{{ __('admin.issued_at') }}</div>
                <input class="input" type="datetime-local" name="issued_at" value="{{ old('issued_at', $vm['invoice_defaults']['issued_at']) }}">
            </label>
            <label>
                <div class="metric-label">{{ __('admin.due_at_label') }}</div>
                <input class="input" type="datetime-local" name="due_at" value="{{ old('due_at', $vm['invoice_defaults']['due_at']) }}">
            </label>
            <label>
                <div class="metric-label">{{ __('cycle.notes') }}</div>
                <textarea class="input" name="notes" rows="3">{{ old('notes') }}</textarea>
            </label>
            <button class="cta-primary" type="submit">{{ __('admin.create_invoice') }}</button>
        </form>
    </div>

    <div class="card">
        <h3 class="panel-title">{{ __('admin.manual_payments') }}</h3>
        <div class="section-subtitle">{{ __('admin.manual_payments_note') }}</div>
    </div>
</div>

<div class="card animate-enter-down animate-enter-down-delay-2" style="margin-bottom:16px;">
    <h3 class="panel-title">{{ __('admin.invoices') }}</h3>
    @if($vm['invoices'] === [])
        <div class="section-subtitle">{{ __('admin.no_tenant_invoices') }}</div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.invoice') }}</th>
                        <th>{{ __('admin.period') }}</th>
                        <th>{{ __('admin.amount') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th>{{ __('admin.due_at') }}</th>
                        <th>{{ __('admin.paid_at') }}</th>
                        <th>{{ __('admin.provider') }}</th>
                        <th>{{ __('app.actions') }}</th>
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
                                            <button class="cta-secondary" type="submit">{{ __('admin.mark_paid') }}</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="/backoffice/admin/billing/invoices/{{ $invoice['id'] }}/payments" style="display:grid;gap:8px;">
                                        @csrf
                                        <input class="input" type="number" step="0.01" min="0" name="amount_usd" value="{{ $invoice['amount_usd'] }}">
                                        <input class="input" type="text" name="provider_reference" placeholder="{{ __('admin.reference_placeholder') }}">
                                        <button class="cta-secondary" type="submit">{{ __('admin.register_payment') }}</button>
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

<div class="card animate-enter-down animate-enter-down-delay-3">
    <h3 class="panel-title">{{ __('admin.payments') }}</h3>
    @if($vm['payments'] === [])
        <div class="section-subtitle">{{ __('admin.no_tenant_payments') }}</div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.date') }}</th>
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
