@php
    $registration = $receipt->coworkingRegistration;
    $campus = $receipt->campus ?? $registration?->campus;
    $receiptTitle = match ($receipt->receipt_type) {
        'security_fee' => 'Security Fee Slip',
        'security_refund' => 'Security Refund Receipt',
        default => 'Coworking Charges Receipt',
    };
    $paidDate = optional($receipt->paid_at ?? $registration?->registration_date)->format('d-m-Y');
    $receiptTypeLabel = match ($receipt->receipt_type) {
        'security_fee' => 'Security Fee',
        'security_refund' => 'Security Refund',
        default => 'Coworking Charges',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $receiptTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        .sheet { border: 1px solid #cbd5e1; border-radius: 12px; padding: 24px; max-width: 860px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .header h1 { margin: 0; font-size: 24px; }
        .subtle { color: #64748b; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #dbe4ee; padding: 10px 12px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; width: 30%; }
        .amount { font-size: 28px; font-weight: 700; color: #0f766e; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div>
                <h1>{{ $receiptTitle }}</h1>
                <div class="subtle">Career Institute</div>
            </div>
            <div class="subtle">
                <div><strong>Receipt No:</strong> {{ $receipt->receipt_number }}</div>
                <div><strong>Date:</strong> {{ $paidDate }}</div>
                <div><strong>Campus:</strong> {{ $campus?->code ?? 'N/A' }} - {{ $campus?->name ?? 'N/A' }}</div>
            </div>
        </div>

        <table>
            <tr>
                <th>Member Name</th>
                <td>{{ $registration?->full_name ?? $receipt->lead?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Registration No</th>
                <td>{{ $registration?->registration_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Receipt Type</th>
                <td>{{ $receiptTypeLabel }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{!! nl2br(e($receipt->notes ?: 'N/A')) !!}</td>
            </tr>
        </table>

        <div class="amount">PKR {{ number_format((float) $receipt->amount, 0) }}</div>
    </div>
</body>
</html>
