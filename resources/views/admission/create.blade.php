@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.theme')

@section('title', 'Create New Admission')

@section('content')
	<div class="admission-shell">
		<div class="admission-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="panel-title">Create New Admission <small class="">(All fields marked with * are required)</small></h3>
			<hr>	
				<form method="POST" action="{{ route('admission.store') }}" id="admission-form" class="admission-form">
					@csrf
					@if(request()->boolean('embed'))
						<input type="hidden" name="embed" value="1">
					@endif
					@if(!empty($lead))
						<input type="hidden" name="lead_id" value="{{ $lead->id }}">
					@endif
					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Select Campus</label>
							<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ old('campus_id', $lead->campus_id ?? '') == $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Select Program</label>
							<select class="form-control @error('program_id') is-invalid @enderror" name="program_id" required>
								<option value="">- Select -</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}" {{ old('program_id', $lead->program_id ?? '') == $program->id ? 'selected' : '' }}>
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
									<option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
										{{ $batch->name ?? $batch->code }}
									</option>
								@endforeach
							</select>
							@error('batch_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Student Name (As per CNIC)</label>
							<input type="text" class="form-control @error('student_name') is-invalid @enderror" name="student_name" value="{{ old('student_name', $lead->name ?? '') }}" placeholder="Enter full name" required>
							@error('student_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Primary Contact Number</label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $lead->phone ?? '') }}" placeholder="03XXXXXXXXX" required>
							@error('phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Guardian Name</label>
							<input type="text" class="form-control @error('guardian_name') is-invalid @enderror" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Enter guardian name" required>
							@error('guardian_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Guardian Contact Number</label>
							<input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="03XXXXXXXXX" required>
							@error('guardian_phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control @error('cnic') is-invalid @enderror" name="cnic" value="{{ old('cnic') }}" placeholder="Numbers only" required>
							@error('cnic')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label">Email Address</label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $lead->email ?? '') }}" placeholder="Enter email address" required>
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Education</label>
							<input type="text" class="form-control @error('education') is-invalid @enderror" name="education" value="{{ old('education') }}" placeholder="Enter education" required>
							@error('education')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Date of Birth</label>
							<input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
							@error('date_of_birth')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						
						<div class="form-group col-md-3">
							<label class="form-label required">Gender</label>
							<div class="row mt-2">
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-gender-male"
											name="gender"
											value="male"
											@checked(old('gender', 'male') === 'male')>
										<label class="form-label small mb-0" for="admission-gender-male">
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
											@checked(old('gender') === 'female')>
										<label class="form-label small mb-0" for="admission-gender-female">
											Female
										</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-gender-other"
											name="gender"
											value="other"
											@checked(old('gender') === 'other')>
										<label class="form-label small mb-0" for="admission-gender-other">
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
						<textarea class="form-control @error('postal_address') is-invalid @enderror" name="postal_address" rows="2" placeholder="Enter complete postal address..." required>{{ old('postal_address') }}</textarea>
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
							<input type="text" class="form-control @error('registration_number') is-invalid @enderror" name="registration_number" value="{{ old('registration_number') }}" placeholder="Enter registration number" required>
							@error('registration_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Roll Number</label>
							<input type="text" class="form-control @error('roll_number') is-invalid @enderror" name="roll_number" value="{{ old('roll_number') }}" placeholder="Enter roll number" required>
							@error('roll_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Date of Admission</label>
							<input type="date" class="form-control @error('admission_date') is-invalid @enderror" name="admission_date" value="{{ old('admission_date') }}" required>
							@error('admission_date')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Fee Package</label>
							<input type="number" step="0.01" class="form-control @error('fee_package') is-invalid @enderror" name="fee_package" value="{{ old('fee_package') }}" required>
							@error('fee_package')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="form-label required">Discount Amount</label>
							<input type="number" step="0.01" class="form-control @error('discount_amount') is-invalid @enderror" name="discount_amount" value="{{ old('discount_amount') }}" required>
							@error('discount_amount')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Discount %</label>
							<input type="number" step="0.01" class="form-control @error('discount_percent') is-invalid @enderror" name="discount_percent" value="{{ old('discount_percent') }}" required>
							@error('discount_percent')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Discounted Fee</label>
							<input type="number" step="0.01" class="form-control @error('discounted_fee') is-invalid @enderror" name="discounted_fee" value="{{ old('discounted_fee') }}" required>
							@error('discounted_fee')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="form-label required">Fee Type</label>
							<div class="row mt-2  @error('fee_type') is-invalid @enderror">
								<div class="col-6 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="admission-fee-type-full"
											name="fee_type"
											value="full"
											@checked(old('fee_type', 'full') === 'full')>
										<label class="form-label small mb-0" for="admission-fee-type-full">
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
										<label class="form-label small mb-0" for="admission-fee-type-installments">
											Installments
										</label>
									</div>
								</div>
							</div>
							@error('fee_type')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-12">
							<label class="form-label required">Remarks</label>
							<textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" rows="2" placeholder="Remarks" required>{{ old('remarks') }}</textarea>
							@error('remarks')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<!-- <div class="form-group">
						<label class="form-label required">Receipt Number</label>
						<input type="text" class="form-control @error('receipt_number') is-invalid @enderror" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Enter receipt number" required>
						@error('receipt_number')
							<div class="field-error">{{ $message }}</div>
						@enderror
					</div> -->

						<div  class="form-actions mb-2 mt-3 text-right">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline " style="padding: 0.4rem;"> Admission Now</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; ">Cancel</a>
						</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.admission-shell {
			/* padding: 18px 0 24px; */
		}

		.admission-card {
			border: 1px solid #e3edf7;
			border-radius: 5px;
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
			overflow: hidden;
			background: #fff;
		}

		.admission-card .card-body {
			padding: 28px 30px 24px;
		}

		.adm-title {
			margin: 0 0 24px;
			font-size: 33px;
			font-weight: 800;
			line-height: 1.15;
			color: #183b68;
		}

		.adm-title small {
			font-size: 18px;
			font-weight: 500;
			color: #70839a !important;
		}

		.required::after {
			content: ' *';
			color: #e53935;
		}

		.admission-form .form-row {
			margin-left: -10px;
			margin-right: -10px;
		}

		.admission-form .form-row > [class*="col-"] {
			padding-left: 10px;
			padding-right: 10px;
		}

		.admission-form .form-group {
			margin-bottom: 18px;
		}

		.admission-form label,
		.admission-form .form-label {
			display: inline-block;
			margin-bottom: 8px;
			font-weight: 700;
			color: #223a57;
		}

		.admission-form .form-control {
			min-height: 46px;
			border-radius: 12px;
			border: 1px solid #d6e2f0;
			padding: 10px 14px;
			background: #fff;
			box-shadow: none;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.admission-form textarea.form-control {
			min-height: 92px;
			resize: vertical;
		}

		.admission-form .form-control:focus {
			border-color: #14a2f6;
			box-shadow: 0 0 0 3px rgba(20, 162, 246, 0.12);
		}

		.admission-form .form-control[disabled],
		.admission-form .form-control[readonly] {
			background: #f4f8fc;
			color: #5f7289;
		}

		.admission-form .field-error {
			margin-top: 6px;
			font-size: 12px;
			color: #dc3545;
		}

		.admission-form .choice-group {
			margin-left: 0;
			margin-right: 0;
			padding: 11px 14px;
			border: 1px solid #d6e2f0;
			border-radius: 12px;
			background: #fbfdff;
			min-height: 46px;
		}

		.admission-form .choice-group.is-invalid {
			border-color: #dc3545;
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
			background-color: #fff;
			transition: background 0.2s, box-shadow 0.2s;
		}

		.admission-form .form-check-input[type="radio"]:checked {
			border-color: #00a8ff;
		}

		.admission-form .form-check-input[type="radio"]:checked::before {
			content: '';
			position: absolute;
			top: 2px;
			left: 2px;
			width: 6px;
			height: 6px;
			border-radius: 50%;
			background-color: #00a8ff;
		}

		.admission-form .form-check-label {
			font-size: 0.75rem;
			margin-bottom: 0;
			cursor: pointer;
			color: #42556d;
			font-weight: 600;
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
			border-top: 1px solid #e8eef5;
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.68) 0%, #fff 28%);
		}

		.embed-actions .btn {
			min-width: 160px;
			height: 44px;
			border-radius: 12px;
			font-weight: 700;
		}

		.embed-actions .btn-primary {
			color: #fff !important;
			background: linear-gradient(120deg, #0099f8, #17b3ff);
			border-color: transparent;
			box-shadow: 0 14px 28px rgba(0, 153, 248, 0.24);
		}

		.embed-actions .btn-primary:hover,
		.embed-actions .btn-primary:focus {
			color: #fff !important;
			background: linear-gradient(120deg, #0088dd, #0ea4ef);
			border-color: transparent;
		}

		.embed-actions .btn-outline-danger {
			color: #d64545 !important;
			border: 1px solid rgba(214, 69, 69, 0.32);
			background: #fff;
		}

		.embed-actions .btn-outline-danger:hover,
		.embed-actions .btn-outline-danger:focus {
			color: #fff !important;
			background: #dc3545;
			border-color: #dc3545;
		}

		@media (max-width: 991px) {
			.adm-title {
				font-size: 28px;
			}

			.adm-title small {
				display: block;
				margin-top: 8px;
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
				width: 100%;
			}
		}

@if(request()->boolean('embed'))
		.admission-shell {
			padding: 18px;
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
