@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.theme')

@section('title', 'Create New Registration')

@section('content')
	@php
		$leadDetails = $lead->details ?? [];
		$selectedCampusId = old('campus_id', $lead->campus_id ?? ($defaultCampusId ?? null));
		$selectedProgramId = old('program_id', $lead->program_id ?? null);
	@endphp
	<div class="registration-shell">
		<div class="registration-content">
			<div class="registration-card box-typical box-typical-dashboard panel panel-default">
				@unless(request()->boolean('embed'))
					<header class="box-typical-header panel-heading registration-header">
						<div class="tbl w-100">
							<div class="tbl-row">
								<div class="tbl-cell tbl-cell-title p-0 m-0">
									<h2 class="panel-title registration-title">Create New Registration <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span></h2>
								</div>
							</div>
						</div>
					</header>
				@endunless
				<div class="box-typical-body panel-body registration-body">
					<form method="POST" action="{{ route('registration.store') }}" id="registration-form" class="registration-form">
					@csrf
					@if(request()->boolean('embed'))
						<input type="hidden" name="embed" value="1">
					@endif
					@if(!empty($lead))
						<input type="hidden" name="lead_id" value="{{ $lead->id }}">
					@endif
					<div class="form-row">
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Select Campus</label>
							<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ (string) $selectedCampusId === (string) $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Select Course</label>
							<select class="form-control training-course-select @error('program_id') is-invalid @enderror" name="program_id" required>
								<option value="">-Select-</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}"
										data-title="{{ $program->title ?? $program->name }}"
										data-fee="{{ number_format($program->fee) }}"
										data-duration="{{ $program->duration_weeks / 4 }}"
										{{ (string) $selectedProgramId === (string) $program->id ? 'selected' : '' }}>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
							@error('program_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Full Name (As Per CNIC)</label>
							<input type="text" class="form-control @error('student_name') is-invalid @enderror" name="student_name" placeholder="Enter full name" value="{{ old('student_name', $lead->name ?? '') }}" required>
							@error('student_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Primary Contact Number</label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" placeholder="03XXXXXXXXX" value="{{ old('phone', $lead->phone ?? '') }}" pattern="03[0-9]{9}" maxlength="11" required>
							@error('phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Guardian Name</label>
							<input type="text" class="form-control @error('guardian_name') is-invalid @enderror" name="guardian_name" placeholder="Enter guardian name" value="{{ old('guardian_name') }}" required>
							@error('guardian_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Guardian Contact Number</label>
							<input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" name="guardian_phone" placeholder="03XXXXXXXXX" value="{{ old('guardian_phone') }}" pattern="03[0-9]{9}" maxlength="11" required>
							@error('guardian_phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control @error('cnic') is-invalid @enderror" name="cnic" placeholder="13 digits without dashes" value="{{ old('cnic') }}" pattern="[0-9]{13}" maxlength="13" required>
							@error('cnic')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label label-without-required">Passport Number (Optional)</label>
							<input type="text" class="form-control @error('passport_number') is-invalid @enderror" name="passport_number" placeholder="Enter passport number" value="{{ old('passport_number') }}">
							@error('passport_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Email Address</label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter email address" value="{{ old('email', $lead->email ?? '') }}" required>
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Education</label>
							<input type="text" class="form-control @error('education') is-invalid @enderror" name="education" placeholder="Enter recent completed degree" value="{{ old('education', data_get($leadDetails, 'current_education')) }}" required>
							@error('education')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Date of Birth</label>
							<input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subDay()->toDateString() }}" required>
							@error('date_of_birth')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="col-md-6 col-lg-3 mb-lg-1 registration-gender-group">
							<label class="form-label text-dark fw-semibold registration-gender-title">
								Gender
							</label>
							<div class="row mt-2 choice-group registration-gender-options @error('gender') is-invalid @enderror">
								<div class="col-4 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 @error('gender') is-invalid @enderror"
											   id="gender-male"
											   name="gender"
											   type="radio"
											   value="male"
											   {{ old('gender', data_get($leadDetails, 'gender', 'male')) === 'male' ? 'checked' : '' }}
											   required>
										<label class="form-check-label mt-1 mb-0" for="gender-male">Male</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 @error('gender') is-invalid @enderror"
											   id="gender-female"
											   name="gender"
											   type="radio"
											   value="female"
											   {{ old('gender', data_get($leadDetails, 'gender', 'male')) === 'female' ? 'checked' : '' }}>
										<label class="form-check-label mt-1  mb-0" for="gender-female">Female</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 @error('gender') is-invalid @enderror"
											   id="gender-other"
											   name="gender"
											   type="radio"
											   value="other"
											   {{ old('gender', data_get($leadDetails, 'gender', 'male')) === 'other' ? 'checked' : '' }}>
										<label class="form-check-label mt-1 mb-0" for="gender-other">Other</label>
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
							<textarea class="form-control registration-textarea-address @error('address') is-invalid @enderror" name="address" rows="1" placeholder="Enter complete postal address..." required>{{ old('address') }}</textarea>
							@error('address')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Registration Number</label>
							<input type="text" class="form-control" id="reg-number" value="{{ $preview['registration_number'] ?? '' }}" disabled>
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Date of Registration</label>
							<input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" disabled>
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Admission/Registration Fee</label>
							<input type="number" step="0.01" class="form-control @error('fee') is-invalid @enderror" name="fee" value="2000" readonly>
							@error('fee')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Receipt Number</label>
							<input type="text" class="form-control" id="receipt-number" value="{{ $preview['receipt_number'] ?? '' }}" disabled>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-12">
							<label class="form-label label-without-required">Remarks</label>
							<textarea class="form-control registration-textarea-remarks @error('remarks') is-invalid @enderror" name="remarks" rows="2" placeholder="Remarks">{{ old('remarks') }}</textarea>
							@error('remarks')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>
					<div class="form-actions registration-actions mb-2 mt-3 text-right">
						<button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04">Register Now</button>
						<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 {{ request()->boolean('embed') ? 'embed-cancel' : '' }}">Cancel</a>
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
            --typo-registration-create-font-weight-1: 600;
        }

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
			max-height: none !important;
			border: 1px solid #e3edf7;
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
			background: #fff;
		}

		.registration-card .panel-heading {
			padding: 10px 20px;
		}

		.registration-card .panel-body {
			max-height: none !important;
			overflow: visible !important;
		}

		.registration-header {
			border-bottom: 1px solid #e6eef3;
			background: #fff;
		}

		.registration-title {
			font-size: 18px;
			font-weight: 500;
			color: #25364a;
			margin: 0;
		}

		.registration-title > span {
			font-size: 14px;
			font-weight: 400;
			color: #5f7289;
		}

		.registration-body {
			padding: 10px 10px 5px;
			overflow: visible !important;
		}

		.registration-form .required::after {
			content: ' *';
			color: #e53935;
		}

		.registration-form .form-row {
			margin-left: -10px;
			margin-right: -10px;
		}

		.registration-form .form-row > [class*="col-"] {
			padding-left: 10px;
			padding-right: 10px;
		}

		.registration-form .form-group {
			margin-bottom: 18px;
		}

		.registration-form label,
		.registration-form .form-label {
			display: block;
			min-height: 22px;
			margin-bottom: 8px;
			font-weight: var(--typo-registration-create-font-weight-1);
			color: #223a57;
		}

		.registration-form .label-without-required {
			padding-top: 0px;
		}

		.registration-form .form-control {
			min-height: 46px;
			border-radius: 12px;
			border: 1px solid #d6e2f0;
			padding: 10px 14px;
			background: #fff;
			box-shadow: none;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.registration-form textarea.form-control {
			min-height: 92px;
			resize: vertical;
		}

		.registration-form .registration-textarea-address {
			min-height: 54px !important;
			height: 54px !important;
			max-height: 54px !important;
			resize: none;
		}

		.registration-form .registration-textarea-remarks {
			min-height: 54px !important;
			height: 54px !important;
			max-height: 54px !important;
			resize: none;
		}

		.registration-form .form-control:focus {
			border-color: #14a2f6;
			box-shadow: 0 0 0 3px rgba(20, 162, 246, 0.12);
		}

		.registration-form .form-control[disabled],
		.registration-form .form-control[readonly] {
			background: #f4f8fc;
			color: #5f7289;
		}

		.registration-form .field-error {
			margin-top: 6px;
			font-size: 12px;
			color: #dc3545;
		}

		.registration-actions {
			padding: 0 10px 4px;
		}

		.training-course-select {
			width: 100%;
			min-width: 0;
			max-width: 100%;
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

		.training-course-option-label {
			font-weight: var(--typo-registration-create-font-weight-1);
			color: #183b68;
		}

		.training-course-option-value {
			color: #5f6b7a;
		}

		.choice-group.is-invalid {
			border: 1px solid #e53935;
			border-radius: 6px;
			margin-left: 0;
			margin-right: 0;
			padding: 4px 0;
		}

		.registration-form .choice-group {
			align-items: center;
			padding-top: 4px;
			padding-bottom: 2px;
		}

		.registration-form .choice-group .form-check {
			gap: 3px;
		}

		.registration-gender-title {
			font-size: 15px;
			font-weight: var(--typo-registration-create-font-weight-1);
			margin-bottom: 10px;
		}

		.registration-gender-options {
			margin-top: 0 !important;
		}

		.registration-gender-group .form-check-input[type="radio"] {
			width: 17px;
			height: 17px !important;
			border-width: 2px;
		}

		.registration-gender-group .form-check-input[type="radio"]:checked::before {
			top: 3px;
			left: 3px;
			width: 7px;
			height: 7px;
		}

		.form-check-input[type="radio"] {
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
			background-color: #fff;
			transition: background 0.2s, box-shadow 0.2s;
		}

		.form-check-input[type="radio"]:checked {
			border-color: #00a8ff;
		}

		.form-check-input[type="radio"]:checked::before {
			content: '';
			position: absolute;
			top: 2px;
			left: 2px;
			width: 7px;
			height: 7px;
			border-radius: 50%;
			background-color: #00a8ff;
		}

		.registration-gender-group .form-check-label {
			font-size: 15px !important;
			margin-bottom: 0;
			cursor: pointer;
			font-weight: var(--typo-registration-create-font-weight-1);
			color: #223a57;
			line-height: 1.2;
		}

		.registration-form hr {
			margin: 8px 0 22px;
			border-top: 1px solid #e8eef5;
		}

		.embed-actions {
			position: sticky;
			bottom: 0;
			z-index: 2;
			display: flex;
			justify-content: flex-end;
			gap: 12px;
			padding-top: 18px;
			margin-top: 6px;
			/* border-top: 1px solid #e8eef5; */
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.68) 0%, #fff 28%);
		}
/*
		.embed-actions .btn {
			min-width: 160px;
			height: 44px;
		 border-radius: 12px;
			font-weight: 700;
		}

		.embed-actions .btn-primary {

		}

		.embed-actions .btn-primary:hover,
		.embed-actions .btn-primary:focus {
			color: #fff !important;
			background: linear-gradient(120deg, #0088dd, #0ea4ef);
			border-color: transparent;
		} */

		/* .embed-actions .btn-outline-danger {
			color: #d64545 !important;
			border: 1px solid rgba(214, 69, 69, 0.32);
			background: #fff;
		} */

		/* .embed-actions .btn-outline-danger:hover,
		.embed-actions .btn-outline-danger:focus {
			color: #fff !important;
			background: #dc3545;
			border-color: #dc3545;
		} */

		@media (max-width: 767px) {
			.registration-card .panel-heading {
				padding: 10px 14px;
			}

			.registration-body {
				padding: 10px 8px 4px;
			}

			.embed-actions {
				flex-direction: column-reverse;
			}
/*
			.embed-actions .btn {
				width: 100%;
			} */
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
			function escapeHtml(value) {
				return String(value ?? '')
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#039;');
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
						'<span class="training-course-option-line"><span class="training-course-option-label">' + escapeHtml(title) + '</span></span>' +
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
	<script>
		(function () {
			var previewUrl = @json(route('registration.preview'));

			function bind() {
				var $campus = jQuery('select[name="campus_id"]');
				var $reg = jQuery('#reg-number');
				var $receipt = jQuery('#receipt-number');

				if (!$campus.length) {
					console.warn('campus_id select not found');
					return;
				}

				function updateNumbers() {
					var val = $campus.val();
					if (!val) {
						$reg.val('');
						$receipt.val('');
						return;
					}
					jQuery.ajax({
						url: previewUrl,
						type: 'GET',
						dataType: 'json',
						data: { campus_id: val },
						headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
					}).done(function (data) {
						$reg.val((data && data.registration_number) || '');
						$receipt.val((data && data.receipt_number) || '');
					}).fail(function (xhr) {
						console.error('Preview fetch failed:', xhr.status, xhr.responseText);
						$reg.val('');
						$receipt.val('');
					});
				}

				$campus.off('change.regPreview').on('change.regPreview', updateNumbers);
				updateNumbers();
			}

			if (window.jQuery) {
				jQuery(function () { bind(); });
			} else {
				document.addEventListener('DOMContentLoaded', function () {
					if (window.jQuery) bind();
					else console.error('jQuery not loaded — registration preview disabled');
				});
			}
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
							? (formGroup ? formGroup.querySelector('.form-group-radios') : field)
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
				var form = document.getElementById('registration-form');
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
							throw new Error(data.message || 'Unable to save the registration right now.');
						}

						if (window.parent) {
							window.parent.postMessage({
								type: 'lead-modal-close',
								reload: true,
								status: data.status || 'Registration created successfully.'
							}, '*');
						}
					} catch (error) {
						if (window.swal) {
							swal({
								title: 'Error',
								text: error.message || 'Unable to save the registration right now.',
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
