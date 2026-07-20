@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.theme')

@section('title', 'Create New Admission')

@section('content')
	<div class="admission-shell">
		<div class="admission-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				@unless(request()->boolean('embed'))
					<h3 class="panel-title">Create New Admission <span class="">(All fields marked with * are required)</span></h3>
					<hr>
				@endunless
				@if(session('error'))
					<div class="alert alert-danger" style="margin-bottom:14px;padding:10px 14px;border-radius:6px;background:#fdecea;color:#9b1c1c;border:1px solid #f5c2c0;">
						{{ session('error') }}
					</div>
				@endif
				@if($errors->any())
					<div class="alert alert-danger" style="margin-bottom:14px;padding:10px 14px;border-radius:6px;background:#fdecea;color:#9b1c1c;border:1px solid #f5c2c0;">
						<strong>Please fix the following:</strong>
						<ul style="margin:6px 0 0 18px;">
							@foreach($errors->all() as $err)
								<li>{{ $err }}</li>
							@endforeach
						</ul>
					</div>
				@endif
				<form method="POST" action="{{ route('admission.store') }}" id="admission-form" class="admission-form">
					@csrf
					@if(request()->boolean('embed'))
						<input type="hidden" name="embed" value="1">
					@endif
					@if(!empty($lead))
						<input type="hidden" name="lead_id" value="{{ $lead->id }}">
					@endif
					@if(!empty($sourceRegistration))
						<input type="hidden" name="source_registration_id" value="{{ $sourceRegistration->id }}">
					@endif
					@if(!empty($sourceAdmission))
						<input type="hidden" name="source_admission_id" value="{{ $sourceAdmission->id }}">
					@endif
					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Select Campus</label>
							<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ old('campus_id', $formDefaults['campus_id'] ?? '') == $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Select Course</label>
							<select class="form-control training-course-select @error('program_id') is-invalid @enderror" name="program_id" @disabled(!empty($isAnotherCourseEnrollment) && empty($hasAlternativePrograms)) required>
								<option value="">-Select-</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}"
										data-title="{{ $program->title ?? $program->name }}"
										data-fee="{{ number_format($program->fee) }}"
										data-fee-raw="{{ (float) ($program->fee ?? 0) }}"
										data-installments="{{ (int) ($program->installments ?? 1) }}"
										data-duration="{{ $program->duration_weeks / 4 }}"
										@selected(old('program_id', $formDefaults['program_id'] ?? '') == $program->id)>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
							@error('program_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Select Batch</label>
							<select class="form-control @error('batch_id') is-invalid @enderror" name="batch_id" required>
								<option value="">- Select -</option>
								@foreach($batches ?? [] as $batch)
									@php
										$batchLabel = $batch->code ?? $batch->name;
										$batchStart = $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : null;
										$batchEnd = $batch->end_time ? \Carbon\Carbon::parse($batch->end_time)->format('h:i A') : null;
										if ($batchStart && $batchEnd) {
											$batchLabel .= ' (' . $batchStart . ' - ' . $batchEnd . ')';
										} elseif ($batchStart) {
											$batchLabel .= ' (' . $batchStart . ')';
										}
									@endphp
									<option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
										{{ $batchLabel }}
									</option>
								@endforeach
							</select>
							@error('batch_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Student Name (As per CNIC)</label>
							<input type="text" class="form-control @error('student_name') is-invalid @enderror" name="student_name" value="{{ old('student_name', $formDefaults['student_name'] ?? '') }}" placeholder="Enter full name" required>
							@error('student_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Primary Contact Number</label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $formDefaults['phone'] ?? '') }}" placeholder="03XXXXXXXXX" required>
							@error('phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Guardian Name</label>
							<input type="text" class="form-control @error('guardian_name') is-invalid @enderror" name="guardian_name" value="{{ old('guardian_name', $formDefaults['guardian_name'] ?? '') }}" placeholder="Enter guardian name" required>
							@error('guardian_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Guardian Contact Number</label>
							<input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" name="guardian_phone" value="{{ old('guardian_phone', $formDefaults['guardian_phone'] ?? '') }}" placeholder="03XXXXXXXXX" required>
							@error('guardian_phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control @error('cnic') is-invalid @enderror" name="cnic" value="{{ old('cnic', $formDefaults['cnic'] ?? '') }}" placeholder="13 digits without dashes" pattern="[0-9]{13}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);" required>
							@error('cnic')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Email Address</label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $formDefaults['email'] ?? '') }}" placeholder="Enter email address" required>
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Education</label>
							<input type="text" class="form-control @error('education') is-invalid @enderror" name="education" value="{{ old('education', $formDefaults['education'] ?? '') }}" placeholder="Enter education" required>
							@error('education')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Date of Birth</label>
							<input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth', $formDefaults['date_of_birth'] ?? '') }}" required>
							@error('date_of_birth')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>

						<div class="form-group col-md-3">
							<label class="form-label required">Gender</label>
							<div class="row mt-1">
								<div class="col-4 d-flex justify-content-center ">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-gender-male"
											name="gender"
											value="male"
											@checked(old('gender', $formDefaults['gender'] ?? 'male') === 'male')>
										<label class="form-check-label  mb-0 mt-0" for="admission-gender-male">
											Male
										</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-gender-female"
											name="gender"
											value="female"
											@checked(old('gender', $formDefaults['gender'] ?? 'male') === 'female')>
										<label class="form-check-label  mt-0 mb-0" for="admission-gender-female">
											Female
										</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input  mt-0 mr-1"
											type="radio"
											id="admission-gender-other"
											name="gender"
											value="other"
											@checked(old('gender', $formDefaults['gender'] ?? 'male') === 'other')>
										<label class="form-check-label  mt-0 mb-0" for="admission-gender-other">
											Other
										</label>
									</div>
								</div>
							</div>
							@error('gender')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

						<!-- <div class="form-row">
							<div class="form-group col-md-3">
								<label class="form-label required">Country</label>
								<select class="form-control @error('country') is-invalid @enderror" id="adm-country-select" name="country" required></select>
								@error('country')
									<div class="field-error">{{ $message }}</div>
								@enderror
							</div>
							<div class="form-group col-md-3">
								<label class="form-label required">City</label>
								<select class="form-control @error('city') is-invalid @enderror" id="adm-city-select" name="city" required>
									<option>Loading...</option>
								</select>
								@error('city')
									<div class="field-error">{{ $message }}</div>
								@enderror
							</div>
							<div class="form-group col-md-3">
								<label class="form-label required">Area</label>
								<input type="text" class="form-control @error('area') is-invalid @enderror" name="area" value="{{ old('area') }}" placeholder="Enter area" required>
								@error('area')
									<div class="field-error">{{ $message }}</div>
								@enderror
							</div>
						</div> -->
					<div class="form-row">
					<div class="form-group col-12">
						<label class="form-label required">Postal Address</label>
						<textarea class="form-control admission-textarea-address @error('postal_address') is-invalid @enderror" name="postal_address" rows="1" placeholder="Enter complete postal address..." required>{{ old('postal_address', $formDefaults['postal_address'] ?? '') }}</textarea>
						@error('postal_address')
							<div class="field-error">{{ $message }}</div>
						@enderror
					</div>
					</div>
					<div class="form-row">
						<!-- <div class="form-group col-md-3">
							<label>Passport Number (Optional)</label>
							<input type="text" class="form-control @error('passport_number') is-invalid @enderror" name="passport_number" value="{{ old('passport_number') }}" placeholder="Enter passport number">
							@error('passport_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div> -->
						<div class="form-group col-md-3">
							<label class="form-label required">Registration Number</label>
							<input type="text"
								   class="form-control @error('registration_number') is-invalid @enderror"
								   name="registration_number"
								   id="admission-registration-number"
								   value="{{ old('registration_number', $previewRegistrationNumber) }}"
								   placeholder="Auto-generated"
								   readonly>
							@error('registration_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Roll Number</label>
							<input type="text"
								   class="form-control @error('roll_number') is-invalid @enderror"
								   name="roll_number"
								   id="admission-roll-number"
								   value="{{ old('roll_number', $previewRollNumber) }}"
								   placeholder="Auto-generated"
								   readonly>
							@error('roll_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Date of Admission</label>
							<input type="date"
								   class="form-control @error('admission_date') is-invalid @enderror"
								   name="admission_date"
								   value="{{ old('admission_date', now()->format('Y-m-d')) }}"
								   required>
							@error('admission_date')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Fee Package</label>
							<input type="number" step="0.01"
								   class="form-control @error('fee_package') is-invalid @enderror"
								   name="fee_package"
								   id="admission-fee-package"
								   value="{{ old('fee_package') }}"
								   readonly required>
							@error('fee_package')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">
								Applied Discount %
								<span class="discount-limit-hint" id="admission-discount-limit-hint"></span>
							</label>
							<input type="number" step="0.01"
								   class="form-control @error('discount_percent') is-invalid @enderror"
								   name="discount_percent"
								   id="admission-discount-percent"
								   value="{{ old('discount_percent') }}"
								   readonly required>
							@error('discount_percent')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Discount Amount</label>
							<input type="number" step="0.01" min="0"
								   class="form-control @error('discount_amount') is-invalid @enderror"
								   name="discount_amount"
								   id="admission-discount-amount"
								   value="{{ old('discount_amount') }}"
								   required>
							<div class="field-error" id="admission-discount-error"></div>
							@error('discount_amount')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Discounted Fee</label>
							<input type="number" step="0.01"
								   class="form-control @error('discounted_fee') is-invalid @enderror"
								   name="discounted_fee"
								   id="admission-discounted-fee"
								   value="{{ old('discounted_fee') }}"
								   readonly required>
							@error('discounted_fee')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Fee Type</label>
							<div class="row mt-1  @error('fee_type') is-invalid @enderror">
								<div class="col-6 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-fee-type-full"
											name="fee_type"
											value="full"
											@checked(old('fee_type', 'full') === 'full')>
										<label class="form-check-label small mt-0 mb-0" for="admission-fee-type-full">
											Full Fee
										</label>
									</div>
								</div>
								<div class="col-6 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-fee-type-installments"
											name="fee_type"
											value="installments"
											@checked(old('fee_type') === 'installments')>
										<label class="form-check-label small mt-0 mb-0" for="admission-fee-type-installments">
											Installments
										</label>
									</div>
								</div>
							</div>
							<small class="text-muted d-block mt-1" id="admission-fee-type-hint"></small>
							@error('fee_type')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row" id="installments-block" style="display:none;">
						<div class="form-group col-12">
							<label class="form-label">
								Installment Amounts
								<small class="text-muted" id="installments-max-hint"></small>
							</label>
							<div id="installments-rows" class="installments-rows"></div>
							<div class="installments-summary">
								<span>Total: <strong id="installments-total">0</strong></span>
								<span class="ml-3">Remaining: <strong id="installments-remaining">0</strong></span>
							</div>
							@error('installment_amounts')
								<div class="field-error mt-2">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12">
							<label class="form-label required">Remarks</label>
							<textarea class="form-control admission-textarea-remarks @error('remarks') is-invalid @enderror" name="remarks" rows="1" placeholder="Remarks" required>{{ old('remarks', $formDefaults['remarks'] ?? '') }}</textarea>
							@error('remarks')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Receipt Number</label>
							<input type="text"
								   class="form-control @error('receipt_number') is-invalid @enderror"
								   name="receipt_number"
								   id="admission-receipt-number"
								   value="{{ old('receipt_number', $previewReceiptNumber) }}"
								   placeholder="Auto-generated"
								   readonly>
							@error('receipt_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

						<div  class="form-actions mb-2 mt-3 text-right">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04" @disabled(!empty($isAnotherCourseEnrollment) && empty($hasAlternativePrograms))> Save Admission</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 {{ request()->boolean('embed') ? 'embed-cancel' : '' }}">Cancel</a>
						</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
        :root {
            --dimension-admission-create-1: 100%;
            --dimension-admission-create-2: 46px;
            --dimension-admission-create-3: 54px;
            --dimension-admission-create-4: 6px;
            --space-admission-create-1: 10px;
            --space-admission-create-2: -10px;
            --space-admission-create-3: 18px;
            --space-admission-create-4: 6px;
            --space-admission-create-5: 8px;
            --color-admission-create-1: #00a8ff;
            --color-admission-create-2: #14a2f6;
            --color-admission-create-3: #42556d;
            --color-admission-create-4: #54667a;
            --color-admission-create-5: #d6e2f0;
            --color-admission-create-6: #dc3545;
            --color-admission-create-7: #fff;
        }

        :root {
            --dimension-admission-create-1: 100%;
            --dimension-admission-create-2: 46px;
            --dimension-admission-create-3: 54px;
            --dimension-admission-create-4: 6px;
            --space-admission-create-1: 10px;
            --space-admission-create-2: -10px;
            --space-admission-create-3: 18px;
            --space-admission-create-4: 6px;
            --space-admission-create-5: 8px;
            --typo-admission-create-font-size-1: 14px;
            --typo-admission-create-font-weight-2: 600;
            --typo-admission-create-font-size-3: 12px;
        }

		.ci-inline-pad-04 {
			padding: 0.4rem !important;
		}

		.admission-shell {
			/* padding: 18px 0 24px; */
		}

		.admission-card {
			border: 1px solid #e3edf7;
			border-radius: 5px;
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
			overflow: hidden;
			background: var(--color-admission-create-7);
		}

		.admission-card .card-body {
			padding: 28px 30px 24px;
		}

		.adm-title {
			margin: 0 0 24px;
			font-size: 2.0625rem;
			font-weight: 800;
			line-height: 1.15;
			color: #183b68;
		}

		.adm-title small {
			font-size: 1.125rem;
			font-weight: 500;
			color: #70839a !important;
		}

		.required::after {
			content: ' *';
			color: #e53935;
		}

		.admission-prefill-alert {
			margin-bottom: 16px;
			border-radius: 10px;
			padding: 12px 16px;
			border: 1px solid #cae5ff;
			background: linear-gradient(135deg, #f7fbff 0%, #eef7ff 100%);
			color: #135898;
			font-size: var(--typo-admission-create-font-size-1);
			font-weight: var(--typo-admission-create-font-weight-2);
		}

		.admission-prefill-alert--warning {
			border-color: #f5d18b;
			background: linear-gradient(135deg, var(--color-admission-create-7)af0 0%, var(--color-admission-create-7)4db 100%);
			color: #8a5b00;
		}

		.field-help {
			margin-top: var(--space-admission-create-4);
			font-size: var(--typo-admission-create-font-size-3);
			color: var(--color-admission-create-4);
		}

		.admission-form .form-row {
			margin-left: var(--space-admission-create-2);
			margin-right: var(--space-admission-create-2);
		}

		.admission-form .form-row > [class*="col-"] {
			padding-left: var(--space-admission-create-1);
			padding-right: var(--space-admission-create-1);
		}

		.admission-form .form-group {
			margin-bottom: var(--space-admission-create-3);
		}

		.admission-form label,
		.admission-form .form-label {
			display: inline-block;
			margin-bottom: var(--space-admission-create-5);
			font-weight: var(--typo-admission-create-font-weight-2);
			/* color: #223a57 ; */
		}

		.admission-form .form-control {
			min-height: var(--dimension-admission-create-2);
			border-radius: 12px;
			border: 1px solid var(--color-admission-create-5);
			padding: 10px 14px;
			background: var(--color-admission-create-7);
			box-shadow: none;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.admission-form textarea.form-control {
			min-height: 92px;
			resize: vertical;
		}

		.admission-form .admission-textarea-address {
			min-height: var(--dimension-admission-create-3) !important;
			height: var(--dimension-admission-create-3) !important;
			max-height: var(--dimension-admission-create-3) !important;
			resize: none;
		}

		.admission-form .admission-textarea-remarks {
			min-height: var(--dimension-admission-create-3) !important;
			height: var(--dimension-admission-create-3) !important;
			max-height: var(--dimension-admission-create-3) !important;
			resize: none;
		}

		.admission-form .form-control:focus {
			border-color: var(--color-admission-create-2);
			box-shadow: 0 0 0 3px rgba(20, 162, 246, 0.12);
		}

		.admission-form .form-control[disabled],
		.admission-form .form-control[readonly] {
			background: #f4f8fc;
			color: #5f7289;
		}

		.admission-form .field-error {
			margin-top: var(--space-admission-create-4);
			font-size: var(--typo-admission-create-font-size-3);
			color: var(--color-admission-create-6);
		}

		.training-course-select {
			width: var(--dimension-admission-create-1);
			min-width: 0;
			max-width: var(--dimension-admission-create-1);
			display: block;
		}

		.training-course-select + .select2-container {
			width: 100% !important;
		}

		.training-course-option {
			display: flex;
			flex-direction: column;
			gap: 0;
			line-height: 1.25;
		}

		.training-course-option-line {
			display: block;
			white-space: normal;
			margin-bottom: 0;
		}

		.training-course-option-line:last-child {
			margin-bottom: 0;
		}

		.training-course-option-label {
			font-weight: 700 !important;
			color: var(--color-admission-create-4);
		}

		.training-course-option-value {
			color: #343434;
			display: inline;
			white-space: normal;
		}

		.select2-results__option--highlighted .training-course-option-label,
		.select2-results__option--highlighted .training-course-option-value {
			color: inherit;
		}

		.admission-form .choice-group {
			margin-left: 0;
			margin-right: 0;
			padding: 11px 14px;
			border: 1px solid var(--color-admission-create-5);
			border-radius: 12px;
			background: #fbfdff;
			min-height: var(--dimension-admission-create-2);
		}

		.admission-form .choice-group.is-invalid {
			border-color: var(--color-admission-create-6);
		}

		.admission-form .form-check-input[type="radio"] {
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
			background-color: var(--color-admission-create-7);
			transition: background 0.2s, box-shadow 0.2s;
		}

		.admission-form .form-check-input[type="radio"]:checked {
			border-color: var(--color-admission-create-1);
		}

		.admission-form .form-check-input[type="radio"]:checked::before {
			content: '';
			position: absolute;
			top: 2px;
			left: 2px;
			width: var(--dimension-admission-create-4);
			height: var(--dimension-admission-create-4);
			border-radius: 50%;
			background-color: var(--color-admission-create-1);
		}

		.admission-form .form-check-label {
			font-size: var(--typo-admission-create-font-size-1);
			margin-bottom: 0;
			cursor: pointer;
			/* color: var(--color-admission-create-3); */
			font-weight: var(--typo-admission-create-font-weight-2);
		}

		.embed-actions {
			position: sticky;
			bottom: 0;
			z-index: 2;
			display: flex;
			justify-content: flex-end;
			gap: 12px;
			padding-top: var(--space-admission-create-3);
			margin-top: var(--space-admission-create-4);
			border-top: 1px solid #e8eef5;
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.68) 0%, var(--color-admission-create-7) 28%);
		}

		.embed-actions .btn {
			min-width: 160px;
			height: 44px;
			border-radius: 12px;
			font-weight: 700;
		}

		.embed-actions .btn-primary {
			color: var(--color-admission-create-7) !important;
			background: linear-gradient(120deg, #0099f8, #17b3ff);
			border-color: transparent;
			box-shadow: 0 14px 28px rgba(0, 153, 248, 0.24);
		}

		.embed-actions .btn-primary:hover,
		.embed-actions .btn-primary:focus {
			color: var(--color-admission-create-7) !important;
			background: linear-gradient(120deg, #0088dd, #0ea4ef);
			border-color: transparent;
		}

		.embed-actions .btn-outline-danger {
			color: #d64545 !important;
			border: 1px solid rgba(214, 69, 69, 0.32);
			background: var(--color-admission-create-7);
		}

		.embed-actions .btn-outline-danger:hover,
		.embed-actions .btn-outline-danger:focus {
			color: var(--color-admission-create-7) !important;
			background: var(--color-admission-create-6);
			border-color: var(--color-admission-create-6);
		}

		.installments-rows {
			display: flex;
			flex-wrap: wrap;
			gap: var(--space-admission-create-5);
		}

		.installments-rows .installment-item {
			flex: 1 1 140px;
		}

		.installments-rows .installment-item label {
			font-size: var(--typo-admission-create-font-size-3);
			color: var(--color-admission-create-4);
			margin-bottom: 4px;
			display: block;
			font-weight: var(--typo-admission-create-font-weight-2);
		}

		.installments-rows .installment-item input {
			min-height: 38px;
			border-radius: 8px;
			border: 1px solid var(--color-admission-create-5);
			padding: 6px 10px;
			width: var(--dimension-admission-create-1);
			background: var(--color-admission-create-7);
		}

		.installments-rows .installment-item input:focus {
			outline: 0;
			border-color: var(--color-admission-create-2);
			box-shadow: 0 0 0 2px rgba(20, 162, 246, 0.15);
		}

		.installments-summary {
			margin-top: var(--space-admission-create-1);
			font-size: 0.8125rem;
			color: var(--color-admission-create-3);
		}

		.installments-summary strong {
			color: #1f2d3d;
		}

		.discount-limit-hint {
			color: var(--color-admission-create-6);
			font-weight: var(--typo-admission-create-font-weight-2);
			font-size: var(--typo-admission-create-font-size-3);
			margin-left: var(--space-admission-create-4);
		}

		.discount-limit-hint:empty {
			display: none;
		}

		#admission-discount-amount.is-invalid {
			border-color: var(--color-admission-create-6);
			background-color: var(--color-admission-create-7)5f5;
		}

		@media (max-width: 991px) {
			.adm-title {
				font-size: 1.75rem;
			}

			.adm-title small {
				display: block;
				margin-top: var(--space-admission-create-5);
			}
		}

		@media (max-width: 767px) {
			.admission-card .card-body {
				padding: 20px 18px 18px;
			}

			.embed-actions {
				flex-direction: column-reverse;
			}

			.embed-actions .btn {
				width: var(--dimension-admission-create-1);
			}
		}

@if(request()->boolean('embed'))
		.admission-shell {
			padding: var(--space-admission-create-3);
		}

		.admission-card {
			border-radius: 20px;
			box-shadow: none;
		}

		.admission-card .card-body {
			padding: 22px 22px 18px;
		}
@endif
	</style>
@endpush

@push('scripts')
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			CountryCityLoader.init('adm-country-select', 'adm-city-select', {
				country: @json(old('country', 'Pakistan')),
				city: @json(old('city', 'Faisalabad'))
			});
		});
	</script>

	<script>
		(function () {
			const programMap = @json($programMap);
			const discountMap = @json($discountMap);

			const campusEl = document.querySelector('select[name="campus_id"]');
			const programEl = document.querySelector('select[name="program_id"]');
			const feePackageEl = document.getElementById('admission-fee-package');
			const discountPctEl = document.getElementById('admission-discount-percent');
			const discountAmtEl = document.getElementById('admission-discount-amount');
			const discountLimitHintEl = document.getElementById('admission-discount-limit-hint');
			const discountErrorEl = document.getElementById('admission-discount-error');
			const discountedFeeEl = document.getElementById('admission-discounted-fee');
			const feeTypeFull = document.getElementById('admission-fee-type-full');
			const feeTypeInst = document.getElementById('admission-fee-type-installments');
			const feeTypeHintEl = document.getElementById('admission-fee-type-hint');
			const installmentsBlock = document.getElementById('installments-block');
			const installmentsRows = document.getElementById('installments-rows');
			const installmentsTotalEl = document.getElementById('installments-total');
			const installmentsRemainingEl = document.getElementById('installments-remaining');
			const installmentsMaxHint = document.getElementById('installments-max-hint');
			const initialInstallmentAmounts = @json(array_values(old('installment_amounts', [])));
			let pendingInstallmentAmounts = Array.isArray(initialInstallmentAmounts) ? initialInstallmentAmounts.slice() : [];

			let maxDiscountPercent = 0;
			let currentFee = 0;

			function round2(n) {
				return Math.round((Number(n) || 0) * 100) / 100;
			}

			function selectedProgramOption() {
				if (!programEl) {
					return null;
				}

				if (programEl.value !== '') {
					for (const option of programEl.options) {
						if (option.value === programEl.value) {
							return option;
						}
					}
				}

				return programEl.selectedIndex >= 0
					? programEl.options[programEl.selectedIndex]
					: null;
			}

			function selectedProgramInstallments() {
				const option = selectedProgramOption();
				const optionInstallments = option ? Number(option.getAttribute('data-installments')) : NaN;

				if (Number.isFinite(optionInstallments) && optionInstallments > 0) {
					return Math.max(1, optionInstallments);
				}

				const prog = programMap[programEl.value];

				return prog ? Math.max(1, Number(prog.installments) || 1) : 1;
			}

			function programInstallmentsMax() {
				return selectedProgramInstallments();
			}

			function programAllowsInstallments() {
				return programInstallmentsMax() > 1;
			}

			function updateFeeTypeHint() {
				if (!feeTypeHintEl) {
					return;
				}

				const max = programInstallmentsMax();

				if (!programEl.value) {
					feeTypeHintEl.textContent = 'Select a course first to load fee type options.';
					return;
				}

				feeTypeHintEl.textContent = max > 1
					? 'This course allows up to ' + max + ' installments.'
					: 'This course supports full fee only.';
			}

			function syncFeeTypeAvailability() {
				const allowsInstallments = programAllowsInstallments();

				feeTypeInst.disabled = !allowsInstallments;
				updateFeeTypeHint();

				if (!allowsInstallments && feeTypeInst.checked) {
					feeTypeFull.checked = true;
				}
			}

			function recalcFees() {
				const programId = programEl.value;
				const campusId = campusEl.value;
				const option = selectedProgramOption();
				const prog = programMap[programId];
				if (!prog) {
					currentFee = 0;
					maxDiscountPercent = 0;
					feePackageEl.value = '';
					discountPctEl.value = '';
					discountAmtEl.value = '';
					discountedFeeEl.value = '';
					discountLimitHintEl.textContent = '';
					clearDiscountError();
					syncFeeTypeAvailability();
					rebuildInstallments();
					return;
				}
				const optionFee = option ? Number(option.getAttribute('data-fee-raw')) : NaN;
				currentFee = Number.isFinite(optionFee) ? optionFee : (Number(prog.fee) || 0);
				const key = programId + ':' + (campusId || 'any');
				const fallbackKey = programId + ':any';
				maxDiscountPercent = Number(
					(campusId && discountMap[key] !== undefined ? discountMap[key] : discountMap[fallbackKey]) || 0
				);

				const amt = round2(currentFee * maxDiscountPercent / 100);
				const net = round2(currentFee - amt);

				feePackageEl.value = currentFee.toFixed(2);
				discountAmtEl.value = amt.toFixed(2);
				discountPctEl.value = maxDiscountPercent.toFixed(2);
				discountedFeeEl.value = net.toFixed(2);
				updateLimitHint();
				clearDiscountError();

				syncFeeTypeAvailability();
				rebuildInstallments();
			}

			function updateLimitHint() {
				const maxAmount = maxDiscountAmount();
				if (maxDiscountPercent > 0) {
					discountLimitHintEl.textContent = 'limit (' + maxDiscountPercent + '% / Rs. ' + maxAmount.toFixed(2) + ')';
				} else {
					discountLimitHintEl.textContent = '';
				}
			}

			function maxDiscountAmount() {
				return round2(currentFee * maxDiscountPercent / 100);
			}

			function clearDiscountError() {
				discountErrorEl.textContent = '';
				discountAmtEl.classList.remove('is-invalid');
			}

			function showDiscountError(msg) {
				discountErrorEl.textContent = msg;
				discountAmtEl.classList.add('is-invalid');
			}

			function onDiscountAmountInput() {
				if (currentFee <= 0) {
					return;
				}
				const entered = round2(Number(discountAmtEl.value) || 0);
				const cap = maxDiscountAmount();

				if (entered > cap) {
					showDiscountError('Discount cannot exceed ' + cap.toFixed(2) + ' (' + maxDiscountPercent + '%).');
				} else if (entered < 0) {
					showDiscountError('Discount cannot be negative.');
				} else {
					clearDiscountError();
				}

				const pct = round2((entered / currentFee) * 100);
				discountPctEl.value = pct.toFixed(2);
				discountedFeeEl.value = round2(currentFee - entered).toFixed(2);

				if (feeTypeInst.checked) {
					renderInstallmentInputs();
				}
			}

			function discountedFee() {
				return Number(discountedFeeEl.value) || 0;
			}

			function rebuildInstallments() {
				const max = programInstallmentsMax();

				if (max <= 1) {
					installmentsMaxHint.textContent = '';
					installmentsRows.innerHTML = '';
					installmentsTotalEl.textContent = '0.00';
					installmentsRemainingEl.textContent = '0.00';
					return;
				}

				installmentsMaxHint.textContent = '(Program allows ' + max + ' installment' + (max === 1 ? '' : 's') + '.)';
				renderInstallmentInputs();
			}

			function renderInstallmentInputs() {
				const n = programInstallmentsMax();
				const total = discountedFee();
				installmentsRows.innerHTML = '';
				let amounts = [];

				if (pendingInstallmentAmounts.length > 0) {
					for (let i = 0; i < n; i++) {
						amounts.push(round2(Number(pendingInstallmentAmounts[i]) || 0));
					}
					pendingInstallmentAmounts = [];
				} else {
					const baseSplit = round2(total / n);
					for (let i = 0; i < n; i++) {
						amounts.push(baseSplit);
					}
					const drift = round2(total - amounts.reduce((s, a) => s + a, 0));
					if (drift !== 0 && n > 0) {
						amounts[n - 1] = round2(amounts[n - 1] + drift);
					}
				}

				for (let i = 0; i < n; i++) {
					const wrap = document.createElement('div');
					wrap.className = 'installment-item';

					const label = document.createElement('label');
					label.textContent = 'Installment ' + (i + 1);

					const input = document.createElement('input');
					input.type = 'number';
					input.step = '0.01';
					input.min = '0';
					input.name = 'installment_amounts[]';
					input.value = amounts[i].toFixed(2);
					input.dataset.idx = i;
					input.addEventListener('input', onInstallmentInput);

					wrap.appendChild(label);
					wrap.appendChild(input);
					installmentsRows.appendChild(wrap);
				}
				updateSummary();
			}

			function onInstallmentInput(e) {
				const inputs = installmentsRows.querySelectorAll('input');
				const idx = Number(e.target.dataset.idx);
				const total = discountedFee();

				let sumThroughChanged = 0;
				for (let i = 0; i <= idx; i++) {
					sumThroughChanged += round2(Number(inputs[i].value) || 0);
				}

				const remaining = round2(total - sumThroughChanged);
				const tail = inputs.length - (idx + 1);
				if (tail <= 0) {
					updateSummary();
					return;
				}

				if (remaining <= 0) {
					for (let i = idx + 1; i < inputs.length; i++) {
						inputs[i].value = '0.00';
					}
				} else {
					const split = round2(remaining / tail);
					let assigned = 0;
					for (let i = idx + 1; i < inputs.length - 1; i++) {
						inputs[i].value = split.toFixed(2);
						assigned = round2(assigned + split);
					}
					inputs[inputs.length - 1].value = round2(remaining - assigned).toFixed(2);
				}

				updateSummary();
			}

			function updateSummary() {
				const inputs = installmentsRows.querySelectorAll('input');
				let sum = 0;
				inputs.forEach(function (i) { sum = round2(sum + (Number(i.value) || 0)); });
				installmentsTotalEl.textContent = sum.toFixed(2);
				installmentsRemainingEl.textContent = round2(discountedFee() - sum).toFixed(2);
			}

			function toggleInstallmentsBlock() {
				syncFeeTypeAvailability();

				if (feeTypeInst.checked && !feeTypeInst.disabled) {
					installmentsBlock.style.display = 'flex';
					if (installmentsRows.querySelectorAll('input').length !== programInstallmentsMax()) {
						rebuildInstallments();
					} else {
						updateSummary();
					}
				} else {
					installmentsBlock.style.display = 'none';
				}
			}

			function refreshProgramPricingState() {
				refreshBatchOptions();
				refreshPreviewNumbers();
				recalcFees();
				toggleInstallmentsBlock();
			}

			function queueProgramPricingStateRefresh() {
				if (window.requestAnimationFrame) {
					window.requestAnimationFrame(refreshProgramPricingState);
					return;
				}

				window.setTimeout(refreshProgramPricingState, 0);
			}

			const regNumberEl = document.getElementById('admission-registration-number');
			const rollNumberEl = document.getElementById('admission-roll-number');
			const receiptNumberEl = document.getElementById('admission-receipt-number');
			const batchEl = document.querySelector('select[name="batch_id"]');
			const leadId = @json(!empty($isAnotherCourseEnrollment) ? null : optional($lead)->id);
			const previewUrl = @json(route('admission.preview-numbers'));
			const existingRegFromServer = @json($existingRegistration?->registration_number);

			function refreshPreviewNumbers() {
				const campusId = campusEl.value;
				const batchId = batchEl ? batchEl.value : '';
				const programId = programEl.value;
				if (!batchId) {
					rollNumberEl.value = '';
				}
				if (!campusId) return;
				const params = new URLSearchParams({ campus_id: campusId });
				if (batchId) params.append('batch_id', batchId);
				if (programId) params.append('program_id', programId);
				if (leadId) params.append('lead_id', leadId);
				fetch(previewUrl + '?' + params.toString(), {
					headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
					credentials: 'same-origin'
				})
				.then(function (r) { return r.ok ? r.json() : null; })
				.then(function (data) {
					if (!data) return;
					if (!existingRegFromServer && data.registration_number) {
						regNumberEl.value = data.registration_number;
					}
					rollNumberEl.value = data.roll_number || '';
					if (receiptNumberEl && data.receipt_number) {
						receiptNumberEl.value = data.receipt_number;
					}
				});
			}

			const allBatches = @json($batchList);

			function formatBatchLabel(b) {
				let label = b.code || b.name || ('Batch #' + b.id);
				const fmt = function (t) {
					if (!t) return null;
					const parts = String(t).split(':');
					if (parts.length < 2) return null;
					let h = parseInt(parts[0], 10);
					const m = parts[1];
					const ampm = h >= 12 ? 'PM' : 'AM';
					h = h % 12 || 12;
					return (h < 10 ? '0' + h : h) + ':' + m + ' ' + ampm;
				};
				const s = fmt(b.start_time);
				const e = fmt(b.end_time);
				if (s && e) label += ' (' + s + ' - ' + e + ')';
				else if (s) label += ' (' + s + ')';
				return label;
			}

			function refreshBatchOptions() {
				if (!batchEl) return false;
				const campusId = campusEl.value ? Number(campusEl.value) : null;
				const programId = programEl.value ? Number(programEl.value) : null;
				const previousValue = batchEl.value;

				const filtered = allBatches.filter(function (b) {
					if (campusId && b.campus_id && Number(b.campus_id) !== campusId) return false;
					if (programId && b.program_id && Number(b.program_id) !== programId) return false;
					return true;
				});

				batchEl.innerHTML = '';
				const placeholder = document.createElement('option');
				placeholder.value = '';
				placeholder.textContent = '- Select -';
				batchEl.appendChild(placeholder);

				filtered.forEach(function (b) {
					const opt = document.createElement('option');
					opt.value = b.id;
					opt.textContent = formatBatchLabel(b);
					batchEl.appendChild(opt);
				});

				if (previousValue && filtered.some(function (b) { return String(b.id) === String(previousValue); })) {
					batchEl.value = previousValue;
				} else if (filtered.length === 1) {
					batchEl.value = filtered[0].id;
				} else {
					batchEl.value = '';
				}

				return batchEl.value !== previousValue;
			}

			campusEl.addEventListener('change', function () { refreshBatchOptions(); refreshPreviewNumbers(); recalcFees(); });
			programEl.addEventListener('change', queueProgramPricingStateRefresh);
			if (batchEl) batchEl.addEventListener('change', refreshPreviewNumbers);

			if (window.jQuery) {
				jQuery(programEl).on('change select2:select select2:clear select2:close', queueProgramPricingStateRefresh);
			}

			refreshBatchOptions();
			refreshPreviewNumbers();
			discountAmtEl.addEventListener('input', onDiscountAmountInput);
			feeTypeFull.addEventListener('change', toggleInstallmentsBlock);
			feeTypeInst.addEventListener('change', toggleInstallmentsBlock);

			function init() {
				if (programEl.value) recalcFees();
				toggleInstallmentsBlock();
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', init);
			} else {
				init();
			}
		})();
	</script>
	<script>
		(function () {
			function escapeHtml(value) {
				return String(value ?? '')
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#39;');
			}

			function formatTrainingCourseOption(state) {
				if (!state.id) {
					return state.text;
				}

				var option = state.element;
				if (!option) {
					return state.text;
				}

				var title = option.getAttribute('data-title') || state.text || '';
				var fee = option.getAttribute('data-fee') || '';
				var duration = option.getAttribute('data-duration') || '';

				return (
					'<div class="training-course-option">' +
						'<span class="training-course-option-line"> <span class="training-course-option-label">' + escapeHtml(title) + '</span></span>' +
						'<span class="training-course-option-line"><span class="training-course-option-label">Fee:</span> <span class="training-course-option-value">' + escapeHtml(fee) + '</span></span>' +
						'<span class="training-course-option-line"><span class="training-course-option-label">Duration:</span> <span class="training-course-option-value">' + escapeHtml(duration) + ' months</span></span>' +
					'</div>'
				);
			}

			function formatTrainingCourseSelection(state) {
				if (!state.id) {
					return state.text;
				}

				var option = state.element;
				return option ? (option.getAttribute('data-title') || state.text || '') : state.text;
			}

			$(function () {
				if (!window.jQuery || !$.fn.select2) {
					return;
				}

				$('.training-course-select').select2({
					width: '100%',
					templateResult: function (state) {
						return $(formatTrainingCourseOption(state));
					},
					templateSelection: function (state) {
						return formatTrainingCourseSelection(state);
					},
					escapeMarkup: function (markup) {
						return markup;
					}
				});
			});
		})();
	</script>
	@if(request()->boolean('embed'))
	<script>
		(function () {
			function clearErrors(form) {
				form.querySelectorAll('.field-error').forEach(function (node) {
					node.textContent = '';
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

					if (field) {
						var formGroup = field.closest('.form-group');
						var invalidTarget = field.type === 'radio'
							? (formGroup ? formGroup.querySelector('.gender-options') : field)
							: field;
						var errorNode = formGroup ? formGroup.querySelector('.field-error') : null;

						if (invalidTarget) {
							invalidTarget.classList.add('is-invalid');
						}

						if (errorNode) {
							errorNode.textContent = message;
						}
					}
				});
			}

			document.addEventListener('DOMContentLoaded', function () {
				var form = document.getElementById('admission-form');
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
							method: 'POST',
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
							throw new Error(data.message || 'Unable to save the admission right now.');
						}

						if (window.parent) {
							window.parent.postMessage({
								type: 'lead-modal-close',
								reload: true,
								status: data.status || 'Admission created successfully.'
							}, '*');
						}
					} catch (error) {
						if (window.swal) {
							swal({
								title: 'Error',
								text: error.message || 'Unable to save the admission right now.',
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
