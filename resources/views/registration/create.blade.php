@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.theme')

@section('title', 'Create New Registration')

@section('content')
	<div class="registration-shell">
		<div class="registration-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="panel-title">Create New Registration <small class="">(All fields marked with * are required)</small></h3>
				<hr>
				
				<form method="POST" action="{{ route('registration.store') }}" id="registration-form" class="registration-form">
					@csrf
					@if(request()->boolean('embed'))
						<input type="hidden" name="embed" value="1">
					@endif
					@if(!empty($lead))
						<input type="hidden" name="lead_id" value="{{ $lead->id }}">
					@endif
					<div class="form-row">
						<!-- <div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Select Campus</label>
							<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ old('campus_id', $lead->campus_id ?? null) == $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div> -->
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Full Name (As Per CNIC)</label>
							<input type="text" class="form-control @error('student_name') is-invalid @enderror" name="student_name" placeholder="Enter full name" value="{{ old('student_name', $lead->name ?? '') }}" required>
							@error('student_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Primary Contact Number</label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" placeholder="03XXXXXXXXX" value="{{ old('phone', $lead->phone ?? '') }}" required>
							@error('phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Guardian Name</label>
							<input type="text" class="form-control" placeholder="Enter guardian name">
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Guardian Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control" placeholder="Numbers only">
						</div>
						<!-- <div class="form-group col-md-6 col-lg-3">
							<label class="form-label label-without-required">Passport Number (Optional)</label>
							<input type="text" class="form-control" placeholder="Enter passport number">
						</div> -->
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Email Address</label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter email address" value="{{ old('email', $lead->email ?? '') }}">
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Education</label>
							<input type="text" class="form-control" placeholder="Enter recent completed degree">
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Date of Birth</label>
							<input type="date" class="form-control" placeholder="dd/mm/yyyy">
						</div>
					</div>

					<div class="form-row">
						
						
						
						<!-- <div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Country</label>
							<select class="form-control" id="reg-country-select"></select>
						</div> -->
						<div class="form-group col-md-6 col-lg-3 mb-lg-1">
							<label class="form-label required">
								Gender
							</label>
							<div class="row mt-2 choice-group">
								<div class="col-4 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											   id="signup_v2-gender-male"
											   name="signup_v2[gender]"
											   data-validation="[NOTEMPTY]"
											   data-validation-group="signup_v2-gender"
											   data-validation-message="You must select a gender"
											   type="radio"
											   value="male"
											   {{ old('signup_v2.gender') === 'male' ? 'checked' : '' }}>
										<label class="form-label small mb-0" for="signup_v2-gender-male">Male</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 mr-1"
											   id="signup_v2-gender-female"
											   name="signup_v2[gender]"
											   data-validation-group="signup_v2-gender"
											   type="radio"
											   value="female"
											   {{ old('signup_v2.gender') === 'female' ? 'checked' : '' }}>
										<label class="form-label small mb-0" for="signup_v2-gender-female">Female</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 mr-1"
											   id="signup_v2-gender-other"
											   name="signup_v2[gender]"
											   data-validation-group="signup_v2-gender"
											   type="radio"
											   value="other"
											   {{ old('signup_v2.gender') === 'other' ? 'checked' : '' }}>
										<label class="form-label small mb-0" for="signup_v2-gender-other">Other</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12">
						<label class="form-label required">Postal Address</label>
						<textarea class="form-control" rows="2" placeholder="Enter complete postal address..."></textarea>
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

						<!-- <div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">City</label>
							<select class="form-control" id="reg-city-select">
								<option>Loading...</option>
							</select>
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Area</label>
							<input type="text" class="form-control" placeholder="Enter area">
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Personal Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label required">Program</label>
							<select class="form-control @error('program_id') is-invalid @enderror" name="program_id" required>
								<option value="">- Select -</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}" {{ old('program_id', $lead->program_id ?? null) == $program->id ? 'selected' : '' }}>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
							@error('program_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div> -->
					<!-- </div> -->

					

					<!-- <hr> -->

				
					<!-- <div class="form-row"> -->
						
						<!-- <div class="form-group col-md-6 col-lg-3">
							<label class="form-label label-without-required">Mode / Shift</label>
							<input type="text" class="form-control" value="Aligned with lead selection" disabled>
						</div> -->
						
						<!-- <div class="form-group col-md-6 col-lg-3">
							<label class="form-label label-without-required">Discount</label>
							<input type="number" step="0.01" class="form-control @error('discount') is-invalid @enderror" name="discount" value="0" readonly>
							@error('discount')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6 col-lg-3">
							<label class="form-label label-without-required">Net Payable (After Discount)</label>
							<input type="text" class="form-control" id="net-payable" value="2000" readonly>
						</div> -->
					<!-- </div> -->

					<!-- <hr>

					<div class="form-row">
						
						
					</div> -->
					<!-- <div class="form-row">
					
					</div> -->
					<div class="form-row">
						<div class="form-group col-12">
							<label class="form-label label-without-required">Remarks</label>
							<textarea class="form-control" rows="4" style="height:7rem !Important;" placeholder="Remarks"></textarea>
						</div>
					</div>
					<div  class="form-actions mb-2 mt-3 text-right">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline " style="padding: 0.4rem;"> Register Now</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; ">Cancel</a>
						</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.registration-shell {
			/* padding: 18px 0 24px; */
		}

		.registration-card {
			border: 1px solid #e3edf7;
			/* border-radius: 24px; */
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
			overflow: hidden;
			background: #fff;
		}

		.registration-card .card-body {
			padding: 28px 30px 24px;
		}

		.reg-title {
			margin: 0 0 24px;
			font-size: 33px;
			font-weight: 800;
			line-height: 1.15;
			color: #183b68;
		}

		.reg-title small {
			font-size: 18px;
			font-weight: 500;
			color: #70839a !important;
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
			font-weight: 600;
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

		.registration-form .form-group-radios {
			min-height: 100%;
			padding: 14px 16px;
			border: 1px solid #d6e2f0;
			border-radius: 16px;
			background: #fbfdff;
		}

		.registration-form .form-group-radios .form-label {
			margin-bottom: 12px;
		}

		.registration-form .form-group-radios .radio {
			display: flex;
			align-items: center;
			margin-top: 10px;
		}

		.registration-form .form-group-radios .radio:first-of-type {
			margin-top: 0;
		}

		.registration-form .form-group-radios input[type="radio"] {
			margin-right: 8px;
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

		@media (max-width: 991px) {
			.reg-title {
				font-size: 28px;
			}

			.reg-title small {
				display: block;
				margin-top: 8px;
			}
		}

		@media (max-width: 767px) {
			.registration-card .card-body {
				padding: 20px 18px 18px;
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
			/* padding: 18px; */
		}

		.registration-card {
			border-radius: 20px;
			box-shadow: none;
		}

		.registration-card .card-body {
			padding: 22px 22px 18px;
		}
@endif
	</style>
@endpush

@push('scripts')
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			CountryCityLoader.init('reg-country-select', 'reg-city-select', {
				country: @json(old('country', 'Pakistan')),
				city: @json(old('city', 'Faisalabad'))
			});
		});
	</script>
	<script>
		(function() {
			var campusSelect = document.querySelector('select[name="campus_id"]');
			var regField = document.getElementById('reg-number');
			var receiptField = document.getElementById('receipt-number');
			var netField = document.getElementById('net-payable');

			function updateNumbers() {
				if (!campusSelect || !campusSelect.value) return;
				fetch('{{ route('registration.preview') }}?campus_id=' + campusSelect.value)
					.then(function (res) { return res.json(); })
					.then(function (data) {
						if (regField && data.registration_number) regField.value = data.registration_number;
						if (receiptField && data.receipt_number) receiptField.value = data.receipt_number;
					})
					.catch(function () {});
			}

			if (campusSelect) {
				campusSelect.addEventListener('change', updateNumbers);
				updateNumbers();
			}

			if (netField) {
				netField.value = '2000';
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
