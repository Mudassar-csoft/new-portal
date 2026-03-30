@extends('layouts.theme')

@section('title', 'Manage Time Table')

@section('content')
    @php
        $filters = $filters ?? ['campus_id' => null, 'program_id' => null, 'batch_id' => null, 'day_of_week' => null, 'status' => null, 'search' => null];
    @endphp

    <div class="batch-timetable-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="box-typical box-typical-dashboard panel panel-default batch-timetable-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Manage Time Table</h3>
                    <small class="text-muted">Create, update, and review weekly schedule slots for each batch.</small>
                </div>
                <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('batch.index') }}" class="btn btn-default">Back to Batches</a>
                    <a href="{{ route('batch.create') }}" class="btn btn-primary">Create Batch</a>
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('batch.timetable.index') }}" class="batch-filter-form">
                    <div class="form-row batch-filter-row">
                        <div class="form-group col-lg-4">
                            <label class="form-label">Campus</label>
                            <select class="form-control" name="campus_id">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4">
                            <label class="form-label">Programme</label>
                            <select class="form-control" name="program_id">
                                <option value="">All Programmes</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) == $program->id)>
                                        {{ $program->code }} - {{ $program->title ?? $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4">
                            <label class="form-label">Batch</label>
                            <select class="form-control" name="batch_id">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? null) == $batch->id)>
                                        {{ $batch->code }} - {{ $batch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4">
                            <label class="form-label">Day</label>
                            <select class="form-control" name="day_of_week">
                                <option value="">All Days</option>
                                @foreach($dayOptions as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['day_of_week'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 batch-filter-col-wide">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Batch, room, instructor, or topic">
                        </div>
                        <div class="form-group batch-filter-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('batch.timetable.index') }}" class="btn btn-danger">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="batch-timetable-layout">
                    <div class="batch-timetable-form-wrap">
                        <div class="batch-timetable-form-card">
                            <h4 class="batch-subtitle">{{ $editingEntry ? 'Edit Time Table Slot' : 'Add Time Table Slot' }}</h4>
                            <form method="POST" action="{{ $editingEntry ? route('batch.timetable.update', $editingEntry) : route('batch.timetable.store') }}">
                                @csrf
                                @if($editingEntry)
                                    @method('PATCH')
                                @endif

                                <div class="form-group">
                                    <label class="required">Batch</label>
                                    <select class="form-control" name="batch_id" required>
                                        <option value="">- Select Batch -</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}" @selected((string) old('batch_id', $editingEntry?->batch_id) === (string) $batch->id)>
                                                {{ $batch->code }} - {{ $batch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="required">Day</label>
                                        <select class="form-control" name="day_of_week" required>
                                            <option value="">- Select Day -</option>
                                            @foreach($dayOptions as $key => $label)
                                                <option value="{{ $key }}" @selected(old('day_of_week', $editingEntry?->day_of_week) === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="required">Status</label>
                                        <select class="form-control" name="status" required>
                                            @foreach($statusOptions as $key => $label)
                                                <option value="{{ $key }}" @selected(old('status', $editingEntry?->status ?? 'active') === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="required">Start Time</label>
                                        <input type="time" class="form-control" name="start_time" value="{{ old('start_time', $editingEntry?->start_time ? \Carbon\Carbon::parse($editingEntry->start_time)->format('H:i') : null) }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="required">End Time</label>
                                        <input type="time" class="form-control" name="end_time" value="{{ old('end_time', $editingEntry?->end_time ? \Carbon\Carbon::parse($editingEntry->end_time)->format('H:i') : null) }}" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Lab / Room</label>
                                        <input type="text" class="form-control" name="lab" value="{{ old('lab', $editingEntry?->lab) }}" placeholder="e.g. Lab-A">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Instructor</label>
                                        <input type="text" class="form-control" name="instructor" value="{{ old('instructor', $editingEntry?->instructor) }}" placeholder="Optional override">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Subject / Topic</label>
                                    <input type="text" class="form-control" name="topic" value="{{ old('topic', $editingEntry?->topic) }}" placeholder="e.g. Adobe Illustrator">
                                </div>

                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="3" placeholder="Optional note">{{ old('remarks', $editingEntry?->remarks) }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end" style="gap:10px;">
                                    @if($editingEntry)
                                        <a href="{{ route('batch.timetable.index') }}" class="btn btn-default">Cancel Edit</a>
                                    @endif
                                    <button type="submit" class="btn btn-primary">{{ $editingEntry ? 'Update Slot' : 'Add Slot' }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="batch-timetable-board-wrap">
                        <div class="batch-timetable-board-card">
                            <h4 class="batch-subtitle">Weekly Overview</h4>
                            <div class="timetable-board-grid">
                                @foreach($dayOptions as $dayKey => $dayLabel)
                                    @php $dayEntries = $boardEntries->get($dayKey, collect()); @endphp
                                    <div class="timetable-day-card">
                                        <div class="timetable-day-head">
                                            <strong>{{ $dayLabel }}</strong>
                                            <span>{{ $dayEntries->count() }} slot{{ $dayEntries->count() === 1 ? '' : 's' }}</span>
                                        </div>
                                        <div class="timetable-day-body">
                                            @forelse($dayEntries as $entry)
                                                <div class="timetable-slot">
                                                    <div class="slot-time">
                                                        {{ \Carbon\Carbon::parse($entry->start_time)->format('h:i A') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($entry->end_time)->format('h:i A') }}
                                                    </div>
                                                    <div class="slot-title">{{ $entry->batch?->code ?? 'Batch' }}{{ $entry->topic ? ' | ' . $entry->topic : '' }}</div>
                                                    <div class="slot-meta">{{ $entry->lab ?: 'No room' }}{{ $entry->instructor ? ' | ' . $entry->instructor : '' }}</div>
                                                </div>
                                            @empty
                                                <div class="text-muted small">No slots configured.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover table-bordered batch-table">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Programme</th>
                                <th>Campus</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Instructor</th>
                                <th>Topic</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                                <tr>
                                    <td>{{ $entry->batch?->code ?? 'N/A' }}<br><span class="text-muted">{{ $entry->batch?->name ?? '' }}</span></td>
                                    <td>{{ $entry->batch?->program?->title ?? $entry->batch?->program?->name ?? 'N/A' }}</td>
                                    <td>{{ $entry->batch?->campus?->code ?? $entry->batch?->campus?->name ?? 'N/A' }}</td>
                                    <td>{{ $dayOptions[$entry->day_of_week] ?? ucfirst($entry->day_of_week) }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($entry->start_time)->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::parse($entry->end_time)->format('h:i A') }}
                                    </td>
                                    <td>{{ $entry->lab ?: 'N/A' }}</td>
                                    <td>{{ $entry->instructor ?: ($entry->batch?->instructor ?? 'N/A') }}</td>
                                    <td>{{ $entry->topic ?: 'N/A' }}</td>
                                    <td><span class="label {{ $entry->status === 'active' ? 'label-success' : ($entry->status === 'inactive' ? 'label-default' : 'label-danger') }}">{{ ucfirst($entry->status) }}</span></td>
                                    <td class="text-right">
                                        @include('batch.partials.timetable-action')
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No timetable entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $entries->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .batch-timetable-shell {
            padding: 10px;
        }

        .batch-timetable-card {
            max-width: 1450px;
            margin: 0 auto;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .batch-filter-form {
            margin-bottom: 18px;
        }

        .batch-filter-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
        }

        .batch-filter-col {
            flex: 1 1 180px;
            min-width: 180px;
        }

        .batch-filter-col-wide {
            flex: 2 1 280px;
            min-width: 260px;
        }

        .batch-filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .batch-timetable-layout {
            display: grid;
            grid-template-columns: minmax(320px, 380px) 1fr;
            gap: 18px;
            align-items: start;
        }
.form-group.batch-filter-actions
 {
    margin: 3.8%;
}
        .batch-timetable-form-card,
        .batch-timetable-board-card {
            border: 1px solid #dbe5f1;
            border-radius: 12px;
            padding: 16px;
            background: #fbfdff;
        }

        .batch-subtitle {
            margin-bottom: 14px;
            font-size: 16px;
            font-weight: 700;
            color: #334155;
        }

        .timetable-board-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .timetable-day-card {
            border: 1px solid #dbe5f1;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .timetable-day-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: #eff7ff;
            border-bottom: 1px solid #dbe5f1;
            font-size: 12px;
            color: #64748b;
        }

        .timetable-day-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .timetable-slot {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            background: #f8fbff;
        }

        .slot-time {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .slot-title {
            color: #1e293b;
            margin-bottom: 2px;
        }

        .slot-meta {
            font-size: 12px;
            color: #64748b;
        }

        .batch-table thead th {
            background: #1fb2ff;
            color: #fff;
            border-color: #1aa4ea;
        }

        .batch-table td,
        .batch-table th {
            vertical-align: middle;
        }

        @media (max-width: 992px) {
            .batch-timetable-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .batch-filter-actions {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush
