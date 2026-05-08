@extends('layouts.theme')

@section('title', 'Student Attendance')

@section('content')
    @php
        $filters = $filters ?? [
            'attendance_date' => now()->toDateString(),
            'campus_id' => null,
            'program_id' => null,
            'batch_id' => null,
            'status' => null,
            'search' => null,
        ];
        $summary = $summary ?? ['total' => 0, 'present' => 0, 'late' => 0, 'half_day' => 0, 'leave' => 0, 'absent' => 0];
        $badgeClasses = [
            'present' => 'badge-success',
            'late' => 'badge-warning',
            'half_day' => 'badge-info',
            'leave' => 'badge-primary',
            'absent' => 'badge-danger',
        ];
    @endphp

    <div class="student-attendance-shell">
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

        <section class="box-typical box-typical-dashboard panel panel-default student-attendance-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Student Attendance</h3>
                    <!-- <small class="text-muted">Check daily attendance by campus, batch, program, and student search.</small> -->
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('student.attendance.index') }}" class="mb-3">
                    <div class="form-row student-attendance-filters">
                        <div class="form-group    col-lg-4 col-md-6">
                            <label class="form-label">Attendance Date</label>
                            <input type="date" name="attendance_date" class="form-control" value="{{ $filters['attendance_date'] }}">
                        </div>
                        <div class="form-group    col-lg-4 col-md-6">
                            <label class="form-label">Campus</label>
                            <select name="campus_id" class="form-control">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group    col-lg-4 col-md-6">
                            <label class="form-label">Program</label>
                            <select name="program_id" class="form-control">
                                <option value="">All Programmes</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) == $program->id)>
                                        {{ $program->code }} - {{ $program->title ?? $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        </div>
                        <div class="form-row">
                            <div class="form-group    col-lg-4 col-md-6">
                            <label class="form-label">Batch</label>
                            <select name="batch_id" class="form-control">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" @selected(($filters['batch_id'] ?? null) == $batch->id)>
                                        {{ $batch->code }} - {{ $batch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group    col-lg-4 col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach(['present', 'late', 'half_day', 'leave', 'absent'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group    col-lg-4 col-md-6">
                            <label class="form-label">Search Student</label>
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Student name, roll number, or registration number">
                        </div>
                        <div class="form-group student-actions  mr-1">
                            <button type="submit" class="btn btn-primary-outline ">Filter</button>
                            <a href="{{ route('student.attendance.index') }}" class="btn btn-danger-outline p-2">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- <div class="student-summary-grid">
                    <div class="student-summary-card ">
                        <strong>{{ number_format((int) ($summary['total'] ?? 0)) }}</strong>
                        <span class="student-summary-label">Total Students</span>
                    </div>
                    <div class="student-summary-card tone-green">
                        <strong>{{ number_format((int) ($summary['present'] ?? 0)) }}</strong>
                        <span class="student-summary-label">Present</span>
                    </div>
                    <div class="student-summary-card tone-yellow">
                        <strong>{{ number_format((int) ($summary['late'] ?? 0)) }}</strong>
                        <span class="student-summary-label">Late</span>
                    </div>
                    <div class="student-summary-card tone-blue">
                        <strong>{{ number_format((int) ($summary['half_day'] ?? 0)) }}</strong>
                        <span class="student-summary-label">Half Day</span>
                    </div>
                    <div class="student-summary-card tone-purple">
                        <strong>{{ number_format((int) ($summary['leave'] ?? 0)) }}</strong>
                        <span class="student-summary-label">Leave</span>
                    </div>
                    <div class="student-summary-card tone-red">
                        <strong>{{ number_format((int) ($summary['absent'] ?? 0)) }}</strong>
                        <span class="student-summary-label">Absent</span>
                    </div>
                </div> -->

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover student-attendance-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Roll / Reg</th>
                                <th>Campus</th>
                                <th>Program</th>
                                <th>Batch</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Worked</th>
                                <th>Late</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Biometric</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                @php
                                    $attendance = $student->attendances->first();
                                    $status = $attendance?->status ?? 'absent';
                                @endphp
                                <tr>
                                    <td>{{ $student->student_name }}</td>
                                    <td>
                                        <div>{{ $student->roll_number ?: '-' }}</div>
                                        <small class="text-muted">{{ $student->registration_number ?: '-' }}</small>
                                    </td>
                                    <td>{{ $student->campus?->code ?? '-' }}{{ $student->campus?->name ? ' - ' . $student->campus->name : '' }}</td>
                                    <td>{{ $student->program?->title ?? $student->program?->name ?? '-' }}</td>
                                    <td>{{ $student->batch?->code ?? '-' }}{{ $student->batch?->name ? ' - ' . $student->batch->name : '' }}</td>
                                    <td>{{ optional($attendance?->check_in_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ optional($attendance?->check_out_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ $attendance?->worked_minutes ?? 0 }}</td>
                                    <td>{{ $attendance?->late_minutes ?? 0 }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClasses[$status] ?? 'badge-secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </td>
                                    <td>{{ strtoupper($attendance?->source ?? '-') }}</td>
                                    <td>{{ $attendance?->device_user_id ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">No students found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $students->links() }}
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default student-attendance-card mt-3">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title mb-0">Biometric / CSV Import</h3>
            </header>
            <div class="box-typical-body panel-body">
                <div class="alert alert-info m-2">
                    Import ZKTeco export files or standard CSV files. Supported headers include
                    <strong>roll_number</strong>, <strong>registration_number</strong>, <strong>student_name</strong>,
                    <strong>attendance_date</strong>, <strong>punch_time</strong>, <strong>check_in_at</strong>,
                    <strong>check_out_at</strong>, <strong>status</strong>, <strong>device_user_id</strong>,
                    <strong>campus_code</strong>, <strong>program_code</strong>, and <strong>batch_code</strong>.
                </div>

                <form method="POST" action="{{ route('student.attendance.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row student-attendance-filters">
                        <div class="form-group col-md-3 ">
                            <label class="form-label">Source Type</label>
                            <select name="source_type" class="form-control" required>
                                <option value="zkteco">ZKTeco Export</option>
                                <option value="csv">Generic CSV</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 ">
                            <label class="form-label">Device / Source Name</label>
                            <input type="text" name="source_name" class="form-control" placeholder="ZKTeco Main Gate">
                        </div>
                        <div class="form-group  col-md-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Morning shift import">
                        </div>
                        <div class="form-group col-md-3 ">
                            <label class="form-label">CSV File</label>
                            <input type="file" name="import_file" class="form-control-file" required>
                        </div>
                        <div class="form-group student-actions mr-1">
                            <button type="submit" class="btn btn-primary">Import Attendance</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover student-attendance-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Total</th>
                                <th>Processed</th>
                                <th>Failed</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($imports as $import)
                                <tr>
                                    <td>{{ optional($import->import_date)->format('Y-m-d') ?: '-' }}</td>
                                    <td>{{ strtoupper($import->source_type) }}</td>
                                    <td>{{ $import->source_name ?: '-' }}</td>
                                    <td>{{ $import->total_records }}</td>
                                    <td>{{ $import->processed_records }}</td>
                                    <td>{{ $import->failed_records }}</td>
                                    <td>{{ $import->remarks ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No attendance imports logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        /* .student-attendance-shell {
            padding: 10px;
        } */

        .student-attendance-card {
            /* max-width: 1380px; */
            margin: 0 auto;
        }

        .student-attendance-filters {
            display: flex;
            /* gap: 14px; */
            flex-wrap: wrap;
            align-items: end;
        }

        .student-actions {
            display: flex;
            gap: 10px;
            align-items: end;
            margin-left: auto;
        }

        .student-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(150px, 1fr));
            gap: 14px;
            padding:15px;
        }

        .student-summary-card {
            border: 1px solid #e6edf5;
            border-radius: 12px;
            padding: 14px 16px;
            background: #34a853;
            text-align:center;
            height:25vh;
            color:white;
            min-height: 86px;
        }

        .student-summary-card strong {
            display: block;
            font-size: 18px;
            line-height: 1.2;
            margin-top: 30px;
            text-align:center;
        }

        .student-summary-label {
            color: white;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-top: 1rem;
        }

        .tone-green { background: #f35f62; }
        .tone-yellow { background: #fdc518; }
        .tone-blue { background:  #975ce7; }
        .tone-purple { background: #a2cf37; }
        .tone-red { background: #00a8ff; }

        .student-attendance-table thead th {
            white-space: nowrap;
        }

        .student-attendance-table {
            width: auto !important;
            min-width: 100% !important;
            max-width: none !important;
            table-layout: auto !important;
        }

        .student-attendance-table th,
        .student-attendance-table td {
            width: auto !important;
            min-width: 0 !important;
            max-width: none !important;
        }

        @media (max-width: 767px) {
            .student-summary-card strong {
                margin-top: 10px;
            }
            .student-summary-label {
                margin-top: 5px;
            }
            .student-actions {
                width: 100%;
                margin-left: 0;
            }

            .student-actions .btn {
                flex: 1 1 auto;
            }
        }
    </style>
@endpush
