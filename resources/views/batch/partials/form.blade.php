@php
    $batch = $batch ?? new \App\Models\Batch();
    $sessionOptions = $sessionOptions ?? ['morning' => 'Morning', 'evening' => 'Evening', 'weekend' => 'Weekend'];
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $selectedSession = old('session', $batch->session ?: 'morning');
    $selectedStatus = old('status', $batch->status ?: 'active');
@endphp

<div class="form-row">
    <div class="form-group col-md-4">
        <label class="required">Select Campus</label>
        <select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" id="batch-campus" required>
            <option value="">- Select -</option>
            @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" @selected((string) old('campus_id', $batch->campus_id) === (string) $campus->id)>
                    {{ $campus->name }}{{ $campus->code ? ' (' . $campus->code . ')' : '' }}
                </option>
            @endforeach
        </select>
        @error('campus_id')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label class="required">Select Program</label>
        <select class="form-control @error('program_id') is-invalid @enderror" name="program_id" id="batch-program" required>
            <option value="">- Select -</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}" data-code="{{ $program->code }}" @selected((string) old('program_id', $batch->program_id) === (string) $program->id)>
                    {{ $program->title ?? $program->name }}{{ $program->code ? ' (' . $program->code . ')' : '' }}
                </option>
            @endforeach
        </select>
        @error('program_id')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label class="required">Batch Code</label>
        <input type="text" class="form-control" id="batch-code-preview" value="{{ old('code', $batch->code) }}" readonly>
        <small class="text-muted">Code is auto-generated. If a duplicate exists, the system adds a suffix automatically.</small>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label class="required">Batch Name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $batch->name) }}" placeholder="e.g. Graphic Design Spring Morning" required>
        @error('name')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label class="required">Instructor / Teacher</label>
        <input type="text" class="form-control @error('instructor') is-invalid @enderror" name="instructor" value="{{ old('instructor', $batch->instructor) }}" placeholder="Enter instructor name" required>
        @error('instructor')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Lab / Room</label>
        <input type="text" class="form-control @error('lab') is-invalid @enderror" name="lab" value="{{ old('lab', $batch->lab) }}" placeholder="e.g. Lab-A / Room 4">
        @error('lab')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label class="required">Batch Starting Date</label>
        <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" id="batch-start-date" value="{{ old('start_date', optional($batch->start_date)->format('Y-m-d')) }}" required>
        @error('start_date')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label>Expected Ending Date</label>
        <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date', optional($batch->end_date)->format('Y-m-d')) }}">
        @error('end_date')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="required">Start Time</label>
        <input type="time" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time', $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('H:i') : null) }}" required>
        @error('start_time')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="required">End Time</label>
        <input type="time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time', $batch->end_time ? \Carbon\Carbon::parse($batch->end_time)->format('H:i') : null) }}" required>
        @error('end_time')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="required d-block">Batch Session</label>
        <div class="session-radio-group">
            @foreach($sessionOptions as $key => $label)
                <label class="session-radio">
                    <input type="radio" name="session" value="{{ $key }}" @checked($selectedSession === $key) class="@error('session') is-invalid @enderror">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('session')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label class="required">Batch Status</label>
        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label>Remarks</label>
    <textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" rows="3" placeholder="Optional notes, capacity, or schedule remarks">{{ old('remarks', $batch->remarks) }}</textarea>
    @error('remarks')
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>
