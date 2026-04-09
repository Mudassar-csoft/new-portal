@php
    $program = $program ?? new \App\Models\Program();
    $typeOptions = collect($typeOptions ?? []);
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive'];
    $selectedType = old('program_type', $program->program_type ?: 'certificate');
    $selectedStatus = old('status', $program->status ?: 'active');
    $discountRows = old('discounts', $discountRows ?? []);

    if ($selectedType && !$typeOptions->contains($selectedType)) {
        $typeOptions = $typeOptions->prepend($selectedType);
    }

    if ($discountRows === []) {
        $discountRows = [[
            'campus_id' => '',
            'discount_percent' => null,
            'status' => 'active',
        ]];
    }
@endphp

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Programme Type</label>
        <select class="form-control @error('program_type') is-invalid @enderror" name="program_type" required>
            <option value="">- Select -</option>
            @foreach($typeOptions as $type)
                <option value="{{ $type }}" @selected($selectedType === $type)>{{ ucwords($type) }}</option>
            @endforeach
        </select>
        @error('program_type')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Programme Title</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $program->title ?? $program->name) }}" placeholder="Enter programme title" required>
        @error('title')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Programme Code</label>
        <input type="text" name="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $program->code) }}" placeholder="Use short unique code GD-01 or CIT." required>
        <!-- <small class="text-muted">Use a short unique code such as GD-01 or CIT.</small> -->
        @error('code')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label>Discount Limit (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="discount_limit" class="form-control @error('discount_limit') is-invalid @enderror" value="{{ old('discount_limit', $program->discount_limit) }}" placeholder="Maximum allowed discount for this programme.">
        <!-- <small class="text-muted">Maximum allowed discount for this programme.</small> -->
        @error('discount_limit')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Fee</label>
        <input type="number" step="0.01" min="0" name="fee" class="form-control @error('fee') is-invalid @enderror" value="{{ old('fee', $program->fee) }}" placeholder="Enter fee amount" required>
        @error('fee')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Duration (Weeks)</label>
        <input type="number" min="1" name="duration_weeks" class="form-control @error('duration_weeks') is-invalid @enderror" value="{{ old('duration_weeks', $program->duration_weeks) }}" placeholder="e.g. 12" required>
        @error('duration_weeks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Installments</label>
        <input type="number" min="1" max="36" name="installments" class="form-control @error('installments') is-invalid @enderror" value="{{ old('installments', $program->installments ?: 1) }}" placeholder="e.g. 3" required>
        @error('installments')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="required">Status</label>
        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Upload Outline</label>
        <input type="file" name="outline" class="form-control-file @error('outline') is-invalid @enderror" accept=".pdf,.doc,.docx">
        <small class="text-muted d-block">Accepted files: PDF, DOC, DOCX up to 5 MB.</small>
        @error('outline')
            <div class="field-error">{{ $message }}</div>
        @enderror
        @if($program->outline_path)
            <div class="mt-2">
                <a href="{{ route('program.outline', $program) }}" class="btn btn-xs btn-default">Download Current Outline</a>
            </div>
        @endif
    </div>
    @if($program->outline_path)
        <div class="form-group col-lg-3 col-md-6">
            <label>Outline Action</label>
            <div class="checkbox">
                <input type="hidden" name="remove_outline" value="0">
                <label>
                    <input type="checkbox" name="remove_outline" value="1" @checked(old('remove_outline'))>
                    Remove current outline
                </label>
            </div>
        </div>
    @endif
</div>

<div class="form-group">
    <label>Prerequisite</label>
    <textarea class="form-control @error('prerequisite') is-invalid @enderror" name="prerequisite" rows="3" placeholder="Enter prerequisite or eligibility">{{ old('prerequisite', $program->prerequisite) }}</textarea>
    @error('prerequisite')
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>Remarks</label>
    <textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" rows="3" placeholder="Add admission notes, delivery detail, or management remarks">{{ old('remarks', $program->remarks) }}</textarea>
    @error('remarks')
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>

<hr>

<div class="program-discount-header">
    <div>
        <h4 class="mb-0">Campus Discount Setup</h4>
        <small class="text-muted">Set a global discount for all campuses or create separate campus-wise discounts.</small>
    </div>
    <button type="button" class="btn btn-default" id="add-program-discount">Add Discount Row</button>
</div>

<div id="program-discount-rows" data-next-index="{{ count($discountRows) }}">
    @foreach($discountRows as $index => $row)
        <div class="program-discount-row" data-discount-row>
            <div class="program-discount-col">
                <label>Campus Scope</label>
                <select class="form-control @error('discounts.' . $index . '.campus_id') is-invalid @enderror" name="discounts[{{ $index }}][campus_id]">
                    <option value="">- Select -</option>
                    <option value="all" @selected(($row['campus_id'] ?? '') === 'all')>All campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected((string) ($row['campus_id'] ?? '') === (string) $campus->id)>
                            {{ $campus->name }}{{ $campus->city ? ' (' . $campus->city . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('discounts.' . $index . '.campus_id')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="program-discount-col">
                <label>Discount %</label>
                <input type="number" step="0.01" min="0" max="100" name="discounts[{{ $index }}][discount_percent]" class="form-control @error('discounts.' . $index . '.discount_percent') is-invalid @enderror" value="{{ $row['discount_percent'] ?? '' }}" placeholder="e.g. 10">
                @error('discounts.' . $index . '.discount_percent')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="program-discount-col">
                <label>Status</label>
                <select class="form-control @error('discounts.' . $index . '.status') is-invalid @enderror" name="discounts[{{ $index }}][status]">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($row['status'] ?? 'active') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('discounts.' . $index . '.status')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="program-discount-action">
                <button type="button" class="btn btn-danger" data-remove-discount>Remove</button>
            </div>
        </div>
    @endforeach
</div>

<template id="program-discount-template">
    <div class="program-discount-row" data-discount-row>
        <div class="program-discount-col">
            <label>Campus Scope</label>
            <select class="form-control" name="discounts[__INDEX__][campus_id]">
                <option value="">- Select -</option>
                <option value="all">All campuses</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}">{{ $campus->name }}{{ $campus->city ? ' (' . $campus->city . ')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="program-discount-col">
            <label>Discount %</label>
            <input type="number" step="0.01" min="0" max="100" name="discounts[__INDEX__][discount_percent]" class="form-control" placeholder="e.g. 10">
        </div>
        <div class="program-discount-col">
            <label>Status</label>
            <select class="form-control" name="discounts[__INDEX__][status]">
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected($key === 'active')>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="program-discount-action">
            <button type="button" class="btn btn-danger" data-remove-discount>Remove</button>
        </div>
    </div>
</template>
