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
            //['label' => 'Attendance Requests', 'value' => (int) ($stats['pending_attendance_requests'] ?? 0), 'tone' => 'request'],
            ['label' => 'Leave Requests', 'value' => (int) ($stats['pending_leave_requests'] ?? 0), 'tone' => 'leave'],
            ['label' => 'Draft Payroll Runs', 'value' => (int) ($stats['draft_payroll_runs'] ?? 0), 'tone' => 'payroll'],
            ['label' => 'Published Notices', 'value' => (int) ($stats['published_announcements'] ?? 0), 'tone' => 'notice'],
            ['label' => 'Expiring Documents (30d)', 'value' => (int) ($stats['expiring_documents'] ?? 0), 'tone' => 'doc'],
        ];
    @endphp

    <div class="hrm-shell">
        @include('partials.session-status-alert')

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
            <div class="box-typical-body panel-body hrm-dashboard-div">
                <div class="row">
                    @foreach($cards as $card)
                        <div class="col-xl-3 col-md-6">
                            <article class="hrm-stat tone-{{ $card['tone'] }}">
                                <div class="stat-value">{{ number_format((int) $card['value']) }}</div>
                                <div class="stat-label">{{ $card['label'] }}</div>
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
        :root {
            --space-hrm-dashboard-1: 10px;
            --color-hrm-dashboard-1: #975ce7;
            --color-hrm-dashboard-2: #f35f62;
        }

        .hrm-dashboard-div{
            padding:14px !important;
        }
        /* .hrm-shell { padding: 8px 0 16px; } */
        .hrm-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--space-hrm-dashboard-1);
            flex-wrap: wrap;
        }
        .hrm-head-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .hrm-stat {
            border-radius: 10px;
            height: 25vh;
            color: #fff;
            padding: 14px;
            margin-bottom: 32px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
            min-height: 86px;
        }
        .hrm-stat .stat-label {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            opacity: .88;
            text-align: center;
            margin-top: 1rem;
        }
        .hrm-stat .stat-value {
            margin-top: 30px;
            font-size: 18px;
            color: white;
            text-align: center;
            font-weight: 700;
        }
        .tone-total { background: var(--color-hrm-dashboard-2); }
        .tone-active { background: #fdc518; }
        .tone-inactive { background: var(--color-hrm-dashboard-1) }
        .tone-attendance { background: #a2cf37; }
        .tone-request { background: var(--color-hrm-dashboard-1); }
        .tone-leave { background: #4285f4; }
        .tone-payroll { background: #00a8ff; }
        .tone-notice { background: var(--color-hrm-dashboard-2); }
        .tone-doc { background: #34a853; }
        @media (max-width: 760px)  {
            .hrm-stat .stat-label {
                margin-top: 5px;
            }
            .hrm-stat .stat-value {
                margin-top: var(--space-hrm-dashboard-1);
            }
        }
    </style>
@endpush
