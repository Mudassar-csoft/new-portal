@php
    $coworkingGender = ($selectedLeadType ?? 'training') === 'coworking'
        ? old('details.gender', data_get($leadPrefill, 'details.gender', 'male'))
        : null;
@endphp
<div id="lead-form-coworking" class="lead-form active" data-type="coworking">
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Full Name (As Per CNIC)</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Full Name" value="{{ old('name') }}">
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Primary Contact Number</label>
            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="03000000000" value="{{ old('phone') }}">
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Email Adress</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Email" value="{{ old('email') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Space Type</label>
            <select name="details[space_required]" class="form-control @error('details.space_required') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach(['Dedicated Desk', 'Shared Office', 'Private Office', 'Studio Space', 'Meeting Room', 'Event Hall', 'Virtual Office'] as $space)
                    <option value="{{ $space }}" @selected(old('details.space_required') === $space)>{{ $space }}</option>
                @endforeach
            </select>
            @error('details.space_required')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        
    </div>
    <!-- <div class="form-row">
         <div class="col-md-6 col-lg-3 mb-lg-1">
            <label class="form-label text-dark fw-semibold small ">
                Gender
            </label>
             <div class="row mt-2 choice-group @error('details.gender') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center mt-0">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="coworking-gender-male"
                            name="details[gender]"
                            value="male"
                            @checked($coworkingGender === 'male')>
                        <label class="form-check-label small mb-0"
                            for="coworking-gender-male">
                            Male
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="coworking-gender-female"
                            name="details[gender]"
                            value="female"
                            @checked($coworkingGender === 'female')>
                        <label class="form-check-label small mb-0"
                            for="coworking-gender-female">
                            Female
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center ">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="coworking-gender-other"
                            name="details[gender]"
                            value="other"
                            @checked($coworkingGender === 'other')>
                        <label class="form-check-label small mb-0"
                            for="coworking-gender-other">
                            Other
                        </label>
                    </div>
                </div>
            </div>
            @error('details.gender')
                <div class="field-error mt-1">{{ $message }}</div>
            @enderror
        </div> 
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Business Name</label>
            <input type="text" name="details[business_name]" class="form-control @error('details.business_name') is-invalid @enderror" placeholder="Business Name" value="{{ old('details.business_name') }}">
            @error('details.business_name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
       
       
    </div> -->
    <!-- <div class="form-row">
         <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Marketing Source</label>
            <select name="marketing_source" class="form-control @error('marketing_source') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($marketingSources as $source)
                    <option value="{{ $source }}" @selected(old('marketing_source') == $source)>{{ $source }}</option>
                @endforeach
            </select>
            @error('marketing_source')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div> -->
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">No. of Persons</label>
            <input type="text" name="details[business_name]" class="form-control @error('details.business_name') is-invalid @enderror" placeholder="Enter No. of Persons" value="{{ old('details.business_name') }}">
            @error('details.business_name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Country</label>
            <select id="coworking-country-select" name="details[country]" class="form-control @error('details.country') is-invalid @enderror">
                <option value="">Loading countries...</option>
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">City</label>
            <select id="coworking-city-select" name="city" class="form-control @error('city') is-invalid @enderror">
                <option value="">Loading cities...</option>
            </select>
            @error('city')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Area</label>
            <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Area" value="{{ old('details.area') }}">
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
       
        
    </div>
    <div class="form-row align-items-center">
         <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Preferred Campus</label>
            <input type="text" name="details[preferred_location]" class="form-control @error('details.preferred_location') is-invalid @enderror" placeholder="Preferred Location" value="{{ old('details.preferred_location') }}">
            @error('details.preferred_location')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Expected Starting Date</label>
            <input type="datetime-local" name="details[next_followup_at]" class="form-control @error('details.next_followup_at') is-invalid @enderror" value="{{ old('details.next_followup_at') }}">
            @error('')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="form-group col-lg-6 col-md-6">
            <label class="form-label required">Additional Amenities
            </label>
            <input name="details[remarks]" class="form-control @error('details.remarks') is-invalid @enderror" rows="1" placeholder="Enter Additional Amenities">{{ old('details.remarks') }}</input>
            @error('details.remarks')
                <div class="field-error">{{ $message }}</div>
            @enderror
         </div>
          <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Origin</label>
            <select name="origin" class="form-control @error('origin') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($origins as $origin)
                    <option value="{{ $origin }}" @selected(old('origin') == $origin)>{{ $origin }}</option>
                @endforeach
            </select>
            @error('origin')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Next Follow-up</label>
            <input type="datetime-local" name="details[next_followup_at]" class="form-control @error('details.next_followup_at') is-invalid @enderror" value="{{ old('details.next_followup_at') }}">
            @error('details.next_followup_at')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Probability: Selected <span id="probabilityValue">{{ (int) old('details.probability', data_get($leadPrefill, 'details.probability', 0)) }}%</span></label>
            @include('lead.partials.probability_slider', [
                'inputName' => 'details[probability]',
                'inputId' => 'probabilitySlider',
                'displayId' => 'probabilityValue',
                'value' => old('details.probability', data_get($leadPrefill, 'details.probability', 0)),
                'errorKey' => 'details.probability',
                'showDisplay' => false,
            ])
        </div>
    </div>
    <div class="form-row mt-lg-1">
        <div class="form-group col-12">
            <label class="form-label small fw-semibold text-dark required">
                Remarks
            </label>
        <textarea name="details[remarks]" class="form-control form-control-sm @error('details.remarks') is-invalid @enderror" rows="4" placeholder="Remarks">{{ old('details.remarks', data_get($leadPrefill, 'details.remarks', '')) }}</textarea>
            @error('details.remarks')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!-- <div class="form-row">
        <div class="form-group">
            <label class="form-label required">Remarks</label>
            <textarea name="details[remarks]" class="form-control @error('details.remarks') is-invalid @enderror" rows="3" placeholder="Remarks">{{ old('details.remarks') }}</textarea>
            @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div> -->
</div>
