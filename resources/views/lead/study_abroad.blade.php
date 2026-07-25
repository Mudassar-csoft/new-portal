@php
    $studyAbroadGender = ($selectedLeadType ?? 'training') === 'study_abroad'
        ? old('details.gender', data_get($leadPrefill, 'details.gender', 'male'))
        : null;
@endphp

<div id="lead-form-study-abroad" class="lead-form active" data-type="study_abroad">
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Full Name (As Per CNIC)</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Full Name" value="{{ old('name', $leadPrefill['name'] ?? '') }}">
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Primary Contact Number</label>
            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="03000000000" value="{{ old('phone', $leadPrefill['phone'] ?? '') }}" @readonly($lockPhoneField ?? false)>
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
            @if($lockPhoneField ?? false)
                <small class="text-muted">Only an admin can change the contact number.</small>
            @endif
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Email" value="{{ old('email', $leadPrefill['email'] ?? '') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Current Education</label>
            <input type="text" name="details[current_education]" class="form-control @error('details.current_education') is-invalid @enderror" placeholder="Current education" value="{{ old('details.current_education', data_get($leadPrefill, 'details.current_education')) }}">
            @error('details.current_education')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Preferred Study Program</label>
            <input type="text" name="details[preferred_study_program]" class="form-control @error('details.preferred_study_program') is-invalid @enderror" placeholder="Preferred study program" value="{{ old('details.preferred_study_program', data_get($leadPrefill, 'details.preferred_study_program')) }}">
            @error('details.preferred_study_program')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Preferred Country</label>
            <input type="text" name="details[preferred_country]" class="form-control @error('details.preferred_country') is-invalid @enderror" placeholder="Preferred destination country" value="{{ old('details.preferred_country', data_get($leadPrefill, 'details.preferred_country')) }}">
            @error('details.preferred_country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Preferred University</label>
            <input type="text" name="details[preferred_university]" class="form-control @error('details.preferred_university') is-invalid @enderror" placeholder="Preferred university" value="{{ old('details.preferred_university', data_get($leadPrefill, 'details.preferred_university')) }}">
            @error('details.preferred_university')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Preferred Campus</label>
            <select name="campus_id" class="form-control @error('campus_id') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" @selected(old('campus_id', $leadPrefill['campus_id'] ?? null) == $campus->id)>{{ $campus->title ?: $campus->name }} ({{ $campus->code ?: $campus->city ?: 'N/A' }})</option>
                @endforeach
            </select>
            @error('campus_id')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Country</label>
            <select id="study-abroad-country-select" name="details[country]" class="form-control @error('details.country') is-invalid @enderror">
                <option value="">Loading countries...</option>
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">City</label>
            <select id="study-abroad-city-select" name="city" class="form-control @error('city') is-invalid @enderror">
                <option value="">Loading cities...</option>
            </select>
            @error('city')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Area</label>
            <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Area" value="{{ old('details.area', data_get($leadPrefill, 'details.area')) }}">
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-lg-3 col-md-6 mb-lg-1">
            <label class="form-label">Gender</label>
            <div class="row mt-2 choice-group @error('details.gender') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center mt-0">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="study-abroad-gender-male" name="details[gender]" value="male" @checked($studyAbroadGender === 'male')>
                        <label class="form-check-label mb-0" for="study-abroad-gender-male">Male</label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="study-abroad-gender-female" name="details[gender]" value="female" @checked($studyAbroadGender === 'female')>
                        <label class="form-check-label mb-0" for="study-abroad-gender-female">Female</label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="study-abroad-gender-other" name="details[gender]" value="other" @checked($studyAbroadGender === 'other')>
                        <label class="form-check-label mb-0" for="study-abroad-gender-other">Other</label>
                    </div>
                </div>
            </div>
            @error('details.gender')
                <div class="field-error mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Marketing Source</label>
            <select name="marketing_source" class="form-control @error('marketing_source') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($marketingSources as $source)
                    <option value="{{ $source }}" @selected(old('marketing_source', $leadPrefill['marketing_source'] ?? null) == $source)>{{ $source }}</option>
                @endforeach
            </select>
            @error('marketing_source')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Origin</label>
            <select name="origin" class="form-control @error('origin') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($origins as $origin)
                    <option value="{{ $origin }}" @selected(old('origin', $leadPrefill['origin'] ?? null) == $origin)>{{ $origin }}</option>
                @endforeach
            </select>
            @error('origin')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Next Follow-up</label>
            <input type="datetime-local" name="details[next_followup_at]" class="form-control @error('details.next_followup_at') is-invalid @enderror" value="{{ old('details.next_followup_at', data_get($leadPrefill, 'details.next_followup_at')) }}">
            @error('details.next_followup_at')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Probability: Selected <span id="probabilityValue">{{ (int) old('details.probability', data_get($leadPrefill, 'details.probability', 50)) }}%</span></label>
            @include('lead.partials.probability_slider', [
                'inputName' => 'details[probability]',
                'inputId' => 'probabilitySlider',
                'displayId' => 'probabilityValue',
                'value' => old('details.probability', data_get($leadPrefill, 'details.probability', 50)),
                'min' => 1,
                'errorKey' => 'details.probability',
                'required' => true,
                'showDisplay' => false,
            ])
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-12">
            <label class="form-label required">Remarks</label>
            <textarea name="details[remarks]" class="form-control @error('details.remarks') is-invalid @enderror" rows="4" placeholder="Remarks">{{ old('details.remarks', data_get($leadPrefill, 'details.remarks')) }}</textarea>
            @error('details.remarks')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
