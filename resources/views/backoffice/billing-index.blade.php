@extends('backoffice.layout')

@section('title', 'Facturación · '.$vm['tenant']['name'])

@section('content')
<div class="card card-soft animate-enter-down" style="margin-bottom:16px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;">
        <div>
            <h1 class="section-heading">{{ __('billing.title') }}</h1>
            <div class="section-subtitle">{{ __('billing.subtitle', ['tenant' => $vm['tenant']['name']]) }}</div>
        </div>
        <a class="cta-secondary" href="/backoffice/billing/export.xlsx">{{ __('billing.export_excel') }}</a>
    </div>
    @if($vm['subscription_ends_at'])
        <div class="section-subtitle" style="margin-top:10px;">{{ __('billing.plan_ends_at', ['date' => $vm['subscription_ends_at']]) }}</div>
    @endif
    @if($vm['can_renew'])
        <form method="POST" action="/backoffice/billing/renew" style="margin-top:12px;">
            @csrf
            <button type="submit" class="cta-secondary" style="padding:8px 16px;">{{ __('billing.renew_plan') }}</button>
        </form>
    @endif
</div>

@if(session('status'))
    <div class="status-flash" style="margin-bottom:16px;">{{ session('status') }}</div>
@endif
@if($errors->has('checkout'))
    <div class="empty-state" style="padding:14px 16px;margin-bottom:16px;color:#9a1f1f;background:#fff4f4;border-color:#f1caca;">
        {{ $errors->first('checkout') }}
    </div>
@endif

<div class="card animate-enter-down animate-enter-down-delay-1" style="margin-bottom:16px;">
    <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h3 class="panel-title">{{ __('billing.invoices') }}</h3>
        <span class="status-badge status-na">{{ __('billing.read_only') }}</span>
    </div>
    @if($vm['invoices'] === [])
        <div class="section-subtitle">{{ __('billing.no_invoices') }}</div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('billing.invoice') }}</th>
                        <th>{{ __('billing.period') }}</th>
                        <th>{{ __('billing.amount') }}</th>
                        <th>{{ __('billing.status') }}</th>
                        <th>{{ __('billing.due_date') }}</th>
                        <th>{{ __('billing.paid_at') }}</th>
                        <th>{{ __('billing.action') }}</th>
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
                            <td>
                                @if($invoice['payable'])
                                    <form method="POST" action="/backoffice/billing/invoices/{{ $invoice['id'] }}/checkout">
                                        @csrf
                                        <button type="submit" class="cta-secondary" style="padding:6px 14px;font-size:.85rem;">{{ __('billing.pay_with_paypal') }}</button>
                                    </form>
                                @else
                                    &mdash;
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card animate-enter-down animate-enter-down-delay-2">
    <h3 class="panel-title">{{ __('billing.payments') }}</h3>
    @if($vm['payments'] === [])
        <div class="section-subtitle">{{ __('billing.no_payments') }}</div>
    @else
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('billing.date') }}</th>
                        <th>{{ __('billing.invoice') }}</th>
                        <th>{{ __('billing.amount') }}</th>
                        <th>{{ __('billing.provider') }}</th>
                        <th>{{ __('billing.status') }}</th>
                        <th>{{ __('billing.reference') }}</th>
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
