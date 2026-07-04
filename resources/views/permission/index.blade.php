@extends('layouts.theme')

@section('title', 'Permissions')

@section('content')
    @php
        $activeScope = $activeScope ?? 'active';
        $scopes = [
            'active' => 'Active Permissions',
            'deleted' => 'Deleted',
        ];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'permission-status-loader', 'message' => 'Loading permissions...'])

        <div id="permission-status-content" class="follow-content">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default permission-directory">
                <div class="user-mgmt-header">
                    <div class="follow-tab-bar">
                        @foreach ($scopes as $scopeKey => $scopeLabel)
                            @php $isActive = $activeScope === $scopeKey; @endphp
                            <a href="{{ route('permissions.index', $scopeKey === 'active' ? [] : ['scope' => $scopeKey]) }}"
                               class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $scopeKey }}">
                                <span class="label-text">{{ $scopeLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                    <!-- <a href="{{ route('permissions.create') }}" class="btn btn-inline btn-primary-outline create-action-btn">
                        <i class="fa fa-plus mr-1"></i> Create Permission
                    </a> -->
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="permissions-table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Resource</th>
                                    <th>Action</th>
                                    <th>Slug</th>
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
            --dimension-permission-index-1: 100vh;
            --dimension-permission-index-2: 12px;
            --space-permission-index-1: 12px;
            --space-permission-index-2: 8px;
            --color-permission-index-1: #54667a;
        }

        .lead-status-shell { position: relative; min-height: var(--dimension-permission-index-1); width: 100%; overflow: visible; }
        .follow-loader { position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-permission-index-1); background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: var(--space-permission-index-1); }
        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-permission-index-2); }
        .follow-spinner .dot { width: var(--dimension-permission-index-2); height: var(--dimension-permission-index-2); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: var(--color-permission-index-1); font-weight: 600; }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .follow-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.permissions-ready .follow-content { opacity: 1; visibility: visible; }
        body.permissions-ready #permission-status-loader { display: none; }

        .permission-directory { margin: 0 auto; }
        .permission-directory .box-typical-body { overflow: visible; }
        .permission-directory .table-responsive { overflow-x: visible; overflow-y: visible; }

        .user-mgmt-header {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: var(--space-permission-index-1);
            flex-wrap: wrap;
        }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        .create-action-btn {
            align-self: center;
            padding: 0.5rem 1rem !important;
            white-space: nowrap;
            margin: 8px 12px 8px 0;
        }
        @media (max-width: 767px) {
            .create-action-btn { margin: 0 12px 8px; width: calc(100% - 24px); text-align: center; }
        }
        #permissions-table { margin-top: var(--space-permission-index-2); }
        #permissions-table th, #permissions-table td { padding: 6px 10px; vertical-align: middle; text-align: center; }
        #permissions-table tbody tr:nth-of-type(odd) { background-color: #f9fbfd; }
        #permissions-table .follow-action-dropdown .dropdown-menu { z-index: 1070 !important; }

        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
        .dataTables_wrapper .follow-footer { display: flex; align-items: center; justify-content: space-between; gap: var(--space-permission-index-1); }
        .dataTables_wrapper .follow-footer { margin-top: 10px; color: var(--color-permission-index-1); font-size: 13px; }
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
                setTimeout(function () { document.body.classList.add('permissions-ready'); }, 150);
            });
        })();

        $(function () {
            $('#permissions-table').DataTable({
                processing: true, serverSide: true, autoWidth: false,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: "{{ route('permissions.index', ['scope' => $activeScope]) }}",
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'resource', name: 'resource' },
                    { data: 'action', name: 'action' },
                    { data: 'slug', name: 'slug' },
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
