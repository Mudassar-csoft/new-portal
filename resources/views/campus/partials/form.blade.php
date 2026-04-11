@php
    $campus = $campus ?? new \App\Models\Campus();
    $typeOptions = $typeOptions ?? ['company' => 'Company Owned', 'franchise' => 'Franchise'];
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive'];
    $selectedType = old('campus_type', $campus->campus_type ?: 'company');
    $selectedStatus = old('status', $campus->status ?: 'active');
@endphp

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Campus Name</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $campus->name) }}" placeholder="Enter campus name" required>
        @error('name')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Display Title</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $campus->title) }}" placeholder="Optional public or short title">
        @error('title')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Status</label>
        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Campus Code</label>
        <input
            type="text"
            id="campus-code-preview"
            class="form-control"
            value="{{ old('code', $campus->code ?: 'Auto generated on save') }}"
            data-count-url="{{ url('/campus/count') }}"
            data-ignore-id="{{ $campus->exists ? $campus->id : '' }}"
            data-current-code="{{ $campus->code }}"
            readonly
        >
        <!-- <small class="text-muted">Code is generated from the city abbreviation.</small> -->
    </div>
</div>

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Country</label>
        <select class="form-control @error('country') is-invalid @enderror" id="country-select" name="country" required>
            <option value="">Loading countries...</option>
        </select>
        @error('country')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">City</label>
        <select class="form-control @error('city') is-invalid @enderror" id="city-select" name="city" required>
            <option value="">Loading cities...</option>
        </select>
        @error('city')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">City Abbreviation</label>
        <input type="text" name="city_abbr" id="campus-city-abbr" class="form-control text-uppercase @error('city_abbr') is-invalid @enderror" value="{{ old('city_abbr', $campus->city_abbr) }}" placeholder="e.g. FSD" required>
        @error('city_abbr')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Campus Email Address</label>
        <input type="email" name="campus_email" class="form-control @error('campus_email') is-invalid @enderror" value="{{ old('campus_email', $campus->campus_email) }}" placeholder="Enter campus email address">
        @error('campus_email')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Campus Landline Number</label>
        <input type="text" name="landline" class="form-control @error('landline') is-invalid @enderror" value="{{ old('landline', $campus->landline) }}" placeholder="Enter landline number">
        @error('landline')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Campus Mobile Number</label>
        <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $campus->mobile) }}" placeholder="Enter mobile number">
        @error('mobile')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
     <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Number of Labs</label>
        <input type="number" name="labs_count" class="form-control @error('labs_count') is-invalid @enderror" min="0" value="{{ old('labs_count', $campus->labs_count) }}" placeholder="Enter number of labs">
        @error('labs_count')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Royalty Rate (%)</label>
        <input type="number" step="0.01" min="0" name="royalty_rate" id="royalty-rate" class="form-control @error('royalty_rate') is-invalid @enderror" value="{{ old('royalty_rate', $campus->royalty_rate) }}" placeholder="Franchise only">
        @error('royalty_rate')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6 ">
        <label class="form-label required d-block">Campus Type</label>
        <div class=" campus-type-options mt-2 pl-0 pr-0 d-flex justify-content-between">
            @foreach($typeOptions as $key => $label)
                <div class="d-flex justify-content-center m-0 pr-0 pl-0">
                    <div class="form-check d-flex align-items-center campus-type-option">
                        <input
                            class="form-check-input mt-0 mr-1 @error('campus_type') is-invalid @enderror"
                            type="radio"
                            id="campus-type-{{ $key }}"
                            name="campus_type"
                            value="{{ $key }}"
                            @checked($selectedType === $key)
                        >
                        <label class="form-check-label small mb-0" for="campus-type-{{ $key }}">
                            {{ $label }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('campus_type')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
   
</div>

<div class="form-row">
    <div class="col-12">
    <label class="form-label required">Campus Address</label>
    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3" placeholder="Enter campus address here..." required>{{ old('address', $campus->address) }}</textarea>
    @error('address')
        <div class="field-error">{{ $message }}</div>
    @enderror
</div></div>

<!-- <div class="row">
    <div class="col-12">
    <label class="form-label required">Remarks</label>
</div></div> -->


<div class="form-row mt-lg-1">
    <div class="col-12">
        <label class="form-label small text-dark">
            Remarks
        </label>
        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3" placeholder="Enter remarks, admin notes, or branch details">{{ old('remarks', $campus->remarks) }}</textarea>
        @error('remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
        </div>
    </div>
