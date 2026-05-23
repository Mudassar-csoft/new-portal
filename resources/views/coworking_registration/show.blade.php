@extends('layouts.theme')

@section('title', 'Coworking Member - ' . ($registration->full_name ?? 'Detail'))

@section('content')
    @php
        $member = $registration;
        $lead = $member->lead;
        $campus = $member->campus ?? $lead?->campus;
        $leadDetails = $lead->details ?? [];
        $canMarkInactive = ($member->status ?? 'registered') === 'registered';
        $inactiveAction = old('inactive_action', 'leave');
        $inactiveModalTitle = match ($inactiveAction) {
            'drop' => 'Drop Coworking Member',
            'close_agreement' => 'Close Agreement',
            default => 'Leave Coworking Member',
        };
        $inactiveReasonLabel = match ($inactiveAction) {
            'drop' => 'Reason for Drop',
            'close_agreement' => 'Agreement Closing Reason',
            default => 'Reason for Leaving',
        };
        $showInactiveModal = $errors->has('leave_date')
            || $errors->has('damage_deduction_amount')
            || $errors->has('damage_notes')
            || $errors->has('inactive_reason')
            || $errors->has('inactive_remarks');
        $receipts = ($member->receipts ?? collect())
            ->sortBy(fn ($receipt) => sprintf(
                '%s-%010d',
                optional($receipt->paid_at)->format('YmdHis') ?: '99999999999999',
                $receipt->id ?? 0
            ))
            ->values();
        $securityReceipt = $receipts->firstWhere('receipt_type', 'security_fee');
        $chargeReceipt = $receipts->firstWhere('receipt_type', 'coworking_charge');
        $securityRefundReceipt = $receipts->where('receipt_type', 'security_refund')->last();
        $branchCode = $campus?->code ?: (data_get($leadDetails, 'preferred_location') ?: 'N/A');
        $branchName = $campus?->name ?: $campus?->title;
        $spaceType = data_get($leadDetails, 'space_required') ?: 'N/A';
        $businessName = data_get($leadDetails, 'business_name') ?: ($member->nature_of_work ?: 'N/A');
        $membersCount = max(1, (int) data_get($leadDetails, 'person_count', 1));
        $chargeReceipts = $receipts->reject(fn ($receipt) => $receipt->receipt_type === 'security_refund');
        $totalFee = (float) $chargeReceipts->sum('amount');
        $pendingFee = (float) $chargeReceipts->filter(fn ($receipt) => blank($receipt->paid_at))->sum('amount');
        $expectedStartingAt = data_get($leadDetails, 'expected_starting_at');
        $expectedStartingDate = filled($expectedStartingAt) ? \Illuminate\Support\Carbon::parse($expectedStartingAt) : null;
        $cycleStartDate = optional($member->registration_date)->copy();
        $cycleEndDate = optional($member->next_due_date)->copy();
        $cycleDays = $cycleStartDate && $cycleEndDate
            ? max(1, $cycleStartDate->diffInDays($cycleEndDate))
            : 30;
        $dailyDeductionAmount = round(((float) $member->coworking_charges) / $cycleDays, 2);
        $defaultLeaveDate = old('leave_date', optional($member->leave_date)->toDateString() ?: now()->toDateString());
        $defaultDamageDeduction = old('damage_deduction_amount', number_format((float) ($member->damage_deduction_amount ?? 0), 2, '.', ''));
        $memberStatusLabel = match ($member->status) {
            'inactive' => 'Inactive',
            default => ucfirst($member->status ?? 'Registered'),
        };
        $pendingCoworkingCharge = $chargeReceipts->first(fn ($receipt) => $receipt->receipt_type === 'coworking_charge' && blank($receipt->paid_at));
        $daysUntilNextDue = $member->next_due_date
            ? now()->startOfDay()->diffInDays($member->next_due_date->copy()->startOfDay(), false)
            : null;
        $showUpcomingCoworkingCharge = !$pendingCoworkingCharge
            && ($member->status ?? 'registered') === 'registered'
            && $member->next_due_date
            && filled($member->coworking_charges);
        $canOpenChargeModal = ($member->status ?? 'registered') === 'registered'
            && $member->next_due_date
            && filled($member->coworking_charges);
        $showCollectChargeAction = !$pendingCoworkingCharge
            && ($member->status ?? 'registered') === 'registered'
            && $member->next_due_date
            && $daysUntilNextDue !== null
            && $daysUntilNextDue >= 0
            && $daysUntilNextDue <= 2;
        $accountHistory = $receipts->map(function ($receipt) {
            $feeType = match ($receipt->receipt_type) {
                'security_fee' => 'Security Fee',
                'security_refund' => 'Security Refund',
                'coworking_charge' => 'Coworking Charges',
                default => \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $receipt->receipt_type)),
            };

            return [
                'fee_type' => $feeType,
                'amount' => (float) $receipt->amount,
                'status' => $receipt->receipt_type === 'security_refund'
                    ? 'refunded'
                    : ($receipt->paid_at ? 'paid' : 'pending'),
                'due_date' => $receipt->paid_at ?? $receipt->created_at,
                'collected_at' => $receipt->paid_at,
                'voucher_url' => route('coworking-registrations.receipts.voucher', $receipt),
                'receipt_number' => $receipt->receipt_number,
            ];
        });

        if ($showUpcomingCoworkingCharge) {
            $upcomingMonth = optional($member->next_due_date)->format('F');

            $accountHistory->push([
                'fee_type' => sprintf('Coworking Charges (%s)', $upcomingMonth),
                'amount' => (float) $member->coworking_charges,
                'status' => 'pending',
                'due_date' => $member->next_due_date,
                'collected_at' => null,
                'voucher_url' => null,
                'receipt_number' => null,
            ]);
        }

        $showChargeModal = $errors->has('charge_date') || $errors->has('charge_amount');
        $defaultChargeDate = old('charge_date', optional($member->next_due_date)->toDateString() ?: now()->toDateString());
        $defaultChargeAmount = old('charge_amount', number_format((float) $member->coworking_charges, 2, '.', ''));
    @endphp

    <div class="student-detail-shell coworking-detail-shell">
        @if(session('status'))
            <div class="student-flash">{{ session('status') }}</div>
        @endif

        <div class="box-typical box-typical-dashboard panel panel-default student-detail-header">
            <div class="panel-body">
                <h3 class="panel-title mb-0">Coworking Space Member Detail
                    <small class="text-muted" style="font-size:14px;font-weight:400;">
                        - {{ $member->full_name ?? '' }}
                    </small>
                </h3>
            </div>
        </div>

        <div class="student-detail-grid">
            <aside class="student-profile-card box-typical box-typical-dashboard panel panel-default">
                <div class="profile-banner profile-banner--coworking"></div>
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar">
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="50" cy="50" r="48" fill="#2f8fdc"/>
                            <circle cx="50" cy="38" r="16" fill="#fff8e1"/>
                            <path d="M22 86c0-15 12-26 28-26s28 11 28 26z" fill="#fff8e1"/>
                        </svg>
                    </div>
                </div>

                <div class="profile-body">
                    <h3 class="profile-name">{{ $member->full_name }}</h3>
                    <div class="profile-phone">{{ $member->phone ?? 'N/A' }}</div>
                    <div class="profile-campus">
                        {{ $branchCode }}@if($branchName) - {{ $branchName }}@endif
                    </div>

                    <div class="profile-action">
                        <div class="dropdown student-action-wrap">
                            <button class="btn btn-primary-outline dropdown-toggle student-action-btn" type="button" id="coworking-action-{{ $member->id }}" aria-haspopup="true" aria-expanded="false">
                                Actions <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu student-action-menu" aria-labelledby="coworking-action-{{ $member->id }}">
                                @if($canOpenChargeModal)
                                    <button type="button"
                                            class="dropdown-item js-open-charge-modal"
                                            data-charge-modal-open>
                                        <i class="bi bi-clipboard-check"></i>Collect Payment
                                    </button>
                                @endif
                                @if($canMarkInactive)
                                <button type="button"
                                            class="dropdown-item js-open-inactive-modal"
                                            data-inactive-modal-open
                                            data-inactive-action="close_agreement"
                                            data-inactive-title="Close Agreement"
                                            data-inactive-reason-label="Agreement Closing Reason">
                                        <i class="fa fa-file-text-o action-icon "></i>Close Agreement
                                    </button>
                                    <button type="button"
                                            class="dropdown-item js-open-inactive-modal"
                                            data-inactive-modal-open
                                            data-inactive-action="leave"
                                            data-inactive-title="Leave Coworking Member"
                                            data-inactive-reason-label="Reason for Leaving">
                                        <i class="fa fa-pause action-icon action-icon--danger"></i>Leave
                                    </button>
                                    <button type="button"
                                            class="dropdown-item js-open-inactive-modal"
                                            data-inactive-modal-open
                                            data-inactive-action="drop"
                                            data-inactive-title="Drop Coworking Member"
                                            data-inactive-reason-label="Reason for Drop">
                                        <i class="fa fa-stop action-icon action-icon--danger"></i>Drop
                                    </button>
                                    
                                @endif
                                <a class="dropdown-item" href="{{ route('coworking-registrations.edit', $member) }}">
                                    <i class="bi bi-pencil-square action-icon action-icon--dark"></i>Edit
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-tile">
                            <div class="stat-label">Total Fee</div>
                            <div class="stat-value stat-value--blue">{{ number_format($totalFee, 0) }}</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-label">Pending Fee</div>
                            <div class="stat-value stat-value--orange">{{ number_format($pendingFee, 0) }}</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-label">Total Members</div>
                            <div class="stat-value">{{ $membersCount }}</div>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="student-detail-content box-typical box-typical-dashboard panel panel-default">
                <ul class="student-tab-bar" role="tablist">
                    <li class="student-tab" data-target="#pane-coworking" role="tab">
                        Coworking History
                    </li>
                    <li class="student-tab active" data-target="#pane-account" role="tab">
                        Account History
                    </li>
                    <li class="student-tab" data-target="#pane-personal" role="tab">
                        Personal Information
                    </li>
                </ul>

                <div class="student-pane" id="pane-coworking">
                    <div class="pane-card table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    <th>Registration No</th>
                                    <th>Branch</th>
                                    <th>Space Type</th>
                                    <th>Business / Team</th>
                                    <th>Members</th>
                                    <th>Registration Date</th>
                                    <th>Next Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $member->registration_number }}</td>
                                    <td>{{ $branchCode }}@if($branchName)<br><small class="text-muted">{{ $branchName }}</small>@endif</td>
                                    <td>{{ $spaceType }}</td>
                                    <td>{{ $businessName }}</td>
                                    <td>{{ $membersCount }}</td>
                                    <td>{{ optional($member->registration_date)->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td>{{ optional($member->next_due_date)->format('Y-m-d') ?: 'N/A' }}</td>
                                    <td>
                                        <span class="label {{ $member->status === 'registered' ? 'label-success' : 'label-warning' }}">
                                            {{ $memberStatusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="student-pane is-active" id="pane-account">
                    <div class="pane-card table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    <th>Due Date</th>
                                    <th>Collected At</th>
                                    <!-- <th>Receipt Number</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountHistory as $fee)
                                    <tr>
                                        <td>{{ $fee['fee_type'] }}</td>
                                        <td>{{ number_format((float) $fee['amount'], 2) }}</td>
                                        <td class="fee-status-cell">
                                            @if($fee['status'] === 'paid')
                                                <span class="label label-success">Paid</span>
                                            @elseif($fee['status'] === 'refunded')
                                                <span class="label label-info">Refunded</span>
                                            @elseif($fee['status'] === 'pending')
                                                @if(str_contains($fee['fee_type'], 'Coworking Charges'))
                                                    <button type="button" class="label label-warning fee-action-label"
                                                            data-charge-modal-open>
                                                        Pending
                                                    </button>
                                                @else
                                                    <span class="label label-warning">Pending</span>
                                                @endif
                                            @else
                                                <span class="label label-default">{{ ucfirst($fee['status']) }}</span>
                                            @endif

                                            @if($fee['voucher_url'])
                                                <a class="btn btn-xs btn-default fee-action-btn"
                                                   title="View Receipt"
                                                   href="{{ $fee['voucher_url'] }}"
                                                   target="_blank"
                                                   rel="noopener">
                                                    <i class="bi bi-menu-button-wide"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ optional($fee['due_date'])->format('Y-m-d') ?: 'N/A' }}</td>
                                        <td>{{ optional($fee['collected_at'])->format('Y-m-d') ?: 'N/A' }}</td>
                                        <!-- <td>{{ $fee['receipt_number'] ?: 'N/A' }}</td> -->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">No account history found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="student-pane" id="pane-personal">
                    <div class="pane-card pane-card--info">
                        <h3 class="pane-section-title">Personal Information</h3>
                        <div class="row info-columns">
                            <div class="col-lg-12 info-column">
                                <table class="info-table">
                                    <tbody>
                                        <!-- <tr>
                                            <th>Full Name:</th>
                                            <td>{{ $member->full_name ?? 'N/A' }}</td>
                                        </tr> -->
                                        <tr>
                                            <th>Primary Contact:</th>
                                            <td>{{ $member->phone ?? 'N/A' }}</td>
                                        </tr>
                                         <tr>
                                            <th>Date of Birth:</th>
                                            <td>{{ optional($member->date_of_birth)->format('d-M-Y') ?: 'N/A' }}</td>
                                        </tr>
                                         <tr>
                                            <th>CNIC:</th>
                                            <td>{{ $member->cnic ?? 'N/A' }}</td>
                                        </tr>
                                         <tr>
                                            <th>Registration No:</th>
                                            <td>{{ $member->registration_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Guardian Name:</th>
                                            <td>{{ $member->guardian_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Postal Address:</th>
                                            <td>{{ $member->address ?? 'N/A' }}</td>
                                        </tr>
                                         <tr>
                                            <th>Gender:</th>
                                            <td>{{ ucfirst($member->gender ?? 'N/A') }}</td>
                                        </tr>
                                       
                                       
                                        <!-- <tr>
                                            <th>Email Address:</th>
                                            <td>{{ $member->email ?? 'N/A' }}</td>
                                        </tr> -->
                                        <tr>
                                            <th>Qualification:</th>
                                            <td>{{ $member->education ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Guardian Contact:</th>
                                            <td>{{ $member->guardian_phone ?? 'N/A' }}</td>
                                        </tr>
                                       
                                        <!-- <tr>
                                            <th>Nature of Work:</th>
                                            <td>{{ $member->nature_of_work ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Timing:</th>
                                            <td>{{ $member->timing ?? 'N/A' }}</td>
                                        </tr>
                                        
                                        <tr>
                                            <th>Remarks:</th>
                                            <td>{{ $member->remarks ?: 'N/A' }}</td>
                                        </tr> -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- <div class="col-lg-6 info-column">
                                <table class="info-table">
                                    <tbody>
                                        <tr>
                                            <th>Preferred Branch:</th>
                                            <td>{{ $branchCode }}@if($branchName) - {{ $branchName }}@endif</td>
                                        </tr>
                                        <tr>
                                            <th>Space Type:</th>
                                            <td>{{ $spaceType }}</td>
                                        </tr>
                                        <tr>
                                            <th>Business / Team:</th>
                                            <td>{{ $businessName }}</td>
                                        </tr>
                                        <tr>
                                            <th>Area:</th>
                                            <td>{{ data_get($leadDetails, 'area') ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>City:</th>
                                            <td>{{ $lead?->city ?: ($campus?->city ?: 'N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Expected Start:</th>
                                            <td>{{ optional($expectedStartingDate)->format('d-M-Y h:i A') ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Additional Amenities:</th>
                                            <td>{{ data_get($leadDetails, 'additional_amenities') ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Origin:</th>
                                            <td>{{ $lead?->origin ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Marketing Source:</th>
                                            <td>{{ $lead?->marketing_source ?: 'N/A' }}</td>
                                        </tr>
                                       
                                        <tr>
                                            <th>Main Receipt No:</th>
                                            <td>{{ $member->receipt_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Registration Date:</th>
                                            <td>{{ optional($member->registration_date)->format('d-M-Y') ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Next Due Date:</th>
                                            <td>{{ optional($member->next_due_date)->format('d-M-Y') ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Security Fee Status:</th>
                                            <td>{{ $securityRefundReceipt ? 'Refunded' : 'Held' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Security Refund Receipt:</th>
                                            <td>{{ $securityRefundReceipt?->receipt_number ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Leave Date:</th>
                                            <td>{{ optional($member->leave_date)->format('d-M-Y') ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reason for Leaving:</th>
                                            <td>{{ $member->inactive_reason ?: 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> -->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @if($canMarkInactive || $showInactiveModal)
        <div class="fee-edit-modal{{ $showInactiveModal ? ' is-open' : '' }}" id="inactiveModal" aria-hidden="{{ $showInactiveModal ? 'false' : 'true' }}">
            <div class="fee-edit-backdrop" data-inactive-close></div>
            <div class="fee-edit-dialog inactive-dialog" role="dialog" aria-modal="true" aria-labelledby="inactiveModalTitle">
                <div class="fee-edit-header">
                    <h4 id="inactiveModalTitle">{{ $inactiveModalTitle }}</h4>
                    <button type="button" class="fee-edit-close" data-inactive-close aria-label="Close">&times;</button>
                </div>
                    <form method="POST"
                        action="{{ route('coworking-registrations.deactivate', $member) }}"
                        id="inactiveForm"
                        data-registration-date="{{ optional($member->registration_date)->toDateString() }}"
                        data-next-due-date="{{ optional($member->next_due_date)->toDateString() }}"
                        data-cycle-days="{{ $cycleDays }}"
                        data-security-fee="{{ number_format((float) $member->security_fee, 2, '.', '') }}"
                        data-daily-rate="{{ number_format((float) $dailyDeductionAmount, 2, '.', '') }}">
                    @csrf
                    <input type="hidden" name="inactive_action" id="inactive_action" value="{{ $inactiveAction }}">
                    <div class="fee-edit-body">
                        <div class="inactive-receipt-note">
                            Original Security Receipt:
                            <strong>{{ $securityReceipt?->receipt_number ?: 'N/A' }}</strong>
                        </div>

                        <div class="row gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0">
                            <div class="col-md-4">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_security_fee">Security Fee</label>
                                    <input type="text" id="inactive_security_fee" value="{{ number_format((float) $member->security_fee, 2, '.', '') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="leave_date">Leave Date</label>
                                    <input type="date" id="leave_date" name="leave_date" value="{{ $defaultLeaveDate }}" min="{{ optional($member->registration_date)->toDateString() }}" required>
                                    @error('leave_date')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_used_days">Used Days</label>
                                    <input type="text" id="inactive_used_days" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_daily_rate">Day Wise Amount</label>
                                    <input type="text" id="inactive_daily_rate" value="{{ number_format((float) $dailyDeductionAmount, 2, '.', '') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_usage_deduction">Day Wise Deduction</label>
                                    <input type="text" id="inactive_usage_deduction" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="damage_deduction_amount">Damage Deduction</label>
                                    <input type="number" step="0.01" min="0" id="damage_deduction_amount" name="damage_deduction_amount" value="{{ $defaultDamageDeduction }}" required>
                                    @error('damage_deduction_amount')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_refund_amount">Remaining Refundable Security</label>
                                    <input type="text" id="inactive_refund_amount" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="damage_notes">Damage Details</label>
                                    <textarea id="damage_notes" name="damage_notes" row gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0s="2" placeholder="Broken chair, desk damage, mirror issue...">{{ old('damage_notes', $member->damage_notes) }}</textarea>
                                    @error('damage_notes')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_reason" id="inactiveReasonLabel">{{ $inactiveReasonLabel }}</label>
                                    <input type="text" id="inactive_reason" name="inactive_reason" value="{{ old('inactive_reason', $member->inactive_reason) }}" required>
                                    @error('inactive_reason')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="inactive_remarks">Remarks</label>
                                    <textarea id="inactive_remarks" name="inactive_remarks"  placeholder="Additional notes...">{{ old('inactive_remarks', $member->inactive_remarks) }}</textarea>
                                    @error('inactive_remarks')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fee-edit-footer">
                        <button type="button" class="btn btn-primary-outline">Submit</button>
                        <button type="button" class="btn btn-danger-outline" data-inactive-close>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($canOpenChargeModal || $showChargeModal || $showUpcomingCoworkingCharge)
        <div class="fee-edit-modal{{ $showChargeModal ? ' is-open' : '' }}" id="chargeModal" aria-hidden="{{ $showChargeModal ? 'false' : 'true' }}">
            <div class="fee-edit-backdrop" data-charge-close></div>
            <div class="fee-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="chargeModalTitle">
                <div class="fee-edit-header">
                    <h4 id="chargeModalTitle">Collect Coworking Charge</h4>
                    <button type="button" class="fee-edit-close" data-charge-close aria-label="Close">&times;</button>
                </div>
                <form method="POST" action="{{ route('coworking-registrations.collect-charge', $member) }}" id="chargeForm">
                    @csrf
                    <div class="fee-edit-body">
                        <div class="row gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0 gap-0">
                            <div class="col-md-6">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="charge_date">Charge Date</label>
                                    <input type="date" id="charge_date" name="charge_date" value="{{ $defaultChargeDate }}" required>
                                    @error('charge_date')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fee-edit-field">
                                    <label class = "form-label" for="charge_amount">Charge Amount</label>
                                    <input type="number" step="0.01" min="1" id="charge_amount" name="charge_amount" value="{{ $defaultChargeAmount }}" required>
                                    @error('charge_amount')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fee-edit-field">
                                    <label class = " form-label">Due Date</label>
                                    <input type="text" value="{{ optional($member->next_due_date)->format('Y-m-d') ?: 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fee-edit-footer">
                        <button type="button" class="btn btn-primary-outline">Submit</button>
                        <button type="button" class="btn btn-danger-outline" data-inactive-close>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .student-detail-shell {
            padding: 0;
        }

        .student-detail-header {
            margin-bottom: 14px;
        }

        .student-detail-header .panel-body {
            padding: 14px 18px;
        }

        .student-detail-header .panel-title {
            margin: 0;
            font-size: 18px !important;
            font-weight: 600 !important;
        }

        .student-detail-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 16px;
            align-items: start;
        }

        @media (max-width: 991px) {
            .student-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .student-profile-card,
        .student-detail-content {
            padding: 0;
        }

        .student-profile-card {
            overflow: hidden;
        }

        .profile-banner {
            height: 150px;
            position: relative;
            overflow: hidden;
        }

        .profile-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(135deg, rgba(255,255,255,0.05) 0 12px, transparent 12px 24px),
                repeating-linear-gradient(45deg, rgba(255,255,255,0.04) 0 12px, transparent 12px 24px);
            pointer-events: none;
        }

        .profile-banner--coworking {
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.48), rgba(15, 23, 42, 0.48)),
                linear-gradient(135deg, #1f2937 0%, #334155 55%, #0f766e 100%);
        }

        .profile-avatar-wrap {
            margin-top: -50px;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #fff;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .profile-avatar svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .profile-body {
            padding: 12px 20px 20px;
            text-align: center;
        }

        .profile-name {
            margin: 8px 0 4px;
            font-size: 18px;
            font-weight: 600;
            color: #1f2d3d;
        }

        .profile-phone {
            color: #4c5a6a;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .profile-campus {
            color: #0f766e;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .profile-action {
            margin-bottom: 20px;
        }

        .fee-action-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            padding: 4px 10px;
            margin: 0;
            border: none;
            border-radius: 3px;
            background: #f0ad4e;
            color: #fff;
            font-size: 12px;
            text-transform: uppercase;
            cursor: pointer;
        }

        .fee-action-label:hover {
            background: #ec971f;
        }

        .student-action-wrap {
            display: inline-block;
            position: relative;
        }

        .student-action-btn {
            /* background: #fff !important; */
            /* color: #1f2d3d !important; */
            /* border: 1px solid #cfd7df !important; */
            /* padding: 6px 18px !important; */
            /* border-radius: 4px; */
            /* font-weight: 500; */
        }

        .student-action-btn:hover {
            /* border-color: #94a3b8 !important; */
        }

        .student-action-wrap .dropdown-menu {
            display: none;
            min-width: 240px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
            padding: 10px 0;
            /* margin-top: 8px; */
            position: absolute;
            left: 50%;
            transform: translateX(-36%);
            top: -564%;
            z-index: 50;
            text-align: left;
        }

        .student-action-wrap.is-open .dropdown-menu {
            display: block;
        }

        .student-action-wrap .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            color: #303740;
            font-size: 14px;
            line-height: 1.2;
            text-decoration: none;
        }

        .student-action-wrap .dropdown-item:hover {
            background: #f7fafc;
        }

        .student-action-wrap button.dropdown-item {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
        }

        .action-icon {
            width: 18px;
            flex: 0 0 18px;
            text-align: center;
            font-size: 18px;
        }

        .action-icon--danger {
            color: #e34b5f;
        }

        .action-icon--dark {
            color: #3b4450;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .stat-tile {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 6px;
            text-align: center;
        }

        .stat-label {
            font-size: 13px;
            color: #1f2d3d;
            font-weight: 600;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 600;
            margin-top: 4px;
            color: #1f2d3d;
        }

        .stat-value--blue {
            color: #0a96cc;
        }

        .stat-value--orange {
            color: #f5a623;
        }

        .student-detail-content {
            min-width: 0;
        }

        .student-tab-bar {
            display: flex;
            margin: 0;
            padding: 0;
            list-style: none;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .student-tab {
            flex: 1;
            padding: 14px 12px;
            text-align: center;
            cursor: pointer;
            color: #4c5a6a;
            font-size: 16px;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        }

        .student-tab:hover {
            background: #f8fbff;
        }

        .student-tab.active {
            color: #0a96cc;
            font-weight: 600;
            border-bottom-color: #0a96cc;
        }

        .student-pane {
            display: none;
        }

        .student-pane.is-active {
            display: block;
        }

        .pane-card {
            padding: 14px;
        }

        .pane-card--info {
            padding: 18px 20px;
        }

        .info-columns {
            margin-left: -12px;
            margin-right: -12px;
        }

        .info-column {
            padding-left: 12px;
            padding-right: 12px;
        }

        .student-pane .follow-table {
            margin-bottom: 0;
        }

        .student-pane .follow-table thead th {
            white-space: nowrap;
        }

        .student-pane .follow-table tbody td {
            vertical-align: middle;
        }

        .empty-state {
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            padding: 22px 12px !important;
        }

        .fee-status-cell {
            white-space: nowrap;
        }

        .fee-status-cell .label {
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .fee-action-btn {
            margin-left: 4px;
            padding: 3px 8px;
        }

        .fee-action-btn .fa {
            font-size: 12px;
        }

        .pane-section-title {
            margin: 0 0 14px;
            font-size: 18px;
            font-weight: 600;
            color: #1f2d3d;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            padding: 8px 0px 9px 0;
            border-bottom: 1px solid #eef2f7;
            font-size: 14px;
            vertical-align: middle;
        }

        .info-table th {
            max-width: 28%;
            text-align: left;
            color: #1f2d3d;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .info-table td {
            color: #334155;
        }

        .info-table tr:last-child th,
        .info-table tr:last-child td {
            border-bottom: 0;
        }

        .student-flash {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .inactive-receipt-note {
            font-size: 13px;
            color: #475569;
            margin-bottom: 7px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }

        .inactive-dialog {
            max-width: 760px;
        }

        .fee-edit-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .fee-edit-modal.is-open {
            display: flex;
        }

        .fee-edit-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
        }

        .fee-edit-dialog {
            position: relative;
            background: #fff;
            border-radius: 8px;
            width: 100%;
            height: 95vh;
            overflow-y: scroll !important;
            max-width: 723px;
            margin: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: feeEditIn 0.15s ease-out;
        }

        @keyframes feeEditIn {
            from { transform: translateY(-12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .fee-edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 18px;
            background: #1fb2ff;
            color: #fff;
        }

        .fee-edit-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .fee-edit-close {
            background: transparent;
            border: 0;
            color: #fff;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .fee-edit-body {
            padding: 18px;
        }

        .fee-edit-field {
            margin-bottom: 14px;
        }

        .fee-edit-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1f2d3d;
            margin-bottom: 6px;
        }

        .fee-edit-field input,
        .fee-edit-field textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
            color: #1f2d3d;
        }

        .fee-edit-field input[readonly],
        .fee-edit-field textarea[readonly] {
            background: #f8fafc;
        }

        .fee-edit-field input:focus,
        .fee-edit-field textarea:focus {
            outline: 0;
            border-color: #0a96cc;
            box-shadow: 0 0 0 2px rgba(10, 150, 204, 0.15);
        }

        .fee-edit-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 0px 31px 18px;
            /* border-top: 1px solid #e2e8f0; */
            /* background: #f8fafc; */
        }

        .btn-fee-cancel{
            padding: 7px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 0;
        }

        .btn-fee-cancel {
            background: #e2e8f0;
            color: #1f2d3d;
        }

        /* .btn-fee-save {
            background: #20b2aa;
            color: #fff;
        } */

        @media (max-width: 767px) {
            .student-tab-bar {
                flex-direction: column;
            }

            .info-column + .info-column {
                margin-top: 18px;
            }

            .info-table th,
            .info-table td {
                display: block;
                width: 100%;
            }

            .info-table th {
                padding-bottom: 4px;
                border-bottom: 0;
            }

            .info-table td {
                padding-top: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var tabs = document.querySelectorAll('.student-tab');
            var panes = document.querySelectorAll('.student-pane');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var target = this.dataset.target;
                    if (!target) {
                        return;
                    }

                    tabs.forEach(function (node) {
                        node.classList.remove('active');
                    });

                    panes.forEach(function (pane) {
                        pane.classList.remove('is-active');
                    });

                    this.classList.add('active');

                    var pane = document.querySelector(target);
                    if (pane) {
                        pane.classList.add('is-active');
                    }
                });
            });

            document.querySelectorAll('.student-action-btn').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var wrap = this.closest('.student-action-wrap');
                    if (!wrap) {
                        return;
                    }

                    document.querySelectorAll('.student-action-wrap.is-open').forEach(function (node) {
                        if (node !== wrap) {
                            node.classList.remove('is-open');
                        }
                    });

                    wrap.classList.toggle('is-open');
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('.student-action-wrap')) {
                    document.querySelectorAll('.student-action-wrap.is-open').forEach(function (node) {
                        node.classList.remove('is-open');
                    });
                }
            });

            var inactiveModal = document.getElementById('inactiveModal');
            var inactiveForm = document.getElementById('inactiveForm');
            var inactiveActionField = document.getElementById('inactive_action');
            var inactiveModalTitle = document.getElementById('inactiveModalTitle');
            var inactiveReasonLabel = document.getElementById('inactiveReasonLabel');
            var leaveDateField = document.getElementById('leave_date');
            var damageField = document.getElementById('damage_deduction_amount');
            var usedDaysField = document.getElementById('inactive_used_days');
            var usageDeductionField = document.getElementById('inactive_usage_deduction');
            var refundAmountField = document.getElementById('inactive_refund_amount');
            var dailyRateField = document.getElementById('inactive_daily_rate');

            function parseDateValue(value) {
                if (!value) {
                    return null;
                }

                var parts = value.split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) {
                    return null;
                }

                return new Date(parts[0], parts[1] - 1, parts[2]);
            }

            function recalcInactiveRefund() {
                if (!inactiveForm || !leaveDateField || !damageField || !usedDaysField || !usageDeductionField || !refundAmountField) {
                    return;
                }

                var registrationDate = parseDateValue(inactiveForm.dataset.registrationDate);
                var leaveDate = parseDateValue(leaveDateField.value);
                var securityFee = parseFloat(inactiveForm.dataset.securityFee || '0');
                var dailyRate = parseFloat(inactiveForm.dataset.dailyRate || '0');
                var damage = parseFloat(damageField.value || '0');

                if (!registrationDate || !leaveDate || leaveDate < registrationDate) {
                    usedDaysField.value = '0';
                    usageDeductionField.value = '0.00';
                    refundAmountField.value = securityFee.toFixed(2);
                    if (dailyRateField) {
                        dailyRateField.value = dailyRate.toFixed(2);
                    }
                    return;
                }

                // compute used days as days after next due date; if next due date not available, fall back to registration-based days
                var usedDays = 0;
                var nextDueRaw = inactiveForm.dataset.nextDueDate || inactiveForm.getAttribute('data-next-due-date');
                var nextDueDate = nextDueRaw ? parseDateValue(nextDueRaw) : null;
                if (nextDueDate) {
                    if (leaveDate > nextDueDate) {
                        var diffAfterDue = leaveDate.getTime() - nextDueDate.getTime();
                        usedDays = Math.floor(diffAfterDue / 86400000);
                    } else {
                        usedDays = 0;
                    }
                } else {
                    var diffMs = leaveDate.getTime() - registrationDate.getTime();
                    usedDays = Math.floor(diffMs / 86400000) + 1;
                }
                var usageDeduction = dailyRate * usedDays;
                var refundAmount = Math.max(0, securityFee - usageDeduction - Math.max(0, damage));

                usedDaysField.value = String(usedDays);
                usageDeductionField.value = usageDeduction.toFixed(2);
                refundAmountField.value = refundAmount.toFixed(2);

                if (dailyRateField) {
                    dailyRateField.value = dailyRate.toFixed(2);
                }
            }

            function openInactiveModal(config) {
                if (!inactiveModal) {
                    return;
                }

                if (inactiveActionField) {
                    inactiveActionField.value = config && config.action ? config.action : 'leave';
                }

                if (inactiveModalTitle) {
                    inactiveModalTitle.textContent = config && config.title ? config.title : 'Leave Coworking Member';
                }

                if (inactiveReasonLabel) {
                    inactiveReasonLabel.textContent = config && config.reasonLabel ? config.reasonLabel : 'Reason for Leaving';
                }

                inactiveModal.classList.add('is-open');
                inactiveModal.setAttribute('aria-hidden', 'false');
                recalcInactiveRefund();
            }

            function closeInactiveModal() {
                if (!inactiveModal) {
                    return;
                }

                inactiveModal.classList.remove('is-open');
                inactiveModal.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('[data-inactive-modal-open]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    document.querySelectorAll('.student-action-wrap.is-open').forEach(function (node) {
                        node.classList.remove('is-open');
                    });
                    openInactiveModal({
                        action: this.getAttribute('data-inactive-action'),
                        title: this.getAttribute('data-inactive-title'),
                        reasonLabel: this.getAttribute('data-inactive-reason-label')
                    });
                });
            });

            document.querySelectorAll('[data-inactive-close]').forEach(function (button) {
                button.addEventListener('click', function () {
                    closeInactiveModal();
                });
            });

            var chargeModal = document.getElementById('chargeModal');
            var chargeOpenButtons = document.querySelectorAll('[data-charge-modal-open]');
            var chargeCloseButtons = document.querySelectorAll('[data-charge-close]');

            function openChargeModal() {
                if (!chargeModal) {
                    return;
                }

                chargeModal.classList.add('is-open');
                chargeModal.setAttribute('aria-hidden', 'false');
            }

            function closeChargeModal() {
                if (!chargeModal) {
                    return;
                }

                chargeModal.classList.remove('is-open');
                chargeModal.setAttribute('aria-hidden', 'true');
            }

            chargeOpenButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    document.querySelectorAll('.student-action-wrap.is-open').forEach(function (node) {
                        node.classList.remove('is-open');
                    });
                    openChargeModal();
                });
            });

            chargeCloseButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    closeChargeModal();
                });
            });

            if (leaveDateField) {
                leaveDateField.addEventListener('change', recalcInactiveRefund);
            }

            if (damageField) {
                damageField.addEventListener('input', recalcInactiveRefund);
            }

            @if($showInactiveModal)
                openInactiveModal();
            @endif

            @if($showChargeModal)
                openChargeModal();
            @endif
        })();
    </script>
@endpush
