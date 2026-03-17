@php
    $campus = $campus ?? new \App\Models\Campus();
    $typeOptions = $typeOptions ?? ['company' => 'Company Owned', 'franchise' => 'Franchise'];
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive'];
    $selectedType = old('campus_type', $campus->campus_type ?: 'company');
    $selectedStatus = old('status', $campus->status ?: 'active');
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-row">
    <div class="form-group col-md-4">
        <label class="required">Campus Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $campus->name) }}" placeholder="Enter campus name" required>
    </div>
    <div class="form-group col-md-4">
        <label>Display Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $campus->title) }}" placeholder="Optional public or short title">
    </div>
    <div class="form-group col-md-4">
        <label class="required">Campus Code</label>
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
        <small class="text-muted">Code is generated from the city abbreviation.</small>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label class="required">Country</label>
        <select class="form-control" id="country-select" name="country" required>
            <option value="">Loading countries...</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <label class="required">City</label>
        <select class="form-control" id="city-select" name="city" required>
            <option value="">Loading cities...</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <label class="required">City Abbreviation</label>
        <input type="text" name="city_abbr" id="campus-city-abbr" class="form-control text-uppercase" value="{{ old('city_abbr', $campus->city_abbr) }}" placeholder="e.g. FSD" required>
    </div>
    <div class="form-group col-md-3">
        <label class="required">Status</label>
        <select class="form-control" name="status" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Campus Email Address</label>
        <input type="email" name="campus_email" class="form-control" value="{{ old('campus_email', $campus->campus_email) }}" placeholder="Enter campus email address">
    </div>
    <div class="form-group col-md-4">
        <label>Campus Landline Number</label>
        <input type="text" name="landline" class="form-control" value="{{ old('landline', $campus->landline) }}" placeholder="Enter landline number">
    </div>
    <div class="form-group col-md-4">
        <label>Campus Mobile Number</label>
        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $campus->mobile) }}" placeholder="Enter mobile number">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="required d-block">Campus Type</label>
        <div class="campus-type-options">
            @foreach($typeOptions as $key => $label)
                <label class="campus-type-option">
                    <input type="radio" name="campus_type" value="{{ $key }}" @checked($selectedType === $key)>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>Number of Labs</label>
        <input type="number" name="labs_count" class="form-control" min="0" value="{{ old('labs_count', $campus->labs_count) }}" placeholder="Enter number of labs">
    </div>
    <div class="form-group col-md-3">
        <label>Royalty Rate (%)</label>
        <input type="number" step="0.01" min="0" name="royalty_rate" id="royalty-rate" class="form-control" value="{{ old('royalty_rate', $campus->royalty_rate) }}" placeholder="Franchise only">
        <small class="text-muted">Only used for franchise branches.</small>
    </div>
</div>

<div class="form-group">
    <label class="required">Campus Address</label>
    <textarea class="form-control" name="address" rows="3" placeholder="Enter campus address here..." required>{{ old('address', $campus->address) }}</textarea>
</div>

<div class="form-group">
    <label>Remarks</label>
    <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks, admin notes, or branch details">{{ old('remarks', $campus->remarks) }}</textarea>
</div>
