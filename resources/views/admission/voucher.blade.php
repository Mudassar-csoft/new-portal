@php
    $studentName = $admission->student_name ?? '';
    $guardianName = $admission->guardian_name ?? '';
    $guardianLabel = strtolower($admission->gender ?? '') === 'female' ? 'D/O' : 'S/O';
    $courseTitle = $admission->program->title ?? $admission->program->name ?? '-';
    $batchCode = optional($admission->batch)->code ?? optional($admission->batch)->name ?? '-';
    $selectedFee = $selectedFee ?? null;
    $admissionDate = optional($admission->admission_date ?? $admission->created_at)->format('Y-m-d') ?? '-';
    $receiptNo = $selectedFee?->receipt_number ?? $admission->receipt_number ?? '-';
    $voucherDate = optional($selectedFee?->paid_at ?? $admission->admission_date ?? $admission->created_at)->format('d-m-Y') ?? '-';
    $registrationNo = $admission->registration_number ?? optional($admission->registration)->registration_number ?? '-';
    $rollNo = $admission->roll_number ?? '-';
    $campusCode = optional($admission->campus)->code ?? '-';
    $campusName = optional($admission->campus)->name ?? 'Branch';
    $showRegistrationFeeRow = false;
    $registrationFee = (float) ($registrationFeeTotal ?? 0);
    $courseTuitionFee = (float) ($admissionFeeTotal ?? 0);
    $examFee = 0;
    $fine = 0;
    $others = 0;

    $pendingFees = \App\Models\FeeCollection::query()
        ->where('admission_id', $admission->id)
        ->where('status', 'pending')
        ->orderBy('installment_no')
        ->orderBy('due_at')
        ->get();

    $nextPending = $pendingFees->first();
    $installmentNo = $selectedFee?->installment_no ? (string) $selectedFee->installment_no : '';
    $balanceDue = (float) $pendingFees->sum('net_amount');
    $nextDueDate = optional($nextPending?->due_at)->format('Y-m-d') ?? '';
    $totalPaid = (float) ($totalPaid ?? ($registrationFee + $courseTuitionFee));
@endphp

@include('shared.voucher_layout', get_defined_vars())
