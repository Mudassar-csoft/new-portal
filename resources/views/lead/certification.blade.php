@php
    $certificationGender = ($selectedLeadType ?? 'certification') === 'certification'
        ? old('details.gender', data_get($leadPrefill, 'details.gender', 'male'))
        : null;

    $defaultTeachingMethod = old('details.teaching_method') ?: 'campus';
@endphp

<div id="lead-form-certification" class="lead-form active" data-type="certification">
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
            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="03000000000" value="{{ old('phone', $leadPrefill['phone'] ?? '') }}">
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Email" value="{{ old('email', $leadPrefill['email'] ?? '') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Certification / Exam Title</label>
            <input type="text" name="details[certification_title]" class="form-control @error('details.certification_title') is-invalid @enderror" placeholder="Enter certification or exam title" value="{{ old('details.certification_title', data_get($leadPrefill, 'details.certification_title')) }}">
            @error('details.certification_title')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Exam Code</label>
            <input type="text" name="details[exam_code]" class="form-control @error('details.exam_code') is-invalid @enderror" placeholder="Exam Code" value="{{ old('details.exam_code', data_get($leadPrefill, 'details.exam_code')) }}">
            @error('details.exam_code')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Organization / Vendor</label>
            <input type="text" name="details[organization]" class="form-control @error('details.organization') is-invalid @enderror" placeholder="Organization / Vendor" value="{{ old('details.organization', data_get($leadPrefill, 'details.organization')) }}">
            @error('details.organization')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Teaching Method</label>
            <select name="details[teaching_method]" class="form-control @error('details.teaching_method') is-invalid @enderror">
                <option value="">- Select -</option>
                <option value="campus" @selected($defaultTeachingMethod === 'campus')>Campus</option>
                <option value="online" @selected($defaultTeachingMethod === 'online')>Online</option>
                <option value="hybrid" @selected($defaultTeachingMethod === 'hybrid')>Hybrid</option>
            </select>
            @error('details.teaching_method')
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
            <select id="certification-country-select" name="details[country]" class="form-control @error('details.country') is-invalid @enderror">
                <option value="">Loading countries...</option>
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">City</label>
            <select id="certification-city-select" name="city" class="form-control @error('city') is-invalid @enderror">
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
                        <input class="form-check-input mt-0 mr-1" type="radio" id="certification-gender-male" name="details[gender]" value="male" @checked($certificationGender === 'male')>
                        <label class="form-check-label mb-0" for="certification-gender-male">Male</label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="certification-gender-female" name="details[gender]" value="female" @checked($certificationGender === 'female')>
                        <label class="form-check-label mb-0" for="certification-gender-female">Female</label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="certification-gender-other" name="details[gender]" value="other" @checked($certificationGender === 'other')>
                        <label class="form-check-label mb-0" for="certification-gender-other">Other</label>
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
