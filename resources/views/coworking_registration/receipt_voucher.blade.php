@php
    $registration = $receipt->coworkingRegistration;
    $campus = $receipt->campus ?? $registration?->campus;
    $receiptTitle = match ($receipt->receipt_type) {
        'security_fee' => 'Security Fee Slip',
        'security_refund' => 'Security Refund Receipt',
        default => 'Coworking Charges Receipt',
    };
    $receiptTypeLabel = match ($receipt->receipt_type) {
        'security_fee' => 'Security Fee',
        'security_refund' => 'Security Refund',
        default => 'Coworking Charges',
    };
    $receiptDate = optional($receipt->paid_at ?? $registration?->registration_date ?? $receipt->created_at)->format('d-m-Y') ?? '-';
    $registrationDate = optional($registration?->registration_date)->format('d-m-Y') ?? '-';
    $memberName = $registration?->full_name ?? $receipt->lead?->name ?? 'Coworking Member';
    $campusLabel = trim(collect([$campus?->code, $campus?->name])->filter()->implode(' - ')) ?: 'N/A';
    $remarks = trim((string) ($receipt->notes ?: $registration?->remarks ?: 'N/A'));
    $timing = $registration?->timing ?: 'N/A';
    $totalPaid = (float) ($receipt->amount ?? 0);
    $amountInWords = \App\Support\AmountToWords::forRupees($totalPaid);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $receiptTitle }}</title>
    <style>
        :root {
            --dimension-coworking-registration-receipt-voucher-1: 42%;
            --space-coworking-registration-receipt-voucher-1: 10px;
            --space-coworking-registration-receipt-voucher-2: 18px;
            --space-coworking-registration-receipt-voucher-3: 8px;
            --color-coworking-registration-receipt-voucher-1: #fff;
            --typo-coworking-registration-receipt-voucher-font-size-1: 14px;
            --typo-coworking-registration-receipt-voucher-font-size-2: 15px;
            --typo-coworking-registration-receipt-voucher-font-weight-3: 400;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            background: var(--color-coworking-registration-receipt-voucher-1);
            color: #111;
            font-size: var(--typo-coworking-registration-receipt-voucher-font-size-1);
            line-height: 1.2;
            padding: var(--space-coworking-registration-receipt-voucher-1);
        }

        .voucher-sheet {
            max-width: 860px;
            margin: 0 auto;
        }

        .voucher-copy {
            border: 1px solid #444;
            padding: 18px 18px 14px;
            margin-bottom: var(--space-coworking-registration-receipt-voucher-2);
            page-break-inside: avoid;
        }

        .copy-label {
            text-align: center;
            font-size: var(--typo-coworking-registration-receipt-voucher-font-size-1);
            margin-bottom: var(--space-coworking-registration-receipt-voucher-3);
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            gap: var(--space-coworking-registration-receipt-voucher-2);
            margin-bottom: var(--space-coworking-registration-receipt-voucher-1);
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: var(--space-coworking-registration-receipt-voucher-1);
            min-width: 0;
        }

        .brand-logo {
            width: 320px;
            height: 80px;
            flex: 0 0 64px;
        }

        .voucher-meta {
            width: 280px;
        }

        .voucher-title {
            font-size: var(--typo-coworking-registration-receipt-voucher-font-size-2);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .voucher-meta-line {
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td,
        .summary-table td,
        .status-table td,
        .amount-table td {
            border: 1px solid #777;
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-table th {
            font-weight: var(--typo-coworking-registration-receipt-voucher-font-weight-3);
            text-align: left;
            background: var(--color-coworking-registration-receipt-voucher-1);
        }

        .tables-row {
            display: flex;
            gap: 36px;
            margin-top: 12px;
            align-items: flex-start;
        }

        .summary-wrap {
            width: 52%;
        }

        .status-wrap {
            width: var(--dimension-coworking-registration-receipt-voucher-1);
        }

        .summary-table td:first-child,
        .status-table td:first-child {
            width: 58%;
        }

        .summary-table td:last-child,
        .status-table td:last-child {
            width: var(--dimension-coworking-registration-receipt-voucher-1);
        }

        .amount-table {
            margin-top: var(--space-coworking-registration-receipt-voucher-1);
        }

        .amount-table td {
            font-size: var(--typo-coworking-registration-receipt-voucher-font-size-2);
            padding: 5px 6px;
        }

        .voucher-notes {
            display: flex;
            justify-content: space-between;
            gap: var(--space-coworking-registration-receipt-voucher-1);
            margin-top: var(--space-coworking-registration-receipt-voucher-3);
            font-size: 11px;
            color: #8a8a8a;
        }

        .voucher-footer {
            margin-top: var(--space-coworking-registration-receipt-voucher-2);
            font-size: 12px;
            line-height: 1.25;
        }

        .voucher-footer strong {
            font-weight: var(--typo-coworking-registration-receipt-voucher-font-weight-3);
        }

        @media print {
            body {
                padding: 0;
            }

            .voucher-sheet {
                max-width: none;
            }

            .voucher-copy {
                margin-bottom: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="voucher-sheet">
        @foreach (['Member Copy', 'Campus Copy'] as $copyLabel)
            <section class="voucher-copy">
                <div class="copy-label">{{ $copyLabel }}</div>

                <div class="voucher-header">
                    <div class="brand-block">
                        <img src="{{ asset('theme/img/career-updated-logo.png') }}" alt="Career Institute Logo" class="brand-logo">
                    </div>

                    <div class="voucher-meta">
                        <div class="voucher-title">{{ strtoupper($receiptTitle) }}</div>
                        <div class="voucher-meta-line">Receipt No: {{ $receipt->receipt_number ?? '-' }}</div>
                        <div class="voucher-meta-line">Date: {{ $receiptDate }}</div>
                        <div class="voucher-meta-line">Registration No: {{ $registration?->registration_number ?? '-' }}</div>
                        <div class="voucher-meta-line">Campus: {{ $campusLabel }}</div>
                    </div>
                </div>

                <table class="info-table">
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Receipt Type</th>
                            <th>Timing</th>
                            <th>Date of Joining</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $memberName }}</td>
                            <td>{{ $receiptTypeLabel }}</td>
                            <td>{{ $timing }}</td>
                            <td>{{ $registrationDate }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="tables-row">
                    <div class="summary-wrap">
                        <table class="summary-table">
                            <tbody>
                                <tr>
                                    <td>Registration No</td>
                                    <td>{{ $registration?->registration_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Remarks</td>
                                    <td>{{ $remarks }}</td>
                                </tr>
                                <tr>
                                    <td>Total Paid</td>
                                    <td>Rs. {{ number_format($totalPaid, abs($totalPaid - floor($totalPaid)) > 0 ? 2 : 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="status-wrap">
                        <table class="status-table">
                            <tbody>
                                <tr>
                                    <td>Receipt Status</td>
                                    <td>{{ $receipt->paid_at ? 'Paid' : 'Pending' }}</td>
                                </tr>
                                <tr>
                                    <td>Receipt Type</td>
                                    <td>{{ $receiptTypeLabel }}</td>
                                </tr>
                                <tr>
                                    <td>Collected At</td>
                                    <td>{{ optional($receipt->paid_at)->format('Y-m-d') ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <table class="amount-table">
                    <tbody>
                        <tr>
                            <td>
                                Amount in Words:
                                <span>{{ $amountInWords }}</span>
                                Only.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="voucher-notes">
                    <div>
                        This receipt can be produce when demanded<br>
                        Fee once paid is not Refundable.
                    </div>
                    <div>For Career Institute - {{ $campus?->name ?? 'Campus' }}</div>
                </div>

                <div class="voucher-footer">
                    <strong>
                        Career Institute, P-49, Chenab Market, Susan Road, Block Z, Madina Town, Faisalabad, Punjab, Pakistan - 38000<br>
                        Ph:0314-4444010 / 0341-4444010<br>
                        www.career.edu.pk
                    </strong>
                </div>
            </section>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.print();
        });
    </script>
</body>
</html>
