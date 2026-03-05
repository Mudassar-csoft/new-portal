@extends('layouts.theme')

@section('title', 'HR Documents')

@section('content')
    @php
        $filters = $filters ?? ['employee_id' => null, 'status' => null];
    @endphp

    <div class="hrm-shell">
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

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title">Documents (Upload + Expiry Reminder)</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('hrm.documents.index') }}" class="mb-3">
                    <div class="form-row">
                        <div class="form-group custom-col-4">
                            <label class="form-label required" >Employee</label>
                            <select class="form-control" name="employee_id">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? null) == $employee->id)>
                                        {{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-4">
                            <label class="form-label required" >Status</label>
                            <select class="form-control" name="status">
                                <option value="">All</option>
                                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Expired</option>
                            </select>
                        </div>
                        <div class="form-group custom-col-1 d-flex align-items-end justify-end mt-4">
                            <button class="btn btn-inline btn-primary-outline mr-2" type="submit">Filter</button>
                            <a href="{{ route('hrm.documents.index') }}" class="btn btn-inline btn-danger-outline p-2">Reset</a>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('hrm.documents.store') }}" enctype="multipart/form-data" class="mb-3 hrm-box">
                    @csrf
                    <div class="form-row" style="gap:18px;">
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Document Type</label>
                            <select name="document_type" class="form-control" required>
                                @foreach(['offer_letter','cnic_copy','degree','contract','nda','experience_letter','warning_letter','other'] as $type)
                                    <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Issue Date</label>
                            <input type="date" name="issue_date" class="form-control">
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Reminder</label>
                            <input type="number" min="0" max="365" name="reminder_days_before" class="form-control" value="30">
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                         <div class="form-group custom-col-3">
                            <label class="form-label required" >File</label>
                            <input type="file" name="file" class="form-control-file" required>
                        </div>
                    </div>
                    <div class="form-row" style="gap:8px;">
                       
                        
                        <div class="form-group custom col-12">
                            <label class="form-label required" >Notes</label>
                            <input type="text" name="notes" class="form-control" style="
    width: 97%;">
                        </div>
                    </div>
                    <div class="text-right mr-4">
                    <button class="btn btn-inline btn-primary-outline" type="submit">Upload Document</button>
                </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Issue</th>
                                <th>Expiry</th>
                                <th>Reminder</th>
                                <th>Status</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $document)
                                <tr>
                                    <td>{{ $document->employee?->employee_code ?: 'EMP' }} - {{ $document->employee?->full_name ?: '-' }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $document->document_type)) }}</td>
                                    <td>{{ $document->title }}</td>
                                    <td>{{ optional($document->issue_date)->format('Y-m-d') ?: '-' }}</td>
                                    <td>{{ optional($document->expiry_date)->format('Y-m-d') ?: '-' }}</td>
                                    <td>{{ $document->reminder_days_before }} day(s)</td>
                                    <td>{{ ucfirst($document->status) }}</td>
                                    <td>
                                        <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No documents found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $documents->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        
        .hrm-shell { padding: 8px 0 16px; }
        .hrm-table thead th { background: #eef2f7; color: #334155; }
        .hrm-box { border: 1px solid #e6ebf1; border-radius: 8px; padding: 10px; }
    
    </style>
@endpush

