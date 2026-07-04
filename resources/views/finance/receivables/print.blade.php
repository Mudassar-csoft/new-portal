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
        :root {
            --dimension-finance-receivables-print-1: 100%;
            --dimension-finance-receivables-print-2: 12mm;
            --dimension-finance-receivables-print-3: 18mm;
            --dimension-finance-receivables-print-4: 210mm;
            --dimension-finance-receivables-print-5: 26mm;
            --dimension-finance-receivables-print-6: 31mm;
            --dimension-finance-receivables-print-7: 68mm;
            --space-finance-receivables-print-1: 10px;
            --space-finance-receivables-print-2: 2.4mm;
            --space-finance-receivables-print-3: 20px;
            --space-finance-receivables-print-4: 2mm;
            --space-finance-receivables-print-5: 3.2mm 3mm;
            --space-finance-receivables-print-6: 3mm;
            --space-finance-receivables-print-7: 5mm;
            --color-finance-receivables-print-1: #0f8ef2;
            --color-finance-receivables-print-2: #1188cc;
            --color-finance-receivables-print-3: #2e2e32;
            --color-finance-receivables-print-4: #d8e2e7;
            --color-finance-receivables-print-5: #fff;
            --color-finance-receivables-print-6: rgba(238, 242, 247, 0.96);
        }

        :root {
            --dimension-finance-receivables-print-1: 100%;
            --dimension-finance-receivables-print-2: 12mm;
            --dimension-finance-receivables-print-3: 18mm;
            --dimension-finance-receivables-print-4: 210mm;
            --dimension-finance-receivables-print-5: 26mm;
            --dimension-finance-receivables-print-6: 31mm;
            --dimension-finance-receivables-print-7: 68mm;
            --space-finance-receivables-print-1: 10px;
            --space-finance-receivables-print-2: 2.4mm;
            --space-finance-receivables-print-3: 20px;
            --space-finance-receivables-print-4: 2mm;
            --space-finance-receivables-print-5: 3.2mm 3mm;
            --space-finance-receivables-print-6: 3mm;
            --space-finance-receivables-print-7: 5mm;
            --typo-finance-receivables-print-font-size-1: 14px;
            --typo-finance-receivables-print-font-size-2: 20px;
            --typo-finance-receivables-print-font-weight-3: 600;
            --typo-finance-receivables-print-font-size-4: 7.4mm;
            --typo-finance-receivables-print-line-height-5: 1.1;
            --typo-finance-receivables-print-font-weight-6: 700;
            --typo-finance-receivables-print-font-weight-7: 500;
            --typo-finance-receivables-print-line-height-8: 1;
            --typo-finance-receivables-print-font-size-9: 3.7mm;
            --typo-finance-receivables-print-line-height-10: 1.35;
            --typo-finance-receivables-print-font-size-11: 3.5mm;
            --typo-finance-receivables-print-font-size-12: 3.8mm;
            --typo-finance-receivables-print-line-height-13: 1.2;
            --typo-finance-receivables-print-font-size-14: 16px;
        }0___

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
            width: var(--dimension-finance-receivables-print-4);
            margin: 14px auto 10px;
            display: flex;
            justify-content: flex-end;
            gap: var(--space-finance-receivables-print-1);
        }

        .toolbar a,
        .toolbar button {
            border: 1px solid var(--color-finance-receivables-print-2);
            background: var(--color-finance-receivables-print-5);
            color: var(--color-finance-receivables-print-2);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: var(--typo-finance-receivables-print-font-size-1);
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button {
            background: var(--color-finance-receivables-print-2);
            color: var(--color-finance-receivables-print-5);
        }

        .sheet {
            width: var(--dimension-finance-receivables-print-4);
            min-height: 297mm;
            margin: 0 auto;
            background: var(--color-finance-receivables-print-5);
            position: relative;
            overflow: hidden;
            padding: 12mm 17mm 68mm;
        }

        .brand-header {
            display: flex;
            align-items: flex-start;
            gap: var(--space-finance-receivables-print-1);
            width: var(--dimension-finance-receivables-print-1);
        }

        .brand-logo {
            width: 323px;
            height: 100px;
            display: block;
        }

        .brand-services {
            padding-top: 5px;
            color: #0a0a0a;
            font-size: var(--typo-finance-receivables-print-font-size-2);
            /* line-height: 1.25; */
            font-weight: var(--typo-finance-receivables-print-font-weight-3);
        }

        .company-name {
            margin-top: 6mm;
            color: #20262d;
            font-size: var(--typo-finance-receivables-print-font-size-4);
            line-height: var(--typo-finance-receivables-print-line-height-5);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
        }

        .invoice-hero {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            margin-left: 30px !important;
            margin-right: -17mm;
        }

        .invoice-to-title {
            font-size: var(--typo-finance-receivables-print-font-size-4);
            font-weight: var(--typo-finance-receivables-print-font-weight-7);
            line-height: var(--typo-finance-receivables-print-line-height-8);


        }

        .invoice-title-band {
            background: #ececec;
            width: var(--dimension-finance-receivables-print-1);
            min-height: var(--dimension-finance-receivables-print-3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0c0c0c;
            font-size: var(--typo-finance-receivables-print-font-size-2);
            line-height: var(--typo-finance-receivables-print-line-height-8);
            font-weight: 800;
            text-transform: uppercase;
            padding:var(--space-finance-receivables-print-3);
        }

        .bill-to {
            margin-top: 9mm;
            width: 72mm;
            min-height: var(--dimension-finance-receivables-print-5);
        }

        .bill-to-name {
            font-size: 6.2mm;
            line-height: 1.08;
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            margin-bottom: var(--space-finance-receivables-print-2);
        }

        .bill-to-line {
            font-size: var(--typo-finance-receivables-print-font-size-9);
            line-height: var(--typo-finance-receivables-print-line-height-10);
            margin-bottom: 1mm;
            word-break: break-word;
        }

        .summary-grid {
            margin-top: var(--space-finance-receivables-print-7);
            width: var(--dimension-finance-receivables-print-1);
            display: grid;
            grid-template-columns: repeat(4, minmax(0, auto));
            column-gap: 12mm;
            padding: 1mm 0 0mm;
            /* border-bottom: 1px solid #f0f0f0; */
            background: var(--color-finance-receivables-print-5);
        }

        .summary-label {
            color: var(--color-finance-receivables-print-3);
            font-size: 3.9mm;
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            line-height: var(--typo-finance-receivables-print-line-height-5);
            letter-spacing: 0;
            white-space: nowrap;
        }

        .summary-value {
            margin-top: 2.2mm;
            color: var(--color-finance-receivables-print-3);
            font-size: var(--typo-finance-receivables-print-font-size-1);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            line-height: 1.12;
            letter-spacing: 0;
            word-break: break-word;
        }

        .items-table {
            width: var(--dimension-finance-receivables-print-1);
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            margin-top: var(--space-finance-receivables-print-7);
            border: 1px solid var(--color-finance-receivables-print-4);
            background: rgba(255, 255, 255, 0.96);
        }

        .items-table th {
            background: #00a8ff;
            color: var(--color-finance-receivables-print-5);
            border: 1px solid var(--color-finance-receivables-print-1);
            padding: var(--space-finance-receivables-print-5);
            font-size: var(--typo-finance-receivables-print-font-size-9);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
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
            background: var(--color-finance-receivables-print-5);
            border: 1px solid var(--color-finance-receivables-print-4);
            padding: var(--space-finance-receivables-print-5);
            font-size: var(--typo-finance-receivables-print-font-size-11);
            vertical-align: top;
        }

        .items-table tbody tr + tr td {
            border-top: 1px solid var(--color-finance-receivables-print-4);
        }

        .description-title {
            display: block;
            font-size: var(--typo-finance-receivables-print-font-size-12);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            margin-bottom: 1.4mm;
        }

        .description-subtitle {
            display: block;
            color: #4b4b4b;
            line-height: var(--typo-finance-receivables-print-line-height-10);
            /* white-space: pre-line; */
            word-break: break-word;
        }

        .empty-row td {
            color: transparent;
            height: var(--dimension-finance-receivables-print-3);
        }

        .tracking-panel {
            margin-top: var(--space-finance-receivables-print-7);
            min-height: 11mm;
            background: var(--color-finance-receivables-print-6);
            border: 1px solid var(--color-finance-receivables-print-4);
            padding: 3.2mm 4mm;
            display: grid;
            grid-template-columns: 1.1fr repeat(4, 1fr);
            gap: var(--space-finance-receivables-print-6);
            align-items: start;
        }

        .tracking-title {
            font-size: var(--typo-finance-receivables-print-font-size-9);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            line-height: var(--typo-finance-receivables-print-line-height-13);
        }

        .tracking-title span {
            display: none;
        }

        .tracking-stat-label {
            font-size: 3.2mm;
            color: #626262;
            margin-bottom: 0.8mm;
        }

        .tracking-stat-value {
            font-size: var(--typo-finance-receivables-print-font-size-11);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            line-height: 1.15;
        }

        .bottom-grid {
            margin-top: var(--space-finance-receivables-print-7);
            display: grid;
            grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr);
            gap: var(--space-finance-receivables-print-7);
        }

        .notes-panel,
        .totals-panel {
            background: var(--color-finance-receivables-print-6);
            border: 1px solid var(--color-finance-receivables-print-4);
            min-height: var(--dimension-finance-receivables-print-3);
            padding: 4mm 5mm;
        }

        .notes-title {
            color: var(--color-finance-receivables-print-1);
            font-size: var(--typo-finance-receivables-print-font-size-12);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            margin-bottom: var(--space-finance-receivables-print-4);
        }

        .notes-text {
            font-size: 3.4mm;
            line-height: 1.4;
            white-space: pre-line;
        }

        .notes-trust {
            margin-top: var(--space-finance-receivables-print-6);
            font-size: var(--typo-finance-receivables-print-font-size-11);
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            color: #b8b8b8;
        }

        .totals-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            font-size: var(--typo-finance-receivables-print-font-size-11);
            margin-bottom: var(--space-finance-receivables-print-2);
        }

        .totals-line:last-child {
            margin-bottom: 0;
        }

        .totals-line strong {
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
            white-space: nowrap;
        }

        .totals-line.balance {
            padding-top: var(--space-finance-receivables-print-4);
            border-top: 1px solid #c8c8c8;
        }

        .totals-line.balance strong {
            color: var(--color-finance-receivables-print-2);
        }

        .approved-block {
            position: absolute;
            right: 66px;
            bottom: 57mm    ;
            width: 33mm;
            color: #222;
            text-align: left;
            z-index: 2;
        }

        .approved-label {
            font-size: var(--typo-finance-receivables-print-font-size-14);
            font-weight: 400;
            margin-bottom: 35px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #2b2b2b;
            padding-top: var(--space-finance-receivables-print-6);
            text-align: right;
        }

        .signature-name {
            font-size: var(--typo-finance-receivables-print-font-size-2);
            line-height: var(--typo-finance-receivables-print-line-height-5);
            font-weight: var(--typo-finance-receivables-print-font-weight-3);
        }

        .signature-title {
            margin-top: 14px;
            font-size: var(--typo-finance-receivables-print-font-size-1);
        }

        .contact-footer {
            position: absolute;
            left: 17mm;
            bottom: 20px;
            width: 112mm;
            display: grid;
            gap: var(--space-finance-receivables-print-3);
            z-index: 3;
        }

        .contact-row {
            display: grid;
            grid-template-columns: 15mm minmax(0, 1fr);
            gap: 7mm;
            align-items: center;
            color: #151515;
        }

        .contact-icon {
            width: var(--dimension-finance-receivables-print-2);
            height: var(--dimension-finance-receivables-print-2);
            border: 1.2mm solid #d6d9db;
            border-left-color: #17dfe3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5mm;
            font-weight: var(--typo-finance-receivables-print-font-weight-6);
        }

        .contact-text {
            font-size: var(--typo-finance-receivables-print-font-size-14);
            line-height: var(--typo-finance-receivables-print-line-height-13);
            font-weight: var(--typo-finance-receivables-print-font-weight-7);
        }

        .contact-text small {
            display: block;
            font-size: var(--typo-finance-receivables-print-font-size-14);
            line-height: var(--typo-finance-receivables-print-line-height-13);
        }

        .footer-shapes {
            position: absolute;
            right: -46mm;
            bottom: -18mm;
            width: 155mm;
            height: 71mm;
            z-index: 1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            transform-origin: center;
        }

        .shape-gray-top {
            left: 43mm;
            top: 0;
            width: var(--dimension-finance-receivables-print-6);
            height: 34mm;
            background: #bfc5c8;
            transform: skewX(-45deg);
        }

        .shape-gray-bottom {
            left: 39mm;
            top: 34mm;
            width: var(--dimension-finance-receivables-print-6);
            height: var(--dimension-finance-receivables-print-5);
            background: #dfe4e6;
            transform: skewX(45deg);
        }

        .shape-blue {
            left: 68mm;
            top: 0;
            width: 62mm;
            height: var(--dimension-finance-receivables-print-7);
            background: #108ef2;
            clip-path: polygon(0 0, 72% 0, 100% 50%, 72% 100%, 0 100%, 28% 50%);
        }

        .shape-blue-dark {
            left: 122mm;
            top: 20mm;
            width: var(--dimension-finance-receivables-print-5);
            height: 28mm;
            background: #0a5dae;
            clip-path: polygon(0 0, 100% 50%, 0 100%, 34% 50%);
        }

        .shape-cyan {
            left: 139mm;
            top: 0;
            width: 60mm;
            height: var(--dimension-finance-receivables-print-7);
            background: #16dfe4;
            clip-path: polygon(0 50%, 28% 0, 100% 0, 72% 50%, 100% 100%, 28% 100%);
        }

        .screen-only {
            display: block;
        }

        @media print {
            body {
                background: var(--color-finance-receivables-print-5);
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
    <!-- <div class="toolbar screen-only">
        @if($charge->getKey())
            <a href="{{ route('finance.receivables.show', $charge) }}">Back To Invoice</a>
        @endif
        <button type="button" onclick="window.print()">Print</button>
    </div> -->

    <main class="sheet d-flex">
        <header class="brand-header">
            <img class="brand-logo" src="{{ asset('theme/img/Career-updated-logo.png') }}" alt="Career Institute">
            <!-- <div class="brand-services">
                <div>Trainings</div>
                <div>Certification Exam Center</div>
                <div>Study Abroad</div>
                <div>Coworking Space</div>
            </div> -->
            <section class="invoice-hero ml-5">
            <!-- <div class="invoice-to-title">Invoice To</div> -->
            <div class="invoice-title-band">Invoice</div>
        </section>
        </header>

        <!-- <div class="company-name">Career Institute Pvt Ltd</div> -->



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

        <section class="summary-grid" aria-label="Invoice summary">
            <div class="summary-item">
                <div class="summary-label">Total Due </div>
                <div class="summary-value">{{ number_format((float) $charge->net_amount, 0) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Invoice No.</div>
                <div class="summary-value">{{ $invoiceNumber }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Date </div>
                <div class="summary-value">{{ $invoiceDate }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Account </div>
                <div class="summary-value">{{ $accountLabel }}</div>
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
                            <!-- <span class="description-subtitle">
                                Charge Type: {{ $charge->chargeType->name ?? 'Invoice Item' }}
                                @if($charge->remarks)
{{ $charge->remarks }}
                                @endif
                            </span> -->
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

        <!-- <section class="tracking-panel">
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
        </section> -->

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

        <section class="approved-block">
            <div class="approved-label">Approved By:</div>
            <div class="signature-line">
                <div class="signature-name">Adeel Javaid</div>
                <div class="signature-title">Managing Director</div>
            </div>
        </section>

        <section class="contact-footer">
            <div class="contact-row">
                <div class="contact-icon">&#9742;</div>
                <div class="contact-text">
                    <div>0341-4444010</div>
                    <div>0314-4444010</div>
                </div>
            </div>
            <div class="contact-row">
                <div class="contact-icon">&#9678;</div>
                <div class="contact-text">
                    <div>Website</div>
                    <div>www.career.edu.pk</div>
                </div>
            </div>
            <div class="contact-row">
                <div class="contact-icon">&#9679;</div>
                <div class="contact-text">
                    <small>P-703, Sethi Plaza, Main Satiana Road,</small>
                    <small>Faisalabad, Pakistan - 38000</small>
                </div>
            </div>
        </section>

        <div class="footer-shapes" aria-hidden="true">
            <div class="shape shape-gray-top"></div>
            <div class="shape shape-gray-bottom"></div>
            <div class="shape shape-blue"></div>
            <div class="shape shape-blue-dark"></div>
            <div class="shape shape-cyan"></div>
        </div>
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
