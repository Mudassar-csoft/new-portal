<div class="program-filter-field">
    <label class="form-label">Programme</label>
    <select class="form-control form-control-sm" name="program_id">
        <option value="">All Programmes</option>
        @foreach($programs as $program)
            <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) == $program->id)>
                {{ $program->code }} - {{ $program->title ?? $program->name }}
            </option>
        @endforeach
    </select>
</div>
