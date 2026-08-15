@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.theme')

@section('title', 'Create New Coworking Space Registration')

@section('content')
    @php
        $registration = $registration ?? null;
        $isEditMode = (bool) ($isEditMode ?? false);
        $leadDetails = array_merge($lead->details ?? [], array_filter([
            'guardian_name' => $registration?->guardian_name,
            'guardian_phone' => $registration?->guardian_phone,
            'cnic' => $registration?->cnic,
            'current_education' => $registration?->education,
            'date_of_birth' => optional($registration?->date_of_birth)->toDateString(),
            'nature_of_work' => $registration?->nature_of_work,
            'timing' => $registration?->timing,
            'gender' => $registration?->gender,
            'address' => $registration?->address,
            'coworking_charges' => $registration?->coworking_charges,
            'security_fee' => $registration?->security_fee,
            'remarks' => $registration?->remarks,
        ], fn ($value) => $value !== null));
        $selectedCampusId = old('campus_id', $defaultCampusId ?? $registration?->campus_id ?? $lead?->campus_id);
        $registrationDateValue = old('registration_date', $defaultRegistrationDate ?? optional($registration?->registration_date)->toDateString() ?? now()->toDateString());
        $nextDueDateValue = old('next_due_date', $defaultNextDueDate ?? optional($registration?->next_due_date)->toDateString() ?? now()->addMonthNoOverflow()->toDateString());
        $registrationTitle = $isEditMode ? 'Edit Coworking Space Registration' : 'Create New Coworking Space Registration';
        $formAction = $formAction ?? ($isEditMode && $registration ? route('coworking-registrations.update', $registration) : route('coworking-registrations.store'));
        $submitLabel = $submitLabel ?? ($isEditMode ? 'Update Registration' : 'Register Now');
        $leadId = $lead?->id ?? $registration?->lead_id;
        $cancelUrl = $isEditMode && $registration ? route('coworking-registrations.show', $registration) : url()->previous();
    @endphp

    <div class="registration-shell">
        <div class="registration-content">
            <div class="registration-card box-typical box-typical-dashboard panel panel-default">
                @unless(request()->boolean('embed'))
                    <header class="box-typical-header panel-heading registration-header">
                        <div class="tbl w-100">
                            <div class="tbl-row">
                                <div class="tbl-cell tbl-cell-title p-0 m-0">
                                    <h2 class="panel-title registration-title">{{ $registrationTitle }} <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span></h2>
                                </div>
                            </div>
                        </div>
                    </header>
                @endunless

                <div class="box-typical-body panel-body registration-body">
                    <form method="POST" action="{{ $formAction }}" id="coworking-registration-form" class="registration-form">
                        @csrf
                        @if($isEditMode)
                            @method('PUT')
                        @endif
                        @if(request()->boolean('embed'))
                            <input type="hidden" name="embed" value="1">
                        @endif
                        @if(!empty($leadId))
                            <input type="hidden" name="lead_id" value="{{ $leadId }}">
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Select Campus</label>
                                @if($isEditMode)
                                    <input type="hidden" name="campus_id" value="{{ $selectedCampusId }}">
                                @endif
                                <select class="form-control @error('campus_id') is-invalid @enderror" name="{{ $isEditMode ? 'campus_id_display' : 'campus_id' }}" id="coworking-campus" required {{ $isEditMode ? 'disabled' : '' }}>
                                    <option value="">- Select -</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected((string) $selectedCampusId === (string) $campus->id)>
                                            {{ $campus->code ?? $campus->name }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('campus_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Full Name (As Per CNIC)</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name', $registration?->full_name ?? $lead?->name) }}" placeholder="Enter full name" required>
                                @error('full_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Primary Contact Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $registration?->phone ?? $lead?->phone) }}" placeholder="03XXXXXXXXX" pattern="03[0-9]{9}" maxlength="11" required>
                                @error('phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Guardian Name</label>
                                <input type="text" class="form-control @error('guardian_name') is-invalid @enderror" name="guardian_name" value="{{ old('guardian_name', data_get($leadDetails, 'guardian_name')) }}" placeholder="Enter guardian name" required>
                                @error('guardian_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Guardian Contact Number</label>
                                <input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" name="guardian_phone" value="{{ old('guardian_phone', data_get($leadDetails, 'guardian_phone')) }}" placeholder="03XXXXXXXXX" pattern="03[0-9]{9}" maxlength="11" required>
                                @error('guardian_phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">National Identity Card (CNIC)</label>
                                <input type="text" class="form-control @error('cnic') is-invalid @enderror" name="cnic" value="{{ old('cnic', data_get($leadDetails, 'cnic')) }}" placeholder="Numbers only" pattern="[0-9]{13}" maxlength="13" required>
                                @error('cnic')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $registration?->email ?? $lead?->email) }}" placeholder="Enter email address" required>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Education</label>
                                <input type="text" class="form-control @error('education') is-invalid @enderror" name="education" value="{{ old('education', data_get($leadDetails, 'current_education')) }}" placeholder="Enter recent completed degree" required>
                                @error('education')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Date of Birth</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth', data_get($leadDetails, 'date_of_birth')) }}" max="{{ now()->subDay()->toDateString() }}" required>
                                @error('date_of_birth')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Nature of Work</label>
                                <input type="text" class="form-control @error('nature_of_work') is-invalid @enderror" name="nature_of_work" value="{{ old('nature_of_work', data_get($leadDetails, 'nature_of_work', data_get($leadDetails, 'business_name'))) }}" placeholder="Enter nature of work" required>
                                @error('nature_of_work')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Timing (From - To)</label>
                                <input type="text" class="form-control @error('timing') is-invalid @enderror" name="timing" value="{{ old('timing', data_get($leadDetails, 'timing')) }}" placeholder="Select time range" required>
                                @error('timing')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-3 mb-lg-1 registration-gender-group">
                                <label class="form-label text-dark fw-semibold registration-gender-title required">Gender</label>
                                <div class="row mt-2 choice-group registration-gender-options @error('gender') is-invalid @enderror">
                                    <div class="col-4 d-flex justify-content-center mb-1">
                                        <div class="form-check d-flex align-items-center mt-0">
                                            <input class="form-check-input mt-0 @error('gender') is-invalid @enderror"
                                                id="coworking-registration-gender-male"
                                                name="gender"
                                                type="radio"
                                                value="male"
                                                {{ old('gender', data_get($leadDetails, 'gender', 'male')) === 'male' ? 'checked' : '' }}
                                                required>
                                            <label class="form-check-label mb-0" for="coworking-registration-gender-male">Male</label>
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex justify-content-center mb-1">
                                        <div class="form-check d-flex align-items-center">
                                            <input class="form-check-input mt-0 @error('gender') is-invalid @enderror"
                                                id="coworking-registration-gender-female"
                                                name="gender"
                                                type="radio"
                                                value="female"
                                                {{ old('gender', data_get($leadDetails, 'gender', 'male')) === 'female' ? 'checked' : '' }}>
                                            <label class="form-check-label mb-0" for="coworking-registration-gender-female">Female</label>
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex justify-content-center">
                                        <div class="form-check d-flex align-items-center">
                                            <input class="form-check-input mt-0 @error('gender') is-invalid @enderror"
                                                id="coworking-registration-gender-other"
                                                name="gender"
                                                type="radio"
                                                value="other"
                                                {{ old('gender', data_get($leadDetails, 'gender', 'male')) === 'other' ? 'checked' : '' }}>
                                            <label class="form-check-label mb-0" for="coworking-registration-gender-other">Other</label>
                                        </div>
                                    </div>
                                </div>
                                @error('gender')
                                    <div class="field-error mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12">
                                <label class="form-label required">Postal Address</label>
                                <textarea class="form-control registration-textarea-address @error('address') is-invalid @enderror" name="address" rows="1" placeholder="Enter complete postal address..." required>{{ old('address', data_get($leadDetails, 'address')) }}</textarea>
                                @error('address')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label class="form-label required">Registration Number</label>
                                <input type="text" class="form-control" id="coworking-registration-number" value="{{ $preview['registration_number'] ?? $registration?->registration_number ?? '' }}" readonly>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label class="form-label required">Registration Date</label>
                                <input type="date" class="form-control @error('registration_date') is-invalid @enderror" name="registration_date" id="coworking-registration-date" value="{{ $registrationDateValue }}" required {{ $isEditMode ? 'readonly' : '' }}>
                                @error('registration_date')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label class="form-label required">Next Due Date</label>
                                <input type="date" class="form-control" id="coworking-next-due-date" value="{{ $nextDueDateValue }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label class="form-label required">Coworking Charges</label>
                                <input type="number" step="0.01" min="1" class="form-control @error('coworking_charges') is-invalid @enderror" name="coworking_charges" value="{{ old('coworking_charges', $registration?->coworking_charges ?? data_get($leadDetails, 'coworking_charges')) }}" required {{ $isEditMode ? 'readonly' : '' }}>
                                @error('coworking_charges')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label class="form-label required">Security Fee</label>
                                <input type="number" step="0.01" min="0" class="form-control @error('security_fee') is-invalid @enderror" name="security_fee" value="{{ old('security_fee', $registration?->security_fee ?? data_get($leadDetails, 'security_fee')) }}" required {{ $isEditMode ? 'readonly' : '' }}>
                                @error('security_fee')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label class="form-label required">Receipt Number</label>
                                <input type="text" class="form-control" id="coworking-receipt-number" value="{{ $preview['receipt_number'] ?? $registration?->receipt_number ?? '' }}" readonly>
                            </div>
                        </div>

                        @if(! $isEditMode)
                            <div class="form-row">
                                <div class="form-group col-md-6 col-lg-4">
                                    <label class="form-label required">Payment Method</label>
                                    <select class="form-control @error('payment_method') is-invalid @enderror" name="payment_method" required>
                                        <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                        <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                        <option value="online" @selected(old('payment_method') === 'online')>Online</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group col-12">
                                <label class="form-label required">Remarks</label>
                                <textarea class="form-control registration-textarea-remarks @error('remarks') is-invalid @enderror" name="remarks" rows="2" placeholder="Remarks">{{ old('remarks', data_get($leadDetails, 'remarks')) }}</textarea>
                                @error('remarks')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-actions registration-actions mb-2 mt-3 text-right">
                            <button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04">{{ $submitLabel }}</button>
                            <a href="{{ $cancelUrl }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 {{ request()->boolean('embed') ? 'embed-cancel' : '' }}">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-coworking-registration-create-1: 54px;
            --dimension-coworking-registration-create-2: 7px;
            --dimension-coworking-registration-create-3: none;
            --space-coworking-registration-create-1: 0 !important;
            --space-coworking-registration-create-2: 10px;
            --space-coworking-registration-create-3: -10px;
            --space-coworking-registration-create-4: 10px 14px;
            --space-coworking-registration-create-5: 8px;
            --color-coworking-registration-create-1: #00a8ff;
            --color-coworking-registration-create-2: #223a57;
            --color-coworking-registration-create-3: #5f7289;
            --color-coworking-registration-create-4: #e53935;
            --color-coworking-registration-create-5: #fff;
        }

        :root {
            --dimension-coworking-registration-create-1: 54px;
            --dimension-coworking-registration-create-2: 7px;
            --dimension-coworking-registration-create-3: none;
            --space-coworking-registration-create-1: 0 !important;
            --space-coworking-registration-create-2: 10px;
            --space-coworking-registration-create-3: -10px;
            --space-coworking-registration-create-4: 10px 14px;
            --space-coworking-registration-create-5: 8px;
            --typo-coworking-registration-create-font-weight-1: 600;
        }0___

        .ci-inline-pad-04 {
            padding: 0.4rem !important;
        }

        .registration-shell {
            font-family: 'Proxima Nova', sans-serif;
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        .registration-content {
            position: relative;
            min-height: 400px;
        }

        .registration-card {
            overflow: visible !important;
            max-height: var(--dimension-coworking-registration-create-3) !important;
            border: 1px solid #e3edf7;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            background: var(--color-coworking-registration-create-5);
        }

        .registration-card .panel-heading {
            padding: 10px 20px;
        }

        .registration-card .panel-body {
            max-height: var(--dimension-coworking-registration-create-3) !important;
            overflow: visible !important;
        }

        .registration-header {
            border-bottom: 1px solid #e6eef3;
            background: var(--color-coworking-registration-create-5);
        }

        .registration-title {
            font-size: 1.125rem;
            font-weight: 500;
            color: #25364a;
            margin: 0;
        }

        .registration-title > span {
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--color-coworking-registration-create-3);
        }

        .registration-body {
            padding: 10px 10px 5px;
            overflow: visible !important;
        }

        .registration-form .required::after {
            content: ' *';
            color: var(--color-coworking-registration-create-4);
        }

        .registration-form .form-row {
            margin-left: var(--space-coworking-registration-create-3);
            margin-right: var(--space-coworking-registration-create-3);
        }

        .registration-form .form-row > [class*="col-"] {
            padding-left: var(--space-coworking-registration-create-2);
            padding-right: var(--space-coworking-registration-create-2);
        }

        .registration-form .form-group {
            margin-bottom: 18px;
        }

        .registration-form label,
        .registration-form .form-label {
            display: block;
            min-height: 22px;
            margin-bottom: var(--space-coworking-registration-create-5);
            font-weight: var(--typo-coworking-registration-create-font-weight-1);
            color: var(--color-coworking-registration-create-2);
        }

        .registration-form .form-control {
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid #d6e2f0;
            padding: var(--space-coworking-registration-create-4);
            background: var(--color-coworking-registration-create-5);
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .registration-form textarea.form-control {
            min-height: 92px;
            resize: vertical;
        }

        .registration-form .registration-textarea-address,
        .registration-form .registration-textarea-remarks {
            min-height: var(--dimension-coworking-registration-create-1) !important;
            height: var(--dimension-coworking-registration-create-1) !important;
            max-height: var(--dimension-coworking-registration-create-1) !important;
            resize: none;
        }

        .registration-form .form-control:focus {
            border-color: #14a2f6;
            box-shadow: 0 0 0 3px rgba(20, 162, 246, 0.12);
        }

        .registration-form .form-control[disabled],
        .registration-form .form-control[readonly] {
            background: #f4f8fc;
            color: var(--color-coworking-registration-create-3);
        }

        .registration-form .field-error {
            margin-top: 6px;
            font-size: 0.75rem;
            color: #dc3545;
        }

        .registration-form .choice-group {
            margin-left: 0;
            margin-right: 0;
            align-items: center;
            padding-top: 4px;
            padding-bottom: 2px;
        }

        .registration-form .choice-group.is-invalid {
            border: 1px solid var(--color-coworking-registration-create-4);
            border-radius: 6px;
            padding: 4px 0;
        }

        .registration-form .form-control.is-invalid {
            border-color: var(--color-coworking-registration-create-4) !important;
            box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
        }

        .registration-form .choice-group .form-check {
            display: inline-flex !important;
            align-items: center !important;
            gap: var(--space-coworking-registration-create-5);
            margin-bottom: 0;
            padding-left: 0;
            position: relative;
        }

        .registration-actions {
            padding: 0 10px 4px;
        }

        .registration-gender-title {
            font-size: 0.9375rem;
            font-weight: var(--typo-coworking-registration-create-font-weight-1);
            margin-bottom: var(--space-coworking-registration-create-2);
        }

        .registration-gender-options {
            margin-top: var(--space-coworking-registration-create-1);
        }

        .registration-gender-group .form-check-input[type="radio"] {
            width: 17px;
            height: 17px !important;
            border-width: 2px;
            margin: var(--space-coworking-registration-create-1);
            position: static;
            top: auto;
            left: auto;
            flex: 0 0 auto;
        }

        .registration-form .form-check-input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px !important;
            border: 2px solid grey;
            border-radius: 50%;
            outline: none;
            cursor: pointer;
            position: relative;
            background-color: var(--color-coworking-registration-create-5);
            transition: background 0.2s, box-shadow 0.2s;
        }

        .registration-form .form-check-input[type="radio"]:checked {
            border-color: var(--color-coworking-registration-create-1);
        }

        .registration-form .form-check-input[type="radio"]:checked::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: var(--dimension-coworking-registration-create-2);
            height: var(--dimension-coworking-registration-create-2);
            border-radius: 50%;
            background-color: var(--color-coworking-registration-create-1);
        }

        .registration-gender-group .form-check-input[type="radio"]:checked::before {
            top: 3px;
            left: 3px;
            width: var(--dimension-coworking-registration-create-2);
            height: var(--dimension-coworking-registration-create-2);
        }

        .registration-gender-group .form-check-label {
            font-size: clamp(0.7375rem, 1.3vw, 0.9375rem) !important;
            margin: var(--space-coworking-registration-create-1);
            cursor: pointer;
            font-weight: var(--typo-coworking-registration-create-font-weight-1);
            color: var(--color-coworking-registration-create-2);
            line-height: 1.2;
            display: inline-flex;
            align-items: center;
        }

        .registration-form hr {
            margin: 8px 0 22px;
            border-top: 1px solid #e8eef5;
        }

        @media (max-width: 767px) {
            .registration-card .panel-heading {
                padding: var(--space-coworking-registration-create-4);
            }

            .registration-body {
                padding: 10px 8px 4px;
            }
        }

@if(request()->boolean('embed'))
        .registration-shell {
            padding: 0;
        }

        .registration-card {
            border-radius: 0;
            box-shadow: none;
            border: 0;
        }

        .registration-body {
            padding: 12px 12px 6px;
        }

        .registration-header {
            display: none;
        }
@endif
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var isEditMode = @json($isEditMode);
            var previewUrl = @json(route('coworking-registrations.preview'));

            function addOneMonth(dateValue) {
                if (!dateValue) {
                    return '';
                }

                var parts = dateValue.split('-').map(Number);
                if (parts.length !== 3 || parts.some(Number.isNaN)) {
                    return '';
                }

                var year = parts[0];
                var month = parts[1];
                var day = parts[2];
                var nextMonthLastDay = new Date(year, month + 1, 0).getDate();
                var nextDate = new Date(year, month, Math.min(day, nextMonthLastDay));

                return nextDate.toISOString().slice(0, 10);
            }

            function bindPreview() {
                if (isEditMode) {
                    return;
                }

                var campusField = document.getElementById('coworking-campus');
                var dateField = document.getElementById('coworking-registration-date');
                var nextDueField = document.getElementById('coworking-next-due-date');
                var regField = document.getElementById('coworking-registration-number');
                var receiptField = document.getElementById('coworking-receipt-number');

                if (!campusField || !dateField || !nextDueField || !regField || !receiptField) {
                    return;
                }

                function updateDueDate() {
                    nextDueField.value = addOneMonth(dateField.value);
                }

                function updateNumbers() {
                    updateDueDate();

                    if (!campusField.value) {
                        regField.value = '';
                        receiptField.value = '';
                        return;
                    }

                    var params = new URLSearchParams({
                        campus_id: campusField.value,
                        registration_date: dateField.value || ''
                    });

                    fetch(previewUrl + '?' + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(function (response) {
                        return response.ok ? response.json() : null;
                    })
                    .then(function (data) {
                        if (!data) {
                            regField.value = '';
                            receiptField.value = '';
                            return;
                        }

                        regField.value = data.registration_number || '';
                        receiptField.value = data.receipt_number || '';
                        if (data.next_due_date) {
                            nextDueField.value = data.next_due_date;
                        }
                    });
                }

                campusField.addEventListener('change', updateNumbers);
                dateField.addEventListener('change', updateNumbers);
                updateNumbers();
            }

            document.addEventListener('DOMContentLoaded', bindPreview);
        })();
    </script>
    @if(request()->boolean('embed'))
        <script>
            (function () {
                function clearErrors(form) {
                    form.querySelectorAll('.field-error').forEach(function (node) {
                        if (node.closest('.form-group')) {
                            node.textContent = '';
                        }
                    });

                    form.querySelectorAll('.is-invalid').forEach(function (field) {
                        field.classList.remove('is-invalid');
                    });
                }

                function renderErrors(form, errors) {
                    Object.entries(errors || {}).forEach(function (entry) {
                        var key = entry[0];
                        var messages = entry[1] || [];
                        var message = messages.length ? messages[0] : 'Invalid value.';
                        var field = form.querySelector('[name="' + key + '"]');

                        if (!field) {
                            return;
                        }

                        var formGroup = field.closest('.form-group');
                        var invalidTarget = field.type === 'radio'
                            ? (formGroup ? formGroup.querySelector('.choice-group') : field)
                            : field;
                        var errorNode = formGroup ? formGroup.querySelector('.field-error') : null;

                        if (invalidTarget) {
                            invalidTarget.classList.add('is-invalid');
                        }

                        if (errorNode) {
                            errorNode.textContent = message;
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    var form = document.getElementById('coworking-registration-form');
                    var submitButton = form ? form.querySelector('button[type="submit"]') : null;

                    document.querySelectorAll('.embed-cancel').forEach(function (btn) {
                        btn.addEventListener('click', function (event) {
                            event.preventDefault();
                            if (window.parent) {
                                window.parent.postMessage({ type: 'lead-modal-close' }, '*');
                            }
                        });
                    });

                    if (!form) {
                        return;
                    }

                    form.addEventListener('submit', async function (event) {
                        event.preventDefault();
                        clearErrors(form);

                        if (submitButton) {
                            submitButton.disabled = true;
                        }

                        try {
                            var response = await fetch(form.action, {
                                method: form.querySelector('[name="_method"]')?.value || 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || ''
                                },
                                credentials: 'same-origin',
                                body: new FormData(form)
                            });

                            var contentType = response.headers.get('content-type') || '';
                            var data = contentType.indexOf('application/json') !== -1 ? await response.json() : {};

                            if (response.status === 422) {
                                renderErrors(form, data.errors || {});

                                if (window.swal) {
                                    swal({
                                        title: 'Error',
                                        text: data.message || 'Please fix the highlighted fields and try again.',
                                        type: 'error'
                                    });
                                }

                                return;
                            }

                            if (!response.ok) {
                                throw new Error(data.message || 'Unable to save the coworking registration right now.');
                            }

                            if (window.parent) {
                                window.parent.postMessage({
                                    type: 'lead-modal-close',
                                    reload: true,
                                    status: data.status || (isEditMode ? 'Coworking registration updated successfully.' : 'Coworking registration created successfully.'),
                                    openUrls: data.open_urls || []
                                }, '*');
                            }
                        } catch (error) {
                            if (window.swal) {
                                swal({
                                    title: 'Error',
                                    text: error.message || 'Unable to save the coworking registration right now.',
                                    type: 'error'
                                });
                            }
                        } finally {
                            if (submitButton) {
                                submitButton.disabled = false;
                            }
                        }
                    });
                });
            })();
        </script>
    @endif
@endpush
