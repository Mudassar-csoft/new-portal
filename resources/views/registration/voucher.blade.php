@php
    $reg = $registration;
    $studentName = $reg->student_name ?? ($reg->lead->name ?? 'Student');
    $guardianName = $reg->guardian_name ?? '';
    $guardianLabel = strtolower($reg->gender ?? '') === 'female' ? 'D/O' : 'S/O';
    $courseTitle = $reg->program->title ?? $reg->program->name ?? '-';
    $batchCode = optional($reg->admission?->batch)->code ?? optional($reg->admission?->batch)->name ?? '-';
    $admissionDate = optional($reg->registered_at ?? $reg->created_at)->format('Y-m-d') ?? '-';
    $receiptNo = $reg->receipt_number ?? '-';
    $voucherDate = optional($reg->registered_at ?? $reg->created_at)->format('d-m-Y') ?? '-';
    $registrationNo = $reg->registration_number ?? '-';
    $rollNo = optional($reg->admission)->roll_number ?? '-';
    $campusCode = optional($reg->campus)->code ?? '-';
    $campusName = optional($reg->campus)->name ?? 'Branch';
    $campusAddress = trim((string) (optional($reg->campus)->address ?? ''));
    $campusPhone = collect([
        optional($reg->campus)->landline,
        optional($reg->campus)->mobile,
    ])->filter(fn ($value) => filled($value))->implode(' / ');
    $campusFooterLabel = $campusName;
    $showCourseTuitionFeeRow = false;
    $registrationFee = (float) ($reg->fee ?? 0);
    $courseTuitionFee = 0;
    $examFee = 0;
    $fine = 0;
    $others = 0;
    $installmentNo = '';
    $balanceDue = 0;
    $nextDueDate = '';
    $totalPaid = (float) ($reg->net_payable ?? $registrationFee);
@endphp

@include('shared.voucher_layout', get_defined_vars())
