@php
    $studentName = 'Fiza Muzammil';
    $guardianName = 'Muzammil Nadeem';
    $guardianLabel = 'D/O';
    $courseTitle = 'IELTS Academic';
    $batchCode = 'IEL01-22';
    $admissionDate = '2022-10-13';
    $receiptNo = 'CIFSD01-22-000001';
    $voucherDate = '31-10-2022';
    $registrationNo = 'CIFSD01-22-01';
    $rollNo = 'CIFSD01-IEL01-22-01';
    $campusCode = 'CIFSD01';
    $campusName = 'Madina Town Campus';
    $registrationFee = 1000;
    $courseTuitionFee = 24000;
    $examFee = 0;
    $fine = 0;
    $others = 0;
    $installmentNo = '';
    $balanceDue = 0;
    $nextDueDate = '';
    $totalPaid = 25000;
@endphp

@include('shared.voucher_layout', get_defined_vars())
