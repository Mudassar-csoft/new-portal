@extends('layouts.theme')

@section('title', 'Create New Registration')

@section('content')
	<div class="registration-shell">
		<div class="registration-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="reg-title">Create New Registration <small class="text-muted">(All fields marked with * are required)</small></h3>
				<hr>
				<form id="registrationForm">
					
					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Full Name (As Per CNIC)</label>
							<input type="text" name="full_name" class="form-control" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Primary Contact Number</label>
							<input type="text" name="primary_contact" class="form-control" pattern="03[0-9]{9}" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Guardian Name</label>
							<input type="text" name="guardian_name" class="form-control" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Guardian Contact Number</label>
							<input type="text" name="guardian_contact" class="form-control" pattern="03[0-9]{9}" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">National Identity Card (CNIC)</label>
							<input type="text" name="cnic" class="form-control" pattern="[0-9]{13}" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label>Passport Number (Optional)</label>
							<input type="text" name="passport" class="form-control">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Email Address</label>
							<input type="email" name="email" class="form-control" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Date of Birth</label>
							<input type="text" name="dob" class="form-control" pattern="\d{2}/\d{2}/\d{4}" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="form-label text-dark fw-semibold small">
								Gender
							</label>
							<div class="row mt-2 choice-group">
								<div class="col-4 d-flex justify-content-center mb-1">
									<div class="form-check d-flex align-items-center mt-0">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="registration-gender-male"
											name="gender"
											value="male"
											required>
										<label class="form-check-label small mb-0" for="registration-gender-male">
											Male
										</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="registration-gender-female"
											name="gender"
											value="female">
										<label class="form-check-label small mb-0" for="registration-gender-female">
											Female
										</label>
									</div>
								</div>
								<div class="col-4 d-flex justify-content-center">
									<div class="form-check d-flex align-items-center">
										<input class="form-check-input mt-0 mr-1"
											type="radio"
											id="registration-gender-other"
											name="gender"
											value="other">
										<label class="form-check-label small mb-0" for="registration-gender-other">
											Other
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Current Education Level</label>
							<input type="text" name="education" class="form-control" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Country</label>
							<input type="text" name="country" class="form-control" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">City</label>
							<input type="text" name="city" class="form-control" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Area</label>
							<input type="text" name="area" class="form-control" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Personal Contact Number</label>
							<input type="text" name="personal_contact" class="form-control" pattern="03[0-9]{9}" required>
						</div>
					</div>

					<div class="form-group">
						<label class="required">Postal Address</label>
						<textarea name="address" class="form-control" rows="2" required></textarea>
					</div>

					<hr>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Registration Number</label>
							<input type="text" name="reg_no" class="form-control" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Date of Registration</label>
							<input type="text" name="reg_date" class="form-control" pattern="\d{2}/\d{2}/\d{4}" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Registration Fee</label>
							<input type="number" name="fee" class="form-control" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Receipt Number</label>
							<input type="text" name="receipt" class="form-control" required>
						</div>
					</div>

					<div class="form-group">
						<label class="required">Remarks</label>
						<textarea name="remarks" class="form-control" rows="2" required></textarea>
					</div>

					<div class="text-right">
						<button type="submit" class="btn btn-primary">Register Now</button>
						<button type="button" class="btn btn-outline-danger ml-2">Cancel</button>
					</div>

				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
<style>
        :root {
            --dimension-lead-registration-1: 7px;
            --color-lead-registration-1: #00a8ff;
            --color-lead-registration-2: #e53935;
        }


	.registration-shell { padding: 8px 0 16px; }
	.registration-card {
		border: 1px solid #e6ecf2;
		border-radius: 8px;
		box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
	}
	.reg-title { margin-bottom: 16px; font-weight: 700; color: #2f3b52; }
	.required::after { content: ' *'; color: var(--color-lead-registration-2); }

	/* validation styles */
	.is-invalid { border-color: var(--color-lead-registration-2); }
	.choice-group.is-invalid {
		border: 1px solid var(--color-lead-registration-2);
		border-radius: 6px;
		margin-left: 0;
		margin-right: 0;
		padding: 4px 0;
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
		border-color: var(--color-lead-registration-1);
	}
	.form-check-input[type="radio"]:checked::before {
		content: '';
		position: absolute;
		top: 2px;
		left: 2px;
		width: var(--dimension-lead-registration-1);
		height: var(--dimension-lead-registration-1);
		border-radius: 50%;
		background-color: var(--color-lead-registration-1);
	}
	.form-check-label {
		font-size: .75rem;
		margin-bottom: 0;
		cursor: pointer;
	}
</style>
@endpush
@push('scripts')
<script>
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    let isValid = true;

    // Helper functions to set and clear error styles
    function addError(element) {
        element.classList.add('is-invalid');
        isValid = false;
    }

    function removeError(element) {
        element.classList.remove('is-invalid');
    }

    // Validate required text fields
    const requiredFields = [
        'full_name', 'guardian_name', 'education', 'country', 'city', 'area', 'address', 'remarks', 'reg_no', 'receipt'
    ];

    requiredFields.forEach(fieldName => {
        const field = form[fieldName];
        removeError(field);
        if (!field.value.trim()) {
            addError(field);
        }
    });

    // Validate phone numbers (must start with '03' and followed by 9 digits)
    ['primary_contact', 'guardian_contact', 'personal_contact'].forEach(name => {
        const field = form[name];
        removeError(field);
        if (!/^03\d{9}$/.test(field.value.trim())) {
            addError(field);
        }
    });

    // Validate CNIC (must be exactly 13 digits)
    const cnicField = form['cnic'];
    removeError(cnicField);
    if (!/^\d{13}$/.test(cnicField.value.trim())) {
        addError(cnicField);
    }

    // Validate email address
    const emailField = form['email'];
    removeError(emailField);
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailField.value.trim())) {
        addError(emailField);
    }

    // Validate date fields (DOB and Registration Date)
    function isValidDate(dateString) {
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) return false;
        const [day, month, year] = dateString.split('/').map(Number);
        const dateObj = new Date(year, month - 1, day);
        return dateObj.getFullYear() === year && 
               dateObj.getMonth() === month - 1 && 
               dateObj.getDate() === day;
    }

    ['dob', 'reg_date'].forEach(dateFieldName => {
        const dateField = form[dateFieldName];
        removeError(dateField);
        if (!isValidDate(dateField.value.trim())) {
            addError(dateField);
        }
    });

    // Validate registration fee (positive number)
    const feeField = form['fee'];
    removeError(feeField);
    const feeValue = feeField.value.trim();
    if (!/^\d+$/.test(feeValue) || parseInt(feeValue, 10) <= 0) {
        addError(feeField);
    }

    // Validate gender radio button
    const genderSelected = form.querySelector('input[name="gender"]:checked');
    if (!genderSelected) {
        alert('Please select a gender.');
        isValid = false;
    }

    // Validate optional passport (if filled, minimum 5 characters)
    const passportField = form['passport'];
    removeError(passportField);
    const passportValue = passportField.value.trim();
    if (passportValue && passportValue.length < 5) {
        addError(passportField);
    }

    // Final check before submitting
    if (isValid) {
        // All validations passed; submit the form
        form.submit();
    } else {
        alert('Please correct the errors in the form.');
    }
});

</script>
@endpush
