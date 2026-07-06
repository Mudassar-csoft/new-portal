@php
    $member = $registration;
    $campus = $member->campus;
    $date = optional($member->registration_date)->format('d-m-Y');
    $nextDue = optional($member->next_due_date)->format('d-m-Y');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coworking Registration Slip</title>
    <style>
        :root {
            --space-coworking-registration-voucher-1: 24px;
        }

        body { font-family: Arial, sans-serif; margin: var(--space-coworking-registration-voucher-1); color: #1f2937; }
        .sheet { border: 1px solid #cbd5e1; border-radius: 12px; padding: var(--space-coworking-registration-voucher-1); max-width: 920px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .subtle { color: #64748b; font-size: 0.8125rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #dbe4ee; padding: 10px 12px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; width: 24%; }
        .fees td, .fees th { text-align: right; }
        .fees th:first-child, .fees td:first-child { text-align: left; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div>
                <h1>Coworking Space Registration Slip</h1>
                <div class="subtle">Career Institute</div>
            </div>
            <div class="subtle">
                <div><strong>Registration No:</strong> {{ $member->registration_number }}</div>
                <div><strong>Receipt No:</strong> {{ $member->receipt_number }}</div>
                <div><strong>Registration Date:</strong> {{ $date }}</div>
                <div><strong>Campus:</strong> {{ $campus?->code ?? 'N/A' }} - {{ $campus?->name ?? 'N/A' }}</div>
            </div>
        </div>

        <table>
            <tr>
                <th>Full Name</th>
                <td>{{ $member->full_name }}</td>
                <th>Primary Contact</th>
                <td>{{ $member->phone }}</td>
            </tr>
            <tr>
                <th>Guardian Name</th>
                <td>{{ $member->guardian_name }}</td>
                <th>Guardian Contact</th>
                <td>{{ $member->guardian_phone }}</td>
            </tr>
            <tr>
                <th>CNIC</th>
                <td>{{ $member->cnic }}</td>
                <th>Email Address</th>
                <td>{{ $member->email }}</td>
            </tr>
            <tr>
                <th>Education</th>
                <td>{{ $member->education }}</td>
                <th>Date of Birth</th>
                <td>{{ optional($member->date_of_birth)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Nature of Work</th>
                <td>{{ $member->nature_of_work }}</td>
                <th>Timing</th>
                <td>{{ $member->timing }}</td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>{{ ucfirst($member->gender) }}</td>
                <th>Next Due Date</th>
                <td>{{ $nextDue }}</td>
            </tr>
            <tr>
                <th>Postal Address</th>
                <td colspan="3">{{ $member->address }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td colspan="3">{{ $member->remarks ?: '—' }}</td>
            </tr>
        </table>

        <table class="fees">
            <tr>
                <th>Description</th>
                <th>Amount (PKR)</th>
            </tr>
            <tr>
                <td>Initial Coworking Charges</td>
                <td>{{ number_format((float) $member->coworking_charges, 0) }}</td>
            </tr>
            <tr>
                <td>Security Fee</td>
                <td>{{ number_format((float) $member->security_fee, 0) }}</td>
            </tr>
            <tr>
                <th>Total</th>
                <th>{{ number_format((float) $member->coworking_charges + (float) $member->security_fee, 0) }}</th>
            </tr>
        </table>
    </div>
</body>
</html>
