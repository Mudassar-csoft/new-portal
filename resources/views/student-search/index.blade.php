@extends('layouts.theme')

@section('title', 'Student Search')

@section('content')
    <div class="lead-status-shell">
        <div class="follow-content" style="opacity:1; visibility:visible;">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">
                                    Search Results
                                    @if($query !== '')
                                        for <span class="search-needle">"{{ $query }}"</span>
                                    @endif
                                </h2>
                            </div>
                            <div class="tbl-cell text-right" style="min-width: 360px;">
                                <form method="GET" action="{{ route('student-search.index') }}" class="inline-search-form">
                                    <input type="text" name="q" value="{{ $query }}" class="form-control form-control-sm" placeholder="Name, phone, CNIC, roll no, reg no, email...">
                                    <button type="submit" class="btn btn-sm btn-primary-outline">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body follow-body">
                    @if($query === '')
                        <div class="search-empty">
                            <i class="fa fa-search" style="font-size: 32px; color: #cbd5e1;"></i>
                            <p>Enter a name, phone number, CNIC, roll number, or registration number to find a student.</p>
                        </div>
                    @elseif($totalMatches === 0)
                        <div class="search-empty">
                            <i class="fa fa-frown-o" style="font-size: 32px; color: #cbd5e1;"></i>
                            <p>No students or leads matched "<strong>{{ $query }}</strong>".</p>
                        </div>
                    @else
                        <div class="search-summary">
                            Found <strong>{{ $totalMatches }}</strong> result{{ $totalMatches === 1 ? '' : 's' }}
                            ({{ $admissions->count() }} admission{{ $admissions->count() === 1 ? '' : 's' }},
                            {{ ($registrations ?? collect())->count() }} registration{{ ($registrations ?? collect())->count() === 1 ? '' : 's' }},
                            {{ $leads->count() }} lead{{ $leads->count() === 1 ? '' : 's' }}).
                        </div>

                        @if($admissions->isNotEmpty())
                            <h4 class="section-heading">Admissions / Students</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered follow-table">
                                    <thead>
                                        <tr>
                                            <th>Sr</th>
                                            <th>Name</th>
                                            <!-- <th>Roll No</th> -->
                                            <!-- <th>Reg No</th> -->
                                            <th>Programme</th>
                                            <th>Primary Contact</th>
                                            <th>Campus Code</th>
                                            <!-- <th>Admission Date</th> -->
                                            <!-- <th>Status</th> -->
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($admissions as $idx => $a)
                                            <tr>
                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                <td><strong>{{ $a->student_name ?? 'N/A' }}</strong></td>
                                                <!-- <td>{{ $a->roll_number ?: 'N/A' }}</td> -->
                                                <!-- <td>{{ $a->registration_number ?: 'N/A' }}</td> -->
                                                <td>{{ $a->program?->title ?? $a->program?->name ?? 'N/A' }}</td>
                                                <td>{{ $a->phone ?: 'N/A' }}</td>
                                                <td>{{ $a->campus?->code ?? $a->campus?->name ?? 'N/A' }}</td>
                                                <!-- <td>{{ optional($a->admission_date)->format('d-M-Y') ?? 'N/A' }}</td> -->
                                                <!-- <td>
                                                    <span class="label label-info">{{ ucfirst($a->student_status ?? 'enrolled') }}</span>
                                                </td> -->
                                                <td class="text-right">
                                                    <a href="{{ $a->registration_id ? route('student.show', $a->registration_id) : route('admission.status') }}" class="btn btn-primary">
                                                        <!-- <i class="fa fa-eye"></i>  -->
                                                        Action
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if(($registrations ?? collect())->isNotEmpty())
                            <h4 class="section-heading">Registrations</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered follow-table">
                                    <thead>
                                        <tr>
                                           <th>Sr</th>
                                            <th>Name</th>
                                            <!-- <th>Roll No</th> -->
                                            <!-- <th>Reg No</th> -->
                                            <th>Programme</th>
                                            <th>Primary Contact</th>
                                            <th>Campus Code</th>
                                            <!-- <th>Admission Date</th> -->
                                            <!-- <th>Status</th> -->
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($registrations as $idx => $r)
                                            <tr>
                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                <td><strong>{{ $r->student_name ?? 'N/A' }}</strong></td>
                                                <td>{{ $r->program?->title ?? $r->program?->name ?? 'N/A' }}</td>
                                                <td>{{ $r->phone ?: 'N/A' }}</td>
                                                <!-- <td>{{ $r->registration_number ?: 'N/A' }}</td> -->
                                                <!-- <td>{{ $r->receipt_number ?: 'N/A' }}</td> -->
                                                <td>{{ $r->campus?->code ?? $r->campus?->name ?? 'N/A' }}</td>
                                                <!-- <td>
                                                    <span class="label label-{{ $r->status === 'registered' ? 'info' : 'default' }}">
                                                        {{ ucfirst($r->status ?? 'pending') }}
                                                    </span>
                                                </td> -->
                                                <td class="text-right">
                                                    <a href="{{ route('registration.status') }}" class="btn btn-sm btn-primary">
                                                        Action
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($leads->isNotEmpty())
                            <h4 class="section-heading">Leads</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered follow-table">
                                    <thead>
                                        <tr>
                                            <th>Sr</th>
                                            <th>Name</th>
                                            <!-- <th>Roll No</th> -->
                                            <!-- <th>Reg No</th> -->
                                            <th>Programme</th>
                                            <th>Primary Contact</th>
                                            <th>Campus Code</th>
                                            <!-- <th>Admission Date</th> -->
                                            <!-- <th>Status</th> -->
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leads as $idx => $lead)
                                            <tr>
                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                <td><strong style=" color: #0a6fd1;">{{ $lead->name ?? 'N/A' }}</strong></td>
                                                <td>{{ $lead->program?->title ?? $lead->program?->name ?? 'N/A' }}</td>
                                                <td>{{ $lead->phone ?: 'N/A' }}</td>
                                                <!-- <td>{{ $lead->email ?: 'N/A' }}</td> -->
                                                <td>{{ $lead->campus?->code ?? $lead->campus?->name ?? 'N/A' }}</td>
                                                <!-- <td>
                                                    <span class="label label-{{ $lead->status === 'registered' ? 'info' : ($lead->status === 'enrolled' ? 'success' : 'primary') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $lead->status ?? 'pending')) }}
                                                    </span>
                                                </td> -->
                                                <td class="text-right">
                                                    <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-primary">
                                                        <!-- <i class="fa fa-eye"></i>  -->
                                                        Action
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .lead-status-shell { position: relative; min-height: 100vh; width: 100%; }
        .lead-title { font-size: 18px; font-weight: 500; color: #1f2937; line-height: 1.4; }
        .search-needle { color: #0a6fd1; font-weight: 700; }

        .inline-search-form {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
        }
        .inline-search-form .form-control { width: 240px; height: 32px; }

        .search-empty {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        .search-empty p { margin-top: 12px; font-size: 14px; }

        .search-summary {
            padding: 10px 14px;
            margin-bottom: 14px;
            background: #eff6ff;
            border: 1px solid #cfe0f5;
            border-radius: 8px;
            color: #0a6fd1;
            font-size: 13px;
        }

        .section-heading {
            margin: 16px 0 10px;
            font-size: 15px;
            font-weight: 700;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }

        .follow-table th, .follow-table td { padding: 6px 10px; vertical-align: middle; }
        .table-responsive { overflow: visible !important; }

        .tbl-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .tbl-cell.text-right { flex: 0 0 auto; text-align: right; }
    </style>
@endpush
