@php
    $coworkingGender = ($selectedLeadType ?? 'training') === 'coworking'
        ? old('details.gender', data_get($leadPrefill, 'details.gender', 'male'))
        : null;
    $selectedPreferredBranch = old(
        'details.preferred_location',
        data_get($leadPrefill, 'details.preferred_location')
            ?? optional($campuses->firstWhere('id', data_get($leadPrefill, 'campus_id')))->code
    );
@endphp
<div id="lead-form-coworking" class="lead-form active coworking-voucher-lead" data-type="coworking">
    <div class="coworking-voucher-head">
        <div class="coworking-voucher-brand">
            <div class="coworking-voucher-kicker">Career Institute</div>
            <div class="coworking-voucher-name">Coworking Lead Intake</div>
            <div class="coworking-voucher-copy">Structured in the same voucher-style layout for faster scanning and entry.</div>
        </div>
        <div class="coworking-voucher-meta">
            <div><span>Lead Type</span><strong>Coworking Space</strong></div>
            <div><span>Preferred Branch</span><strong>{{ $selectedPreferredBranch ?: 'Pending' }}</strong></div>
            <div><span>Stage</span><strong>New Lead</strong></div>
        </div>
    </div>

    <section class="voucher-section">
        <div class="voucher-section-title">Client Details</div>
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
            <label class="form-label required">Space Type</label>
            <select name="details[space_required]" class="form-control @error('details.space_required') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach(['Dedicated Desk', 'Shared Office', 'Private Office', 'Studio Space', 'Meeting Room', 'Event Hall', 'Virtual Office'] as $space)
                    <option value="{{ $space }}" @selected(old('details.space_required', data_get($leadPrefill, 'details.space_required')) === $space)>{{ $space }}</option>
                @endforeach
            </select>
            @error('details.space_required')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Business / Team Name</label>
            <input type="text" name="details[business_name]" class="form-control @error('details.business_name') is-invalid @enderror" placeholder="Enter Business / Team Name" value="{{ old('details.business_name', data_get($leadPrefill, 'details.business_name', '')) }}">
            @error('details.business_name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">No. of Persons</label>
            <input type="number" min="1" name="details[person_count]" class="form-control @error('details.person_count') is-invalid @enderror" placeholder="Enter No. of Persons" value="{{ old('details.person_count', data_get($leadPrefill, 'details.person_count', '')) }}">
            @error('details.person_count')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Preferred Branch</label>
            <select name="details[preferred_location]" class="form-control @error('details.preferred_location') is-invalid @enderror">
                <option value="">- Select Branch -</option>
                @foreach($campuses as $campus)
                    @php
                        $campusCode = $campus->code ?: $campus->name;
                        $campusName = $campus->title ?: $campus->name;
                        $campusLabel = $campus->code ? ($campus->code . ' - ' . $campusName) : $campusName;
                        $isSelectedPreferredBranch = in_array((string) $selectedPreferredBranch, [
                            (string) $campus->code,
                            (string) $campus->name,
                            (string) $campus->title,
                        ], true);
                    @endphp
                    <option value="{{ $campusCode }}" @selected($isSelectedPreferredBranch)>
                        {{ $campusLabel }}
                    </option>
                @endforeach
            </select>
            @error('details.preferred_location')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Gender</label>
            <div class="row mt-2 choice-group @error('details.gender') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center mt-0">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="coworking-gender-male" name="details[gender]" value="male" @checked($coworkingGender === 'male')>
                        <label class="form-check-label small mb-0" for="coworking-gender-male">Male</label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="coworking-gender-female" name="details[gender]" value="female" @checked($coworkingGender === 'female')>
                        <label class="form-check-label small mb-0" for="coworking-gender-female">Female</label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1" type="radio" id="coworking-gender-other" name="details[gender]" value="other" @checked($coworkingGender === 'other')>
                        <label class="form-check-label small mb-0" for="coworking-gender-other">Other</label>
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
            <label class="form-label">Area</label>
            <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Area" value="{{ old('details.area', data_get($leadPrefill, 'details.area', '')) }}">
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Expected Starting Date</label>
            <input type="datetime-local" name="details[expected_starting_at]" class="form-control @error('details.expected_starting_at') is-invalid @enderror" value="{{ old('details.expected_starting_at', data_get($leadPrefill, 'details.expected_starting_at')) }}">
            @error('details.expected_starting_at')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    </section>

    <section class="voucher-section">
        <div class="voucher-section-title">Lead Preferences</div>
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
            <label class="form-label">Additional Amenities</label>
            <input type="text" name="details[additional_amenities]" class="form-control @error('details.additional_amenities') is-invalid @enderror" placeholder="Parking, lockers, meeting room, etc." value="{{ old('details.additional_amenities', data_get($leadPrefill, 'details.additional_amenities', '')) }}">
            @error('details.additional_amenities')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row align-items-center">
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
    </section>
</div>

@once
    @push('styles')
        <style>
            .coworking-voucher-lead {
                border: 1px solid #2f2f2f;
                background: linear-gradient(180deg, #ffffff 0%, #fbfbfb 100%);
                padding: 18px 16px 10px;
            }

            .coworking-voucher-lead .coworking-voucher-head {
                display: grid;
                grid-template-columns: minmax(0, 1.7fr) minmax(220px, 0.9fr);
                gap: 14px;
                align-items: start;
                border-bottom: 1px solid #2f2f2f;
                padding-bottom: 14px;
                margin-bottom: 14px;
            }

            .coworking-voucher-lead .coworking-voucher-kicker {
                font-size: 12px;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #6b7280;
                margin-bottom: 4px;
            }

            .coworking-voucher-lead .coworking-voucher-name {
                font-size: 24px;
                font-weight: 700;
                color: #111827;
            }

            .coworking-voucher-lead .coworking-voucher-copy {
                margin-top: 4px;
                font-size: 13px;
                color: #4b5563;
            }

            .coworking-voucher-lead .coworking-voucher-meta {
                display: grid;
                gap: 8px;
            }

            .coworking-voucher-lead .coworking-voucher-meta > div {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
                border-bottom: 1px solid #d1d5db;
                font-size: 13px;
            }

            .coworking-voucher-lead .coworking-voucher-meta span {
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .coworking-voucher-lead .coworking-voucher-meta strong {
                color: #111827;
                font-weight: 700;
            }

            .coworking-voucher-lead .voucher-section {
                border: 1px solid #2f2f2f;
                background: #fff;
                padding: 12px 10px 2px;
                margin-bottom: 14px;
            }

            .coworking-voucher-lead .voucher-section-title {
                display: inline-block;
                margin: -21px 0 10px;
                padding: 0 8px;
                background: #fff;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #111827;
            }

            .coworking-voucher-lead .form-label {
                margin-bottom: 6px;
                margin-top: 6px;
                font-size: 13.8px !important;
                color: #343a40 !important;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.03em;
            }

            .coworking-voucher-lead .form-control,
            .coworking-voucher-lead .choice-group {
                min-height: 46px;
                border-radius: 0;
                border: 1px solid #6b7280;
                background: #fff;
                box-shadow: none;
            }

            .coworking-voucher-lead .choice-group {
                margin-left: 0;
                margin-right: 0;
                align-items: center;
            }

            .coworking-voucher-lead .field-error {
                margin-top: 6px;
                font-size: 12px;
                font-weight: 600;
            }

            @media (max-width: 767px) {
                .coworking-voucher-lead .coworking-voucher-head {
                    grid-template-columns: 1fr;
                }

                .coworking-voucher-lead .coworking-voucher-name {
                    font-size: 20px;
                }
            }
        </style>
    @endpush
@endonce
