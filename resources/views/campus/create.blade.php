@extends('layouts.theme')

@section('title', 'Create New Campus')

@section('content')
    <div class="campus-shell">
        <div class="campus-card box-typical box-typical-dashboard panel panel-default">
            <div class="card-body">
                <h3 class="campus-title">Create New Campus <small class="text-muted">(All fields are required)</small></h3>
                <form method="POST" action="{{ route('campus.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="required">Campus Title</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter campus title" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Campus Code</label>
                            <input type="text" id="campus-code" class="form-control" placeholder="Auto generated on save" disabled>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Country</label>
                            <select class="form-control" id="country-select" name="country" required></select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="required">City</label>
                            <select class="form-control" id="city-select" name="city" required>
                                <option>Loading...</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">City Abbreviation</label>
                            <input type="text" name="city_abbr" class="form-control" placeholder="Enter three letter abbreviation" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Campus Email Address</label>
                            <input type="email" name="campus_email" class="form-control" placeholder="Enter campus email address" required>
                        </div>
                    </div>
                    <div class="form-row align-items-center">
                        <div class="form-group col-md-4">
                            <label class="required d-block">Campus Type</label>
                            <div class="campus-type-options">
                                <label class="mr-3"><input type="radio" name="campus_type" value="company" checked> Company Owned</label>
                                <label><input type="radio" name="campus_type" value="franchise"> Franchise</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="required">Campus Landline Number</label>
                            <input type="text" name="landline" class="form-control" placeholder="##-#######" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Campus Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="0300-#######" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="required">Campus Address</label>
                        <textarea class="form-control" name="address" rows="2" placeholder="Enter campus address here..." required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="required">Number of Labs in Campus</label>
                            <input type="number" name="labs_count" class="form-control" placeholder="Enter number of labs" min="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Royalty Rate</label>
                            <input type="number" step="0.01" name="royalty_rate" id="royalty-rate" class="form-control" placeholder="Enter royalty rate">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="">- Select -</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="required">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Enter remarks"></textarea>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Save</button>
                        <a href="{{ route('campus.index') }}" class="btn btn-inline btn-secondary-outline btn-outline-danger">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .campus-shell {
            padding: 8px 0 16px;
        }

        .campus-card {
            border: 1px solid #e6ecf2;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
        }

        .campus-title {
            margin-bottom: 16px;
            font-weight: 700;
            color: #2f3b52;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .campus-type-options input {
            margin-right: 6px;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.country_city_script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            CountryCityLoader.init('country-select', 'city-select', {
                country: 'Pakistan',
                city: 'Faisalabad'
            });

            const abbrInput = document.querySelector('input[name="city_abbr"]');
            const codeField = document.getElementById('campus-code');
			async function fetchExistingCount(abbr) {
				try {
					const res = await fetch(`/campus/count/${abbr}`);
					if (!res.ok) return null;
					const data = await res.json();
					return data.count ?? null;
				} catch (e) {
					return null;
				}
			}

			function updateCodePreview() {
				if (!abbrInput || !codeField) return;
				const abbr = (abbrInput.value || '').toUpperCase().replace(/[^A-Z]/g, '').slice(0, 5);
				if (!abbr) {
					codeField.value = 'Auto generated on save';
					return;
				}
				fetchExistingCount(abbr).then(count => {
					const next = String((count ?? 0) + 1).padStart(2, '0');
					codeField.value = `CI${abbr}${next}`;
				});
			}
			if (abbrInput) {
				abbrInput.addEventListener('input', updateCodePreview);
				updateCodePreview();
			}
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const royalty = document.getElementById('royalty-rate');
            const typeInputs = document.querySelectorAll('input[name="campus_type"]');

            function toggleRoyalty() {
                const isFranchise = Array.from(typeInputs).some(r => r.checked && r.value === 'franchise');
                if (!royalty) return;
                royalty.disabled = !isFranchise;
                if (!isFranchise) {
                    royalty.value = '';
                }
            }

            typeInputs.forEach(input => input.addEventListener('change', toggleRoyalty));
            toggleRoyalty();
        });
    </script>
@endpush
