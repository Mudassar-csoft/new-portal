@php
    $program = $program ?? new \App\Models\Program();
    $typeOptions = collect($typeOptions ?? []);
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive'];
    $selectedType = old('program_type', $program->program_type ?: 'certificate');
    $selectedStatus = old('status', $program->status ?: 'active');
    $discountRows = old('discounts', $discountRows ?? []);
    $codeReadonly = (bool) ($codeReadonly ?? false);

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

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-row mt-1">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Programme Type</label>
        <select class="form-control" name="program_type" required>
            <option value="">- Select -</option>
            @foreach($typeOptions as $type)
                <option value="{{ $type }}" @selected($selectedType === $type)>{{ ucwords($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Programme Title</label>
        <input type="text" name="title" id="program-title" class="form-control" value="{{ old('title', $program->title ?? $program->name) }}" placeholder="Enter programme title" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Fee</label>
        <input type="number" step="0.01" min="0" name="fee" class="form-control" value="{{ old('fee', $program->fee) }}" placeholder="Enter fee amount" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Programme Code</label>
        <input type="text" name="code" id="program-code" class="form-control text-uppercase program-code-field" value="{{ old('code', $program->code) }}" placeholder="Auto code e.g. WD" required @if($codeReadonly) readonly aria-readonly="true" @endif>
        <!-- <small class="text-muted">Use a short unique code such as GD-01 or CIT.</small> -->
    </div>
</div>
<div class="form-row">
     <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Duration (Weeks)</label>
        <input type="number" min="1" name="duration_weeks" class="form-control" value="{{ old('duration_weeks', $program->duration_weeks) }}" placeholder="e.g. 12" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Installments</label>
        <input type="number" min="1" max="36" name="installments" class="form-control" value="{{ old('installments', $program->installments ?: 1) }}" placeholder="e.g. 3" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Status</label>
        <select name="status" class="form-control" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required" >Upload Outline</label>
        <label class="program-upload" for="program-outline-upload">
            <input type="file" id="program-outline-upload" name="outline" accept=".pdf,.doc,.docx">
            <span class="program-upload-icon" aria-hidden="true">
                <i class="fa fa-cloud-upload"></i>
            </span>
            <span class="program-upload-copy">
                <span class="program-upload-title" data-upload-label>Choose outline file</span>
                <span class="program-upload-hint">PDF, DOC, DOCX up to 5 MB</span>
            </span>
        </label>
        <!-- <small class="text-muted d-block">Accepted files: PDF, DOC, DOCX up to 5 MB.</small> -->
        @if($program->outline_path)
            <div class="mt-2">
                <a href="{{ route('program.outline', $program) }}" class="btn btn-xs btn-default">Download Current Outline</a>
            </div>
        @endif
    </div>
</div>

<div class="form-row">
    @if($program->outline_path)
        <div class="form-group col-lg-4 col-md-6">
           <label class="form-label required">Outline Action</label>
            <div class="checkbox">
                <input type="hidden" name="remove_outline" value="0">
               <label class="form-label required">
                    <input type="checkbox" name="remove_outline" value="1" @checked(old('remove_outline'))>
                    Remove current outline
                </label>
            </div>
        </div>
    @endif
</div>

<div class="program-discount-header">
    <div>
        <h4 class="mb-0">Campus Discount</h4>
        <!-- <small class="text-muted">Set a global discount for all campuses or create separate campus-wise discounts.</small> -->
    </div>
    <button type="button" class="btn btn-default" id="add-program-discount">Add</button>
</div>

<div id="program-discount-rows" data-next-index="{{ count($discountRows) }}">
    @foreach($discountRows as $index => $row)
        <div class="program-discount-row" data-discount-row>
            <div class="program-discount-col">
               <label class="form-label required">Campus Scope</label>
                <select class="form-control" name="discounts[{{ $index }}][campus_id]">
                    <option value="">- Select -</option>
                    <option value="all" @selected(($row['campus_id'] ?? '') === 'all')>All campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" @selected((string) ($row['campus_id'] ?? '') === (string) $campus->id)>
                            {{ $campus->name }}{{ $campus->city ? ' (' . $campus->city . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="program-discount-col">
               <label class="form-label required">Discount %</label>
                <input type="number" step="0.01" min="0" max="100" name="discounts[{{ $index }}][discount_percent]" class="form-control" value="{{ $row['discount_percent'] ?? '' }}" placeholder="e.g. 10">
            </div>
            <div class="program-discount-col">
               <label class="form-label required">Status</label>
                <select class="form-control" name="discounts[{{ $index }}][status]">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($row['status'] ?? 'active') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
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
           <label class="form-label required">Campus Scope</label>
            <select class="form-control" name="discounts[__INDEX__][campus_id]">
                <option value="">- Select -</option>
                <option value="all">All campuses</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}">{{ $campus->name }}{{ $campus->city ? ' (' . $campus->city . ')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="program-discount-col">
           <label class="form-label required">Discount %</label>
            <input type="number" step="0.01" min="0" max="100" name="discounts[__INDEX__][discount_percent]" class="form-control" placeholder="e.g. 10">
        </div>
        <div class="program-discount-col">
           <label class="form-label required">Status</label>
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

<hr>

<div class="form-group">
   <label class="form-label required">Prerequisite</label>
    <textarea class="form-control" name="prerequisite" rows="3" placeholder="Enter prerequisite or eligibility">{{ old('prerequisite', $program->prerequisite) }}</textarea>
</div>

<div class="form-group">
   <label class="form-label required">Remarks</label>
    <textarea class="form-control" name="remarks" rows="3" placeholder="Add admission notes, delivery detail, or management remarks">{{ old('remarks', $program->remarks) }}</textarea>
</div>
