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
            background: #fff;
            position: relative;
            overflow: hidden;
            padding: 12mm 17mm 68mm;
        }

        .brand-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 100%;
        }

        .brand-logo {
            width: 100px;
            height: 100px;
            display: block;
        }

        .brand-services {
            padding-top: 5px;
            color: #0a0a0a;
            font-size: 20px;
            /* line-height: 1.25; */
            font-weight: 600;
        }

        .company-name {
            margin-top: 6mm;
            color: #20262d;
            font-size: 7.4mm;
            line-height: 1.1;
            font-weight: 700;
        }

        .invoice-hero {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            margin-left: 30px;
        }

        .invoice-to-title {
            font-size: 7.4mm;
            font-weight: 500;
            line-height: 1;
            

        }

        .invoice-title-band {
            background: #ececec;
            width: 100%;
            min-height: 18mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0c0c0c;
            font-size: 20px;
            line-height: 1;
            font-weight: 800;
            text-transform: uppercase;
            padding:20px;
        }

        .bill-to {
            margin-top: 9mm;
            width: 72mm;
            min-height: 26mm;
        }

        .bill-to-name {
            font-size: 6.2mm;
            line-height: 1.08;
            font-weight: 700;
            margin-bottom: 2.4mm;
        }

        .bill-to-line {
            font-size: 3.7mm;
            line-height: 1.35;
            margin-bottom: 1mm;
            word-break: break-word;
        }

        .summary-grid {
            margin-top: 4mm;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #d8e2e7;
        }

        .summary-label {
            background: #00a8ff;
            color: #20262d;
            border: 1px solid #d8e2e7;
            padding: 3mm 3.2mm;
            font-size: 3.7mm;
            font-weight: 700;
            text-align: left;
            vertical-align: middle;
        }

        .summary-value {
            border: 1px solid #d8e2e7;
            padding: 3mm 3.2mm;
            font-size: 3.7mm;
            font-weight: 700;
            line-height: 1.1;
            word-break: break-word;
            vertical-align: top;
        }

        .summary-meta {
            margin-top: 1.2mm;
            font-size: 3.2mm;
            color: #5a5a5a;
            font-weight: 400;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
            margin-top: 5mm;
            border: 1px solid #d8e2e7;
            background: rgba(255, 255, 255, 0.96);
        }

        .items-table th {
            background: #00a8ff;
            color: #fff;
            border: 1px solid #0f8ef2;
            padding: 3.2mm 3mm;
            font-size: 3.7mm;
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
            background: #fff;
            border: 1px solid #d8e2e7;
            padding: 3.2mm 3mm;
            font-size: 3.5mm;
            vertical-align: top;
        }

        .items-table tbody tr + tr td {
            border-top: 1px solid #d8e2e7;
        }

        .description-title {
            display: block;
            font-size: 3.8mm;
            font-weight: 700;
            margin-bottom: 1.4mm;
        }

        .description-subtitle {
            display: block;
            color: #4b4b4b;
            line-height: 1.35;
            /* white-space: pre-line; */
            word-break: break-word;
        }

        .empty-row td {
            color: transparent;
            height: 18mm;
        }

        .tracking-panel {
            margin-top: 5mm;
            min-height: 11mm;
            background: rgba(238, 242, 247, 0.96);
            border: 1px solid #d8e2e7;
            padding: 3.2mm 4mm;
            display: grid;
            grid-template-columns: 1.1fr repeat(4, 1fr);
            gap: 3mm;
            align-items: start;
        }

        .tracking-title {
            font-size: 3.7mm;
            font-weight: 700;
            line-height: 1.2;
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
            font-size: 3.5mm;
            font-weight: 700;
            line-height: 1.15;
        }

        .bottom-grid {
            margin-top: 5mm;
            display: grid;
            grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr);
            gap: 5mm;
        }

        .notes-panel,
        .totals-panel {
            background: rgba(238, 242, 247, 0.96);
            border: 1px solid #d8e2e7;
            min-height: 18mm;
            padding: 4mm 5mm;
        }

        .notes-title {
            color: #0f8ef2;
            font-size: 3.8mm;
            font-weight: 700;
            margin-bottom: 2mm;
        }

        .notes-text {
            font-size: 3.4mm;
            line-height: 1.4;
            white-space: pre-line;
        }

        .notes-trust {
            margin-top: 3mm;
            font-size: 3.5mm;
            font-weight: 700;
            color: #b8b8b8;
        }

        .totals-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            font-size: 3.5mm;
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

        .approved-block {
            position: absolute;
            right: 66px;
            bottom: 57mm    ;
            width: 54mm;
            color: #222;
            text-align: left;
            z-index: 2;
        }

        .approved-label {
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 35px;
            text-align: end;
        }

        .signature-line {
            border-top: 1px solid #2b2b2b;
            padding-top: 3mm;
            text-align: right;
        }

        .signature-name {
            font-size: 16px;
            line-height: 1.1;
            font-weight: 600;
        }

        .signature-title {
            margin-top: 14px;
            font-size: 4mm;
        }

        .contact-footer {
            position: absolute;
            left: 17mm;
            bottom: 20px;
            width: 112mm;
            display: grid;
            gap: 20px;
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
            width: 12mm;
            height: 12mm;
            border: 1.2mm solid #d6d9db;
            border-left-color: #17dfe3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5mm;
            font-weight: 700;
        }

        .contact-text {
            font-size: 16px;
            line-height: 1.2;
            font-weight: 500;
        }

        .contact-text small {
            display: block;
            font-size: 16px;
            line-height: 1.2;
        }

        .footer-shapes {
            position: absolute;
            right: -46mm;
            bottom: -18mm;
            width: 155mm;
            height: 74mm;
            z-index: 1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            transform-origin: center;
        }

        .shape-gray-top {
            left: 12mm;
            top: 0;
            width: 66mm;
            height: 34mm;
            background: #bfc5c8;
            transform: skewX(-45deg);
        }

        .shape-gray-bottom {
            left: 12mm;
            top: 34mm;
            width: 66mm;
            height: 34mm;
            background: #dfe4e6;
            transform: skewX(45deg);
        }

        .shape-blue {
            left: 68mm;
            top: 0;
            width: 62mm;
            height: 68mm;
            background: #108ef2;
            clip-path: polygon(0 0, 72% 0, 100% 50%, 72% 100%, 0 100%, 28% 50%);
        }

        .shape-blue-dark {
            left: 122mm;
            top: 20mm;
            width: 26mm;
            height: 28mm;
            background: #0a5dae;
            clip-path: polygon(0 0, 100% 50%, 0 100%, 34% 50%);
        }

        .shape-cyan {
            left: 139mm;
            top: 0;
            width: 60mm;
            height: 68mm;
            background: #16dfe4;
            clip-path: polygon(0 50%, 28% 0, 100% 0, 72% 50%, 100% 100%, 28% 100%);
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
    <!-- <div class="toolbar screen-only">
        @if($charge->getKey())
            <a href="{{ route('finance.receivables.show', $charge) }}">Back To Invoice</a>
        @endif
        <button type="button" onclick="window.print()">Print</button>
    </div> -->

    <main class="sheet d-flex">
        <header class="brand-header">
            <img class="brand-logo" src="{{ asset('theme/img/Career-Institute-logo.webp') }}" alt="Career Institute">
            <div class="brand-services">
                <div>Trainings</div>
                <div>Certification Exam Center</div>
                <div>Study Abroad</div>
                <div>Coworking Space</div>
            </div>
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

        <table class="summary-grid border-1-gray">
            <thead>
                <tr>
                    <th class="summary-label">Invoice No.</th>
                    <th class="summary-label">Account</th>
                    <th class="summary-label">Date</th>
                    <th class="summary-label">Total Due</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="summary-value">
                        {{ $invoiceNumber }}
                        <div class="summary-meta">Campus: {{ $charge->campus->code ?? 'N/A' }}</div>
                    </td>
                    <td class="summary-value">
                        {{ $accountLabel }}
                        <div class="summary-meta">Due: {{ $dueDate }} <br> Status: {{ $statusLabel }}</div>
                    </td>
                    <td class="summary-value">{{ $invoiceDate }}</td>
                    <td class="summary-value">{{ number_format((float) $charge->net_amount, 0) }}</td>
                </tr>
            </tbody>
        </table>

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
