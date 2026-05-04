<div class="lead-form" data-type="coworking">
    <div class="form-row">
        <div class="form-group col-md-4">
            <label class="required">Full Name (As Per CNIC)</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Full Name" value="{{ old('name') }}">
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label class="required">Personal Contact Number</label>
            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="03000000000" value="{{ old('phone') }}">
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label>Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Email" value="{{ old('email') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row">
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
                            @checked(old('details.gender', 'male') === 'male')>
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
                            @checked(old('details.gender') === 'female')>
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
                            @checked(old('details.gender') === 'other')>
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
        <div class="form-group col-md-4">
            <label class="required">Business Name</label>
            <input type="text" name="details[business_name]" class="form-control @error('details.business_name') is-invalid @enderror" placeholder="Business Name" value="{{ old('details.business_name') }}">
            @error('details.business_name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label class="required">Space Required</label>
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
    <div class="form-row">
        <div class="form-group col-md-4">
            <label class="required">Marketing Source</label>
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
        <div class="form-group col-md-4">
            <label class="required">Origin</label>
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
        <div class="form-group col-md-4">
            <label class="required">Country</label>
            <input type="text" name="details[country]" class="form-control @error('details.country') is-invalid @enderror" value="{{ old('details.country', 'Pakistan') }}">
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label class="required">City</label>
            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" placeholder="Enter City" value="{{ old('city') }}">
            @error('city')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label>Area</label>
            <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Area" value="{{ old('details.area') }}">
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label>Preferred Location</label>
            <input type="text" name="details[preferred_location]" class="form-control @error('details.preferred_location') is-invalid @enderror" placeholder="Preferred Location" value="{{ old('details.preferred_location') }}">
            @error('details.preferred_location')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row align-items-center">
        <div class="form-group col-md-4">
            <label class="required">Next Follow-up</label>
            <input type="datetime-local" name="details[next_followup_at]" class="form-control @error('details.next_followup_at') is-invalid @enderror" value="{{ old('details.next_followup_at') }}">
            @error('details.next_followup_at')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label class="required">Probability</label>
            <input type="range" name="details[probability]" min="0" max="100" step="10" class="form-control-range probability-range @error('details.probability') is-invalid @enderror" value="{{ old('details.probability', 0) }}">
            <div class="probability-display">Selected: <span>{{ old('details.probability', 0) }}%</span></div>
            @error('details.probability')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-group">
        <label class="required">Remarks</label>
        <textarea name="details[remarks]" class="form-control @error('details.remarks') is-invalid @enderror" rows="3" placeholder="Remarks">{{ old('details.remarks') }}</textarea>
        @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>
