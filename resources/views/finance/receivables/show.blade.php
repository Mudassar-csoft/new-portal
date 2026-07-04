@extends('layouts.theme')

@section('title', 'Invoice Details')

@section('content')
    @php
        $statusColors = [
            'pending' => 'badge-warning',
            'partial' => 'badge-info',
            'paid' => 'badge-success',
            'overdue' => 'badge-danger',
        ];
    @endphp

    <div class="invoice-page-shell">
        @if(session('status'))
            <div class="alert alert-success no-print">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger no-print">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

      

        <section class="box-typical box-typical-dashboard panel panel-default finance-card invoice-card">
            <div class="div d-flex justify-content-between align-items-center panel-heading finance-header">

                    <h1 class="invoice-title">Invoice {{ $charge->invoice_number ?: ($charge->voucher_number ?: 'N/A') }}</h1>
                <!-- <h2 class="invoice-kicker">Finance Management</h2> -->
                <div class="invoice-toolbar no-print">
                <a href="{{ route('finance.receivables') }}" class="btn btn-inline btn-danger-outline">Back</a>
            
                <a href="{{ route('finance.receivables.print', $charge) }}" target="_blank" rel="noopener" class="btn btn-inline btn-primary-outline">Print</a>
                @if($canUpdateReceivables && (float) $charge->balance_amount > 0)
                    <button
                        type="button"
                        class="btn btn-inline btn-primary"
                        data-payment-trigger="invoice"
                        data-charge-id="{{ $charge->id }}"
                        data-payment-action="{{ route('finance.receivables.collect', $charge) }}"
                        data-invoice-number="{{ $charge->invoice_number ?: ($charge->voucher_number ?: 'N/A') }}"
                        data-customer-name="{{ $charge->student_name ?: 'N/A' }}"
                        data-balance-amount="{{ number_format((float) $charge->balance_amount, 2, '.', '') }}"
                        data-payment-ref-preview="{{ ($charge->campus->code ?? 'GEN') . '-RCV-' . now()->format('my') . '-AUTO' }}"
                    >
                        Pay Now
                    </button>
                @endif
              </div>
             </div>
            <!-- <div class="box-typical-body panel-body "> -->
                <div class="invoice-head pt-0 pl-4 pr-4 ">
                    <div>
                      
                        <div class="invoice-meta-grid">
                            <div><strong>Status:</strong> <span class="badge {{ $statusColors[$charge->status] ?? 'badge-secondary' }}">{{ ucfirst($charge->status) }}</span></div>
                            <div><strong>Invoice Date:</strong> {{ optional($charge->invoice_date)->format('Y-m-d') ?: 'N/A' }}</div>
                            <div><strong>Due Date:</strong> {{ optional($charge->due_date)->format('Y-m-d') ?: 'N/A' }}</div>
                            <div><strong>Charge Type:</strong> {{ $charge->chargeType->name ?? 'Invoice' }}</div>
                        </div>
                    </div>
                    <div class="invoice-campus-box">
                        <div class="label">Campus / Franchise</div>
                        <div class="value">{{ $charge->campus->code ?? 'N/A' }}</div>
                        <div class="text-muted">{{ $charge->campus->name ?? 'N/A' }}</div>
                    </div>
                </div>

                @if($charge->status === 'overdue')
                    <!-- <div class="alert alert-danger mt-3">
                        This invoice is overdue. Outstanding balance: <strong>Rs. {{ number_format((float) $charge->balance_amount, 0) }}</strong>
                    </div> -->
                @endif

                <div class="row invoice-address-row">
                    <div class="col-lg-6 col-md-6 pl-4">
                        <div class="invoice-address-card">
                            <div class="invoice-section-label">Bill To</div>
                            <hr>
                            <div class="invoice-party-name">{{ $charge->student_name ?: 'N/A' }}</div>
                            <div>{{ $charge->bill_to_phone ?: 'No phone provided' }}</div>
                            <div>{{ $charge->bill_to_email ?: 'No email provided' }}</div>
                            <div>{{ $charge->bill_to_address ?: 'No address provided' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 pr-4">
                        <div class="invoice-address-card invoice-totals-box">
                            <div class="invoice-totals-line"><span>Subtotal</span><strong>Rs. {{ number_format((float) $charge->amount, 0) }}</strong></div>
                            <div class="invoice-totals-line"><span>Discount</span><strong>Rs. {{ number_format((float) $charge->discount_amount, 0) }}</strong></div>
                            <div class="invoice-totals-line"><span>Total</span><strong>Rs. {{ number_format((float) $charge->net_amount, 0) }}</strong></div>
                            <div class="invoice-totals-line"><span>Paid</span><strong>Rs. {{ number_format((float) $charge->paid_amount, 0) }}</strong></div>
                            <div class="invoice-totals-line total"><span>Balance</span><strong>Rs. {{ number_format((float) $charge->balance_amount, 0) }}</strong></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered finance-table invoice-lines-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($charge->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td>Rs. {{ number_format((float) $item->unit_price, 0) }}</td>
                                    <td>Rs. {{ number_format((float) $item->line_total, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="invoice-note-box">
                            <div class="invoice-section-label">Notes</div>
                            <div>{{ $charge->notes ?: 'No notes added.' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="invoice-note-box">
                            <div class="invoice-section-label">Terms</div>
                            <div>{{ $charge->terms ?: 'No terms added.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Payment History</h3>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Proof</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charge->payments as $payment)
                                <tr>
                                    <td>{{ optional($payment->payment_date)->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td>
                                        <div>
                                            {{
                                                match ($payment->payment_method) {
                                                    'online', 'bank' => 'Online Transfer',
                                                    'cheque' => 'Cheque',
                                                    'cash' => 'Cash',
                                                    default => ucfirst($payment->payment_method ?: 'N/A'),
                                                }
                                            }}
                                        </div>
                                        @if($payment->receiver_name || $payment->depositor_name)
                                            <small class="text-muted">
                                                @if($payment->receiver_name)
                                                    Receiver: {{ $payment->receiver_name }}
                                                @endif
                                                @if($payment->depositor_name)
                                                    @if($payment->receiver_name)
                                                        |
                                                    @endif
                                                    Depositor: {{ $payment->depositor_name }}
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $payment->payment_ref_no ?: '-' }}</div>
                                        @if($payment->bank_name || $payment->account_no || $payment->transfer_id || $payment->cheque_no || $payment->cheque_date || $payment->cheque_payee_name)
                                            <small class="text-muted">
                                                @if($payment->bank_name)
                                                    Bank: {{ $payment->bank_name }}
                                                @endif
                                                @if($payment->account_no)
                                                    @if($payment->bank_name)
                                                        |
                                                    @endif
                                                    Account: {{ $payment->account_no }}
                                                @endif
                                                @if($payment->transfer_id)
                                                    @if($payment->bank_name || $payment->account_no)
                                                        |
                                                    @endif
                                                    Transfer ID: {{ $payment->transfer_id }}
                                                @endif
                                                @if($payment->cheque_no)
                                                    @if($payment->bank_name)
                                                        |
                                                    @endif
                                                    Cheque: {{ $payment->cheque_no }}
                                                @endif
                                                @if($payment->cheque_date)
                                                    @if($payment->bank_name || $payment->cheque_no)
                                                        |
                                                    @endif
                                                    Date: {{ optional($payment->cheque_date)->format('Y-m-d') }}
                                                @endif
                                                @if($payment->cheque_payee_name)
                                                    @if($payment->bank_name || $payment->cheque_no || $payment->cheque_date)
                                                        |
                                                    @endif
                                                    Payee: {{ $payment->cheque_payee_name }}
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                    <td>Rs. {{ number_format((float) $payment->amount, 0) }}</td>
                                    <td>
                                        @if($payment->attachment_path)
                                            <a href="{{ asset('storage/' . $payment->attachment_path) }}" target="_blank">View</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $payment->creator->name ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No payments recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    @if($canUpdateReceivables)
        @include('finance.receivables.partials.payment_modal', ['paymentCharge' => $charge])
    @endif
@endsection

@push('styles')
    <style>
        :root {
            --space-finance-receivables-show-1: 0 !important;
            --space-finance-receivables-show-2: 10px;
            --space-finance-receivables-show-3: 6px;
            --color-finance-receivables-show-1: #0f172a;
            --typo-finance-receivable-show-font-size-1: 12px;
            --typo-finance-receivable-show-font-weight-1: 700;
            --typo-finance-receivable-show-letter-spacing-1: .08em;
        }

        .invoice-page-shell { padding: 8px 0 16px; }
        .invoice-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: var(--space-finance-receivables-show-2);
            margin-bottom: 12px;
        }
        .invoice-card .panel-body { padding: 18px; }
        .invoice-head {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .invoice-kicker {
            font-size: var(--typo-finance-receivable-show-font-size-1);
            font-weight: var(--typo-finance-receivable-show-font-weight-1);
            letter-spacing: var(--typo-finance-receivable-show-letter-spacing-1);
            text-transform: uppercase;
            color: #0f6cbd;
            margin-bottom: var(--space-finance-receivables-show-3);
        }
        .invoice-title {
            font-size: 28px;
            margin: 0 0 10px;
            color: var(--color-finance-receivables-show-1);
        }
        .invoice-meta-grid {
            display: grid;
            gap: var(--space-finance-receivables-show-3);
            color: #475569;
        }
        .invoice-campus-box,
        .invoice-address-card,
        .invoice-note-box {
            border: 1px solid #e7edf5;
            border-radius: 10px;
            padding: 14px;
            background: #fbfdff;
            height: 100%;
        }
        .invoice-campus-box .label,
        .invoice-section-label {
            font-size: var(--typo-finance-receivable-show-font-size-1);
            text-transform: uppercase;
            letter-spacing: var(--typo-finance-receivable-show-letter-spacing-1);
            color: #64748b;
            margin-bottom: 8px;
        }
        .invoice-campus-box .value,
        .invoice-party-name {
            font-size: 20px;
            font-weight: var(--typo-finance-receivable-show-font-weight-1);
            color: var(--color-finance-receivables-show-1);
        }
        .invoice-address-row { margin-top: 16px; }
        .invoice-totals-line {
            display: flex;
            justify-content: space-between;
            gap: var(--space-finance-receivables-show-2);
            padding: 6px 0;
            border-bottom: 1px solid #edf2f7;
        }
        .invoice-totals-line.total {
            border-bottom: 0;
            padding-top: var(--space-finance-receivables-show-2);
            margin-top: 4px;
            font-size: 16px;
        }
        .invoice-lines-table thead th,
        .finance-table thead th {
            background: #1ea7ff;
            color: #fff;
        }
        .required::after { content: ' *'; color: #e53935; }
        @media print {
            .no-print,
            .site-header,
            .side-menu,
            .page-content-header,
            .footer,
            .mobile-menu-left-overlay {
                display: none !important;
            }
            .page-content {
                margin: var(--space-finance-receivables-show-1);
                padding: var(--space-finance-receivables-show-1);
            }
            .invoice-card {
                border: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
@endpush
