@php
    $studentName = $admission->student_name ?? '';
    $guardianName = $admission->guardian_name ?? '';
    $guardianLabel = strtolower($admission->gender ?? '') === 'female' ? 'D/O' : 'S/O';
    $courseTitle = $admission->program->title ?? $admission->program->name ?? '-';
    $batchCode = optional($admission->batch)->code ?? optional($admission->batch)->name ?? '-';
    $selectedFee = $selectedFee ?? null;
    $allAdmissionFees = ($admissionFeeRows ?? collect())->values();
    $receiptCampus = $selectedFee?->campus ?? $admission->campus;
    $admissionDate = optional($admission->admission_date ?? $admission->created_at)->format('Y-m-d') ?? '-';
    $receiptNo = $selectedFee?->receipt_number ?? $admission->receipt_number ?? '-';
    $voucherDate = optional($selectedFee?->paid_at ?? $admission->admission_date ?? $admission->created_at)->format('d-m-Y') ?? '-';
    $registrationNo = $admission->registration_number ?? optional($admission->registration)->registration_number ?? '-';
    $rollNo = $admission->roll_number ?? '-';
    $campusCode = optional($receiptCampus)->code ?? optional($admission->campus)->code ?? '-';
    $campusName = optional($receiptCampus)->name ?? optional($admission->campus)->name ?? 'Branch';
    $campusAddress = trim((string) (optional($receiptCampus)->address ?? ''));
    $campusPhone = collect([
        optional($receiptCampus)->landline,
        optional($receiptCampus)->mobile,
    ])->filter(fn ($value) => filled($value))->implode(' / ');
    $campusFooterLabel = $campusName;
    $campusWebsite = 'www.career.edu.pk';
    $showRegistrationFeeRow = false;
    $registrationFee = (float) ($registrationFeeTotal ?? 0);
    $courseTuitionFee = (float) ($admissionFeeTotal ?? 0);
    $originalFee = 0;
    $voucherDiscountPercent = 0;
    $voucherDiscountAmount = 0;
    $examFee = 0;
    $fine = 0;
    $others = 0;
    $installmentNo = $selectedFee?->installment_no ? (string) $selectedFee->installment_no : '';
    $futureInstallments = $installmentNo !== ''
        ? $allAdmissionFees
            ->filter(function ($fee) use ($selectedFee) {
                return (int) $fee->id !== (int) ($selectedFee?->id ?? 0)
                    && (int) ($fee->installment_no ?? 0) > (int) ($selectedFee?->installment_no ?? 0);
            })
            ->sortBy(fn ($fee) => sprintf('%06d-%010d', (int) ($fee->installment_no ?? 999999), (int) $fee->id))
            ->values()
        : collect();
    $nextScheduledInstallment = $futureInstallments->first();
    $balanceDue = $installmentNo !== ''
        ? (float) $futureInstallments->sum('net_amount')
        : (float) $allAdmissionFees->where('status', 'pending')->sum('net_amount');
    $nextDueDate = optional($nextScheduledInstallment?->due_at)->format('Y-m-d')
        ?? optional($allAdmissionFees->where('status', 'pending')->sortBy('installment_no')->first()?->due_at)->format('Y-m-d')
        ?? '';
    $statusPrimaryLabel = 'Paid Date';
    $statusPrimaryDisplay = optional($selectedFee?->paid_at)->format('Y-m-d')
        ?? optional($admission->admission_date ?? $admission->created_at)->format('Y-m-d')
        ?? '-';
    $statusSecondaryLabel = 'Collected By';
    $statusSecondaryDisplay = trim((string) ($selectedFee?->creator?->name ?? '')) ?: '-';
    $totalPaid = (float) ($totalPaid ?? ($registrationFee + $courseTuitionFee));
@endphp

@include('shared.voucher_layout', get_defined_vars())
