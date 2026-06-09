@php
    $invoiceNumber = $charge->invoice_number ?: ($charge->voucher_number ?: 'N/A');
    $invoiceDate = optional($charge->invoice_date)->format('M d, Y') ?: 'N/A';
    $dueDate = optional($charge->due_date)->format('M d, Y') ?: 'N/A';
    $lastPaymentDate = optional($charge->payments->first()?->payment_date)->format('M d, Y') ?: 'Not recorded';
    $statusLabel = ucfirst((string) $charge->status);
    $accountLabel = $charge->campus->code ?? ('#' . str_pad((string) ($charge->getKey() ?: 0), 6, '0', STR_PAD_LEFT));
    $notesText = trim(implode("\n", array_filter([
        $charge->terms,
        $charge->notes,
    ])));
    $notesText = $notesText !== '' ? $notesText : 'Thank you for your business. We appreciate your prompt payment.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #171717;
            background: #eef2f5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            width: 210mm;
            margin: 14px auto 10px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .toolbar a,
        .toolbar button {
            border: 1px solid #1188cc;
            background: #fff;
            color: #1188cc;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button {
            background: #1188cc;
            color: #fff;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff url('{{ asset('theme/img/invoice-print-template.jpg') }}') center/cover no-repeat;
            position: relative;
            overflow: hidden;
            padding: 62mm 14mm 18mm;
        }

        .bill-to {
            width: 72mm;
            min-height: 34mm;
        }

        .bill-to-name {
            font-size: 7mm;
            line-height: 1.08;
            font-weight: 700;
            margin-bottom: 3mm;
        }

        .bill-to-line {
            font-size: 4.6mm;
            line-height: 1.35;
            margin-bottom: 1mm;
            word-break: break-word;
        }

        .summary-grid {
            margin-top: 15mm;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8mm;
            align-items: start;
        }

        .summary-label {
            font-size: 5.1mm;
            font-weight: 700;
            margin-bottom: 2.5mm;
        }

        .summary-value {
            font-size: 6.4mm;
            font-weight: 700;
            line-height: 1.1;
            word-break: break-word;
        }

        .summary-meta {
            margin-top: 1.5mm;
            font-size: 3.8mm;
            color: #5a5a5a;
        }

        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            margin-top: 9mm;
        }

        .items-table th {
            background: #17dfe3;
            color: #fff;
            padding: 5mm 4mm;
            font-size: 4.8mm;
            font-weight: 700;
            text-align: left;
        }

        .items-table th.text-center,
        .items-table td.text-center {
            text-align: center;
        }

        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }

        .items-table td {
            background: #ececec;
            padding: 4.5mm 4mm;
            font-size: 4.5mm;
            vertical-align: top;
        }

        .items-table tbody tr + tr td {
            border-top: 2mm solid #fff;
        }

        .description-title {
            display: block;
            font-size: 5.6mm;
            font-weight: 700;
            margin-bottom: 1.4mm;
        }

        .description-subtitle {
            display: block;
            color: #4b4b4b;
            line-height: 1.35;
            white-space: pre-line;
            word-break: break-word;
        }

        .empty-row td {
            color: transparent;
            height: 18mm;
        }

        .tracking-panel {
            margin-top: 12mm;
            min-height: 11mm;
            background: #ececec;
            padding: 4mm 6mm;
            display: grid;
            grid-template-columns: 1.1fr repeat(4, 1fr);
            gap: 4mm;
            align-items: start;
        }

        .tracking-title {
            font-size: 4.8mm;
            font-weight: 700;
            line-height: 1.2;
        }

        .tracking-title span {
            display: none;
        }

        .tracking-stat-label {
            font-size: 3.1mm;
            color: #626262;
            margin-bottom: 0.8mm;
        }

        .tracking-stat-value {
            font-size: 4.1mm;
            font-weight: 700;
            line-height: 1.15;
        }

        .bottom-grid {
            margin-top: 4mm;
            display: grid;
            grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr);
            gap: 9mm;
        }

        .notes-panel,
        .totals-panel {
            background: #ececec;
            min-height: 18mm;
            padding: 5mm 6mm;
        }

        .notes-title {
            color: #17dfe3;
            font-size: 5.2mm;
            font-weight: 700;
            margin-bottom: 2mm;
        }

        .notes-text {
            font-size: 3.8mm;
            line-height: 1.4;
            white-space: pre-line;
        }

        .notes-trust {
            margin-top: 3mm;
            font-size: 4.2mm;
            font-weight: 700;
            color: #b8b8b8;
        }

        .totals-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            font-size: 4.4mm;
            margin-bottom: 2.4mm;
        }

        .totals-line:last-child {
            margin-bottom: 0;
        }

        .totals-line strong {
            font-weight: 700;
            white-space: nowrap;
        }

        .totals-line.balance {
            padding-top: 2mm;
            border-top: 1px solid #c8c8c8;
        }

        .totals-line.balance strong {
            color: #1188cc;
        }

        .screen-only {
            display: block;
        }

        @media print {
            body {
                background: #fff;
            }

            .screen-only {
                display: none !important;
            }

            .sheet {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar screen-only">
        @if($charge->getKey())
            <a href="{{ route('finance.receivables.show', $charge) }}">Back To Invoice</a>
        @endif
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <main class="sheet">
        <section class="bill-to">
            <div class="bill-to-name">{{ $charge->student_name ?: 'N/A' }}</div>
            @if($charge->bill_to_phone)
                <div class="bill-to-line">Phone : {{ $charge->bill_to_phone }}</div>
            @endif
            @if($charge->bill_to_email)
                <div class="bill-to-line">Email : {{ $charge->bill_to_email }}</div>
            @endif
            @if($charge->bill_to_address)
                <div class="bill-to-line">Address: {{ $charge->bill_to_address }}</div>
            @endif
        </section>

        <section class="summary-grid">
            <div>
                <div class="summary-label">Total Due //</div>
                <div class="summary-value">{{ number_format((float) $charge->net_amount, 0) }}</div>
            </div>
            <div>
                <div class="summary-label">Invoice No. //</div>
                <div class="summary-value">{{ $invoiceNumber }}</div>
                <div class="summary-meta">Campus: {{ $charge->campus->code ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="summary-label">Date //</div>
                <div class="summary-value">{{ $invoiceDate }}</div>
            </div>
            <div>
                <div class="summary-label">Account //</div>
                <div class="summary-value">{{ $accountLabel }}</div>
                <div class="summary-meta">Due: {{ $dueDate }} | Status: {{ $statusLabel }}</div>
            </div>
        </section>

        <table class="items-table">
            <colgroup>
                <col style="width: 48%;">
                <col style="width: 12%;">
                <col style="width: 14%;">
                <col style="width: 13%;">
                <col style="width: 13%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Unit</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($charge->items as $item)
                    <tr>
                        <td>
                            <span class="description-title">{{ $item->description }}</span>
                            <span class="description-subtitle">
                                Charge Type: {{ $charge->chargeType->name ?? 'Invoice Item' }}
                                @if($charge->remarks)
{{ $charge->remarks }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                        <td class="text-right">{{ number_format((float) $item->line_total, 0) }}</td>
                        <td class="text-right">{{ number_format((float) $item->unit_price, 0) }}</td>
                        <td class="text-right">{{ number_format((float) $item->line_total, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>
                            <span class="description-title">Invoice Item</span>
                            <span class="description-subtitle">No line items were recorded for this invoice.</span>
                        </td>
                        <td class="text-center">-</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <section class="tracking-panel">
            <div class="tracking-title">
                Invoice Tracking
                <span>Use this section to confirm whether the invoice is still open or already settled.</span>
            </div>
            <div>
                <div class="tracking-stat-label">Status</div>
                <div class="tracking-stat-value">{{ $statusLabel }}</div>
            </div>
            <div>
                <div class="tracking-stat-label">Paid Amount</div>
                <div class="tracking-stat-value">Rs. {{ number_format((float) $charge->paid_amount, 0) }}</div>
            </div>
            <div>
                <div class="tracking-stat-label">Outstanding</div>
                <div class="tracking-stat-value">Rs. {{ number_format((float) $charge->balance_amount, 0) }}</div>
            </div>
            <div>
                <div class="tracking-stat-label">Last Payment</div>
                <div class="tracking-stat-value">{{ $lastPaymentDate }}</div>
            </div>
        </section>

        <section class="bottom-grid">
            <div class="notes-panel">
                <div class="notes-title">Terms &amp; Conditions/Notes:</div>
                <div class="notes-text">{{ $notesText }}</div>
                <div class="notes-trust">Your Trust and Support Are Valued</div>
            </div>

            <div class="totals-panel">
                <div class="totals-line">
                    <span>Subtotal</span>
                    <strong>Rs. {{ number_format((float) $charge->amount, 0) }}</strong>
                </div>
                <div class="totals-line">
                    <span>Discount</span>
                    <strong>Rs. {{ number_format((float) $charge->discount_amount, 0) }}</strong>
                </div>
                <div class="totals-line">
                    <span>Grand Total</span>
                    <strong>Rs. {{ number_format((float) $charge->net_amount, 0) }}</strong>
                </div>
                <div class="totals-line">
                    <span>Paid</span>
                    <strong>Rs. {{ number_format((float) $charge->paid_amount, 0) }}</strong>
                </div>
                <div class="totals-line balance">
                    <span>Balance</span>
                    <strong>Rs. {{ number_format((float) $charge->balance_amount, 0) }}</strong>
                </div>
            </div>
        </section>
    </main>

    <script>
        window.addEventListener('load', function () {
            if (window.location.search.indexOf('autoprint=0') === -1) {
                window.setTimeout(function () {
                    window.print();
                }, 250);
            }
        });
    </script>
</body>
</html>
