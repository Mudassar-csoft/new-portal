@php
    $studentName = $studentName ?? 'Student';
    $guardianName = $guardianName ?? '';
    $guardianLabel = $guardianLabel ?? 'S/O';
    $courseTitle = $courseTitle ?? '-';
    $batchCode = $batchCode ?? '-';
    $admissionDate = $admissionDate ?? '-';
    $receiptNo = $receiptNo ?? '-';
    $voucherDate = $voucherDate ?? '-';
    $registrationNo = $registrationNo ?? '-';
    $rollNo = $rollNo ?? '-';
    $campusCode = $campusCode ?? '-';
    $campusName = $campusName ?? 'Branch';
    $showRegistrationFeeRow = $showRegistrationFeeRow ?? true;
    $showCourseTuitionFeeRow = $showCourseTuitionFeeRow ?? true;
    $registrationFee = (float) ($registrationFee ?? 0);
    $courseTuitionFee = (float) ($courseTuitionFee ?? 0);
    $examFee = (float) ($examFee ?? 0);
    $fine = (float) ($fine ?? 0);
    $others = (float) ($others ?? 0);
    $installmentNo = $installmentNo ?? '';
    $balanceDue = (float) ($balanceDue ?? 0);
    $nextDueDate = $nextDueDate ?? '';
    $totalPaid = (float) ($totalPaid ?? ($registrationFee + $courseTuitionFee + $examFee + $fine + $others));
    $currency = $currency ?? 'Rs.';
    $copies = $copies ?? ['Student Copy', 'Campus Copy'];
    $formattedStudentName = trim($studentName . ($guardianName !== '' ? ' ' . $guardianLabel . ' ' . $guardianName : ''));

    $formatAmount = static function ($amount) use ($currency) {
        if ((float) $amount <= 0) {
            return '-';
        }

        $normalizedAmount = round((float) $amount, 2);
        $decimals = abs($normalizedAmount - floor($normalizedAmount)) > 0 ? 2 : 0;

        return $currency . ' ' . number_format($normalizedAmount, $decimals, '.', ',');
    };

    $amountInWords = \App\Support\AmountToWords::forRupees($totalPaid);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career | Fee Voucher</title>
    <style>
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
            background: #fff;
            color: #111;
            font-size: 14px;
            line-height: 1.2;
            padding: 10px;
        }

        .voucher-sheet {
            max-width: 860px;
            margin: 0 auto;
        }

        .voucher-copy {
            border: 1px solid #444;
            padding: 18px 18px 14px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .copy-label {
            text-align: center;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            /* align-items: flex-start; */
            gap: 18px;
            margin-bottom: 10px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand-logo {
            width: 320px;
            height: 80px;
            flex: 0 0 64px;
        }

        .brand-lines div {
            font-size: 14px;
            line-height: 1.25;
        }

        .voucher-meta {
            width: 250px;
        }

        .voucher-title {
            font-size: 15px;
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
        .amount-table td,
        .charges-table td,
        .status-table td {
            border: 1px solid #777;
            padding: 3px 5px;
            vertical-align: top;
        }

        .info-table th {
            font-weight: 400;
            text-align: left;
            background: #fff;
        }

        .tables-row {
            display: flex;
            gap: 44px;
            margin-top: 12px;
            align-items: flex-start;
        }

        .charges-wrap {
            width: 48%;
        }

        .status-wrap {
            width: 44%;
        }

        .charges-table td:first-child,
        .status-table td:first-child {
            width: 63%;
        }

        .charges-table td:last-child,
        .status-table td:last-child {
            width: 37%;
            text-align: left;
        }

        .amount-table {
            margin-top: 10px;
        }

        .amount-table td {
            font-size: 15px;
            padding: 5px 6px;
        }

        .voucher-notes {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 8px;
            font-size: 11px;
            color: #8a8a8a;
        }

        .voucher-footer {
            margin-top: 18px;
            font-size: 12px;
            line-height: 1.25;
        }

        .voucher-footer strong {
            font-weight: 400;
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
        @foreach ($copies as $copyLabel)
            <section class="voucher-copy">
                <div class="copy-label">{{ $copyLabel }}</div>

                <div class="voucher-header">
                    <div class="brand-block">
                        <img src="{{ asset('theme/img/career-updated-logo.png') }}" alt="Career Institute Logo" class="brand-logo">
                        <!-- <div class="brand-lines">
                            <div>Trainings</div>
                            <div>Certification Exam Center</div>
                            <div>Coworking Space</div>
                        </div> -->
                    </div>

                    <div class="voucher-meta">
                        <div class="voucher-title">FEE VOUCHER</div>
                        <div class="voucher-meta-line">Receipt No: {{ $receiptNo }}</div>
                        <div class="voucher-meta-line">Date: {{ $voucherDate }}</div>
                        <div class="voucher-meta-line">Registration No: {{ $registrationNo }}</div>
                        <div class="voucher-meta-line">Roll No: {{ $rollNo }}</div>
                        <div class="voucher-meta-line">Campus Code: {{ $campusCode }}</div>
                    </div>
                </div>

                <table class="info-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Course Title</th>
                            <th>Batch</th>
                            <th>Date of Admission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $formattedStudentName }}</td>
                            <td>{{ $courseTitle }}</td>
                            <td>{{ $batchCode }}</td>
                            <td>{{ $admissionDate }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="tables-row">
                    <div class="charges-wrap">
                        <table class="charges-table">
                            <tbody>
                                @if($showRegistrationFeeRow)
                                    <tr>
                                        <td>Registration Fee</td>
                                        <td>{{ $formatAmount($registrationFee) }}</td>
                                    </tr>
                                @endif
                                @if($showCourseTuitionFeeRow)
                                    <tr>
                                        <td>Course Tuition Fee</td>
                                        <td>{{ $formatAmount($courseTuitionFee) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Exam Fee</td>
                                    <td>{{ $formatAmount($examFee) }}</td>
                                </tr>
                                <tr>
                                    <td>Fine</td>
                                    <td>{{ $formatAmount($fine) }}</td>
                                </tr>
                                <tr>
                                    <td>Others</td>
                                    <td>{{ $formatAmount($others) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Paid</td>
                                    <td>{{ $formatAmount($totalPaid) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="status-wrap">
                        <table class="status-table">
                            <tbody>
                                <tr>
                                    <td>Installment No.</td>
                                    <td>{{ $installmentNo !== '' ? $installmentNo : '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Balance Due</td>
                                    <td>{{ $balanceDue > 0 ? $formatAmount($balanceDue) : $currency . ' 0' }}</td>
                                </tr>
                                <tr>
                                    <td>Next Due Date</td>
                                    <td>{{ $nextDueDate !== '' ? $nextDueDate : '-' }}</td>
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
                                <span class="amountInWords">{{ $amountInWords }}</span>
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
                    <div>For Career Institute - {{ $campusName }}</div>
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
