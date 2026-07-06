<div class="program-filter-field">
    <label class="form-label">Status</label>
    <select class="form-control form-control-sm" name="status">
        <option value="">All Statuses</option>
        @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
            <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>
