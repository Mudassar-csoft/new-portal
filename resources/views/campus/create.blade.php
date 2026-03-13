@extends('layouts.theme')

@section('title', 'Create New Campus')

@section('content')
    <div class="campus-shell">
        <div class="campus-card box-typical box-typical-dashboard panel panel-default">
            <div class="card-body">
                <h3 class="campus-title form-label" style="font-size:12px !important; color:black;">Create New Campus <small class="text-muted">(All fields are required)</small></h3>
                <form method="POST" action="{{ route('campus.store') }}">
                    @csrf
                    <div class="form-row mt-2" >
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Campus Title</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter campus title" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Campus Code</label>
                            <input type="text" id="campus-code" class="form-control" placeholder="Auto generated on save" disabled>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Country</label>
                            <select class="form-control" id="country-select" name="country" required></select>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">City</label>
                            <select class="form-control" id="city-select" name="city" required>
                                <option>Loading...</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row mt-2" >
                        
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">City Abbreviation</label>
                            <input type="text" name="city_abbr" class="form-control" placeholder="Enter three letter abbreviation" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Campus Email Address</label>
                            <input type="email" name="campus_email" class="form-control" placeholder="Enter campus email address" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Campus Landline Number</label>
                            <input type="text" name="landline" class="form-control" placeholder="##-#######" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Campus Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="0300-#######" required>
                        </div>
                    </div>
                    
                    <div class="form-row mt-2" >
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required d-block">Campus Type</label>
                            <div class="campus-type-options d-flex" style= "gap:8px; padding-left:15px !important;" >
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                <input class="form-check-input mt-0 me-2 mr-1 ml-1" type="radio" name="campus_type" value="company" checked>
                                <label class="mt-1"> Company Owned</label>
                                </div>
                                <div class=" d-flex align-items-center mt-2">
                                <input class="form-check-input mt-0 me-2 mr-1 ml-1" type="radio" name="campus_type" value="franchise">
                                <label class=" mt-1"> Franchise</label>
                                </div>
                            </div>
                        </div>
                          <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Number of Labs in Campus</label>
                            <input type="number" name="labs_count" class="form-control" placeholder="Enter number of labs" min="0">
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Royalty Rate</label>
                            <input type="number" step="0.01" name="royalty_rate" id="royalty-rate" class="form-control" placeholder="Enter royalty rate">
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="">- Select -</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-2 ml-4" >
                        <label class="form-label required">Campus Address</label>
                        <textarea class="form-control"style= "width:99.5%;" name="address" rows="2" placeholder="Enter campus address here..." required></textarea>
                    </div>

                    <div class="form-row mt-2 ">
                      
                    </div>


                    <div class="row mt-2 ml-3" >
    <div class="col-12 ">
        <label class="form-label small fw-semibold text-dark required">
            Remarks
        </label>
       <textarea name="details[remarks]"
    class="form-control form-control-sm @error('details.remarks') is-invalid @enderror"
    rows="3"
    style="height:7.5rem !important;padding:10px; width:99.5%;"
    placeholder="Enter Your Remarks Here" style= "padding:10px">{{ old('details.remarks', '') }}</textarea>
        @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

                    <div class="form-actions text-right mt-2">
                        <button type="submit" class="btn btn-inline btn-primary-outline" pt-2>Save</button>
                        <a href="{{ route('campus.index') }}" class="btn btn-inline btn-danger-outline pt-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        *{
             font-family: 'Proxima Nova', sans-serif !important;
    font-size: 12px !important; 
    margin: 0;
    padding: 0;
        }
        #campus-code {
            font-size:12px !important;
        }
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
            margin-right: 3px;
        }
        .form-label {
    font-size: 12px !important;
    font-weight: 600 ;
    text-transform: uppercase;
    margin-bottom: 3px;
    
}
     
.select2-container--arrow .select2-selection--single .select2-selection__rendered, .select2-container--default .select2-selection--single .select2-selection__rendered, .select2-container--white .select2-selection--single .select2-selection__rendered

 {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 12px !important;
    line-height: 2 !important;
    color: #343434;
    padding: .300rem 25px .300rem 1rem;
    height: 32px !important;
    background: #fff;
}
/* ---------- Input, Select, Textarea ---------- */
input.form-control-sm, span#select2-lead-city-select-container .elect2-selection__rendered span
select.form-select-sm, option textarea.form-control-sm {
    font-size: 12px !important;
    padding: 6px 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    height: 32px;
    
    color:#ccc;
}

.form-control {
    height:32px !important;
}
/* ---------- Radio Buttons ---------- */
.form-check-input {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border: 2px solid grey;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
    position: relative;
    background-color: #fff;
    transition: background 0.2s, box-shadow 0.2s;
}

.form-check-input:checked {
    border-color: #00a8ff !important;
}

.form-check-input:checked::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #00a8ff !important;
}


/* ---------- Row & Column Spacing ---------- */
.row.g-3 > [class*="col-"] {
    display: flex;
    flex-direction: column;
    padding: 0;
}

*/
.col-3.d-flex.justify-content-center,
.col-6.d-flex.justify-content-center {
    justify-content: start; 
}

/* Small tweak for slider label */
input[name="details[probability]"] + .small {
    margin-top: 0px;
    font-size: 12px;
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
