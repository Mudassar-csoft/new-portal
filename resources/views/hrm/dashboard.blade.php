@extends('layouts.theme')

@section('title', 'HRM Dashboard')

@section('content')
    @php
        $stats = $stats ?? [];
        $cards = [
            ['label' => 'Employees (Total)', 'value' => (int) ($stats['employees_total'] ?? 0), 'tone' => 'total'],
            ['label' => 'Active Employees', 'value' => (int) ($stats['employees_active'] ?? 0), 'tone' => 'active'],
            ['label' => 'Inactive Employees', 'value' => (int) ($stats['employees_inactive'] ?? 0), 'tone' => 'inactive'],
            ['label' => 'Today Attendance', 'value' => (int) ($stats['today_attendance'] ?? 0), 'tone' => 'attendance'],
            ['label' => 'Attendance Requests', 'value' => (int) ($stats['pending_attendance_requests'] ?? 0), 'tone' => 'request'],
            ['label' => 'Leave Requests', 'value' => (int) ($stats['pending_leave_requests'] ?? 0), 'tone' => 'leave'],
            ['label' => 'Draft Payroll Runs', 'value' => (int) ($stats['draft_payroll_runs'] ?? 0), 'tone' => 'payroll'],
            ['label' => 'Published Notices', 'value' => (int) ($stats['published_announcements'] ?? 0), 'tone' => 'notice'],
            ['label' => 'Expiring Documents (30d)', 'value' => (int) ($stats['expiring_documents'] ?? 0), 'tone' => 'doc'],
        ];
    @endphp

    <div class="hrm-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
            <header class="box-typical-header panel-heading hrm-head">
                <h3 class="panel-title">HRM Dashboard</h3>
                <div class="hrm-head-actions">
                    <a href="{{ route('hrm.employees.index') }}" class="btn btn-inline btn-primary-outline">Employees</a>
                    <a href="{{ route('hrm.attendance.index') }}" class="btn btn-inline btn-primary-outline">Attendance</a>
                    <a href="{{ route('hrm.leaves.index') }}" class="btn btn-inline btn-primary-outline">Leave</a>
                    <a href="{{ route('hrm.payroll.index') }}" class="btn btn-inline btn-primary-outline">Payroll</a>
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <div class="row">
                    @foreach($cards as $card)
                        <div class="col-xl-4 col-md-6">
                            <article class="hrm-stat tone-{{ $card['tone'] }}">
                                <div class="stat-label">{{ $card['label'] }}</div>
                                <div class="stat-value">{{ number_format((int) $card['value']) }}</div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .hrm-shell { padding: 8px 0 16px; }
        .hrm-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .hrm-head-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .hrm-stat {
            border-radius: 10px;
            color: #fff;
            padding: 14px;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
        }
        .hrm-stat .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: .88;
        }
        .hrm-stat .stat-value {
            margin-top: 6px;
            font-size: 24px;
            font-weight: 700;
        }
        .tone-total { background: linear-gradient(135deg, #1f2937, #111827); }
        .tone-active { background: linear-gradient(135deg, #16a34a, #15803d); }
        .tone-inactive { background: linear-gradient(135deg, #6b7280, #4b5563); }
        .tone-attendance { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .tone-request { background: linear-gradient(135deg, #f97316, #ea580c); }
        .tone-leave { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .tone-payroll { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .tone-notice { background: linear-gradient(135deg, #0f766e, #115e59); }
        .tone-doc { background: linear-gradient(135deg, #334155, #1e293b); }
    </style>
@endpush

