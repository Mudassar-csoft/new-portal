@extends('layouts.theme')

@section('title', 'Roles')

@section('content')
    @php
        $activeScope = $activeScope ?? 'active';
        $scopes = [
            'active' => 'Active Roles',
            'deleted' => 'Deleted',
        ];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'role-status-loader', 'message' => 'Loading roles...'])

        <div id="role-status-content" class="follow-content">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default role-directory">
                <div class="user-mgmt-header">
                    <div class="follow-tab-bar">
                        @foreach ($scopes as $scopeKey => $scopeLabel)
                            @php $isActive = $activeScope === $scopeKey; @endphp
                            <a href="{{ route('roles.index', $scopeKey === 'active' ? [] : ['scope' => $scopeKey]) }}"
                               class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $scopeKey }}">
                                <span class="label-text">{{ $scopeLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                    <!-- <a href="{{ route('roles.create') }}" class="btn btn-inline btn-primary-outline create-action-btn">
                        <i class="fa fa-plus mr-1"></i> Create Role
                    </a> -->
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="roles-table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Type</th>
                                    <th>Permissions</th>
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
            --dimension-role-index-1: 100vh;
            --dimension-role-index-2: 12px;
            --space-role-index-1: 12px;
            --space-role-index-2: 8px;
            --color-role-index-1: #54667a;
            --typo-role-index-font-weight-1: 600;
        }

        .lead-status-shell { position: relative; min-height: var(--dimension-role-index-1); width: 100%; overflow: visible; }
        .follow-loader { position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-role-index-1); background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: var(--space-role-index-1); }
        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-role-index-2); }
        .follow-spinner .dot { width: var(--dimension-role-index-2); height: var(--dimension-role-index-2); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: var(--color-role-index-1); font-weight: var(--typo-role-index-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .follow-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.roles-ready .follow-content { opacity: 1; visibility: visible; }
        body.roles-ready #role-status-loader { display: none; }

        .role-directory { margin: 0 auto; }
        .role-directory .box-typical-body { overflow: visible; }
        .role-directory .table-responsive { overflow-x: visible; overflow-y: visible; }

        .user-mgmt-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: var(--space-role-index-1);
            flex-wrap: wrap;
        }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        #roles-table { margin-top: var(--space-role-index-2); }
        #roles-table th, #roles-table td { padding: 6px 10px; vertical-align: middle; }
        #roles-table tbody tr:nth-of-type(odd) { background-color: #f9fbfd; }
        #roles-table .table-name-link {
            color: ##0082C6;
            font-weight: var(--typo-role-index-font-weight-1);
            text-decoration: none;
        }

        #roles-table td.actions-cell { text-align: right; white-space: nowrap; }
        #roles-table .follow-action-dropdown .dropdown-menu { z-index: 1070 !important; }

        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
        .dataTables_wrapper .follow-footer { display: flex; align-items: center; justify-content: space-between; gap: var(--space-role-index-1); }
        .dataTables_wrapper .follow-footer { margin-top: 10px; color: var(--color-role-index-1); font-size: 0.8125rem; }
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate {
            margin: 0; padding: 0; float: none !important;
        }
        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input {
            border: 1px solid #d9e2ef; border-radius: .25rem; padding: .375rem .75rem;
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
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () { document.body.classList.add('roles-ready'); }, 150);
            });
        })();

        $(function () {
            $('#roles-table').DataTable({
                processing: true, serverSide: true, searchDelay: 700, autoWidth: false,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: "{{ route('roles.index', ['scope' => $activeScope]) }}",
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug' },
                    { data: 'is_system', name: 'is_system', orderable: false, searchable: false },
                    { data: 'permissions', name: 'permissions', orderable: false, searchable: false },
                    { data: 'date', name: 'date', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right actions-cell' },
                ]
            });

            var statusMessage = @json(session('status'));
            if (statusMessage) { swal({ title: 'Success', text: statusMessage, type: 'success', timer: 1800, showConfirmButton: false }); }
            var errorMessage = @json(session('error'));
            if (errorMessage) { swal({ title: 'Error', text: errorMessage, type: 'error' }); }
        });
    </script>
@endpush
