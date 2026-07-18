@extends('layouts.theme')

@section('title', 'Users')

@section('content')
    @php
        $activeScope = $activeScope ?? 'active';
        $scopes = [
            'active' => 'Active Users',
            'deleted' => 'Deleted',
        ];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'user-status-loader', 'message' => 'Loading users...'])

        <div id="user-status-content" class="follow-content">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default user-directory">
                <div class="user-mgmt-header">
                    <div class="follow-tab-bar">
                        @foreach ($scopes as $scopeKey => $scopeLabel)
                            @php $isActive = $activeScope === $scopeKey; @endphp
                            <a href="{{ route('users.index', $scopeKey === 'active' ? [] : ['scope' => $scopeKey]) }}"
                               class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $scopeKey }}">
                                <span class="label-text">{{ $scopeLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                    <!-- <a href="{{ route('users.create') }}" class="btn btn-inline btn-primary-outline create-action-btn">
                        <i class="fa fa-plus mr-1"></i> Create User
                    </a> -->
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="users-table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Campus Code</th>
                                    <th>Date</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --dimension-user-index-1: 100vh;
            --dimension-user-index-2: 12px;
            --space-user-index-1: 12px;
            --space-user-index-2: 8px;
            --color-user-index-1: #54667a;
            --typo-user-index-font-weight-1: 600;
        }

        .lead-status-shell { position: relative; min-height: var(--dimension-user-index-1); width: 100%; overflow: visible; }

        .follow-loader {
            position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-user-index-1);
            background: rgba(245, 247, 251, 0.95);
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            z-index: 10; gap: var(--space-user-index-1);
        }
        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-user-index-2); }
        .follow-spinner .dot { width: var(--dimension-user-index-2); height: var(--dimension-user-index-2); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: var(--color-user-index-1); font-weight: var(--typo-user-index-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }

        .follow-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.users-ready .follow-content { opacity: 1; visibility: visible; }
        body.users-ready #user-status-loader { display: none; }

        .user-directory { margin: 0 auto; }
        .user-directory .box-typical-body { overflow: visible; }
        .user-directory .table-responsive { overflow-x: visible; overflow-y: visible; }

        .user-mgmt-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: var(--space-user-index-1);
            flex-wrap: wrap;
        }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        #users-table { margin-top: var(--space-user-index-2); }
        #users-table th, #users-table td { padding: 6px 10px; vertical-align: middle; }
        #users-table tbody tr:nth-of-type(odd) { background-color: #f9fbfd; }
        #users-table .table-name-link {
            color: #0082C6;
            font-weight: var(--typo-user-index-font-weight-1);
            text-decoration: none;
        }
        
        #users-table td.actions-cell { text-align: right; white-space: nowrap; position: relative; }
        #users-table td.actions-cell .dropdown,
        /* #users-table td.actions-cell .follow-action-dropdown { position: relative; z-index: 1065; }
        #users-table .follow-action-dropdown .dropdown-menu { z-index: 1070 !important; } */

        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
        .dataTables_wrapper .follow-footer {
            display: flex; align-items: center; justify-content: space-between; gap: var(--space-user-index-1);
        }
        .dataTables_wrapper .follow-footer { margin-top: 10px; color: var(--color-user-index-1); font-size: 0.8125rem; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { margin: 0; padding: 0; float: none !important; }
        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label { position: relative; margin: 0; font-size: 0; }
        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label::after {
            content: "\f002"; font-family: FontAwesome; position: absolute; right: 10px; top: 50%;
            transform: translateY(-50%); color: #9aa8b6; font-size: 0.75rem; pointer-events: none;
        }
        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input {
            border: 1px solid #d9e2ef; border-radius: .25rem; padding: .375rem 32px .375rem .75rem;
            height: 32px; width: 240px; box-shadow: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        (function () {
            function revealUserPage() {
                setTimeout(function () { document.body.classList.add('users-ready'); }, 150);
            }
            document.addEventListener('DOMContentLoaded', revealUserPage);
        })();

        $(function () {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: "{{ route('users.index', ['scope' => $activeScope]) }}",
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'campus_code', name: 'campus_code', orderable: false, searchable: false },
                    { data: 'date', name: 'date', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right actions-cell' },
                ]
            });

            var statusMessage = @json(session('status'));
            if (statusMessage) {
                swal({ title: 'Success', text: statusMessage, type: 'success', timer: 1800, showConfirmButton: false });
            }
            var errorMessage = @json(session('error'));
            if (errorMessage) {
                swal({ title: 'Error', text: errorMessage, type: 'error' });
            }
        });
    </script>
@endpush
