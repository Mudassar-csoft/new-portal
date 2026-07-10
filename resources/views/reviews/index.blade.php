@extends('layouts.theme')

@section('title', 'Reviews')

@section('content')
    @php
        $activeScope = $activeScope ?? 'active';
        $scopeCards = $scopeCards ?? [];
        $scopeBadgeColors = [
            'active' => 'badge-success',
            'inactive' => 'badge-warning',
            'featured' => 'badge-primary',
            'all' => 'badge-secondary',
        ];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'review-status-loader', 'message' => 'Loading reviews...'])

        <div id="review-status-content" class="follow-content">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default review-directory">
                <div class="user-mgmt-header">
                    <div class="follow-tab-bar">
                        @foreach ($scopeCards as $card)
                            @php $isActive = $activeScope === $card['scope']; @endphp
                            <a href="{{ route('reviews.index', $card['scope'] === 'active' ? [] : ['scope' => $card['scope']]) }}"
                               class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $card['scope'] }}">
                                <span class="label-text">{{ $card['label'] }}</span>
                                <span class="badge {{ $scopeBadgeColors[$card['scope']] ?? 'badge-secondary' }}">{{ number_format((int) $card['count']) }}</span>
                            </a>
                        @endforeach
                    </div>
                    @if(auth()->user()?->hasAnyPermission(['review.create']))
                        <!-- <a href="{{ route('reviews.create') }}" class="btn btn-inline btn-primary-outline create-action-btn">
                            <i class="fa fa-plus mr-1"></i> Create Review
                        </a> -->
                    @endif
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="reviews-table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Review</th>
                                    <th>Rating</th>
                                    <th>Order</th>
                                    <th>Featured</th>
                                    <th>Status</th>
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
            --dimension-review-index-1: 100vh;
            --dimension-review-index-2: 12px;
            --dimension-review-index-3: 52px;
            --space-review-index-1: 12px;
            --space-review-index-2: 8px;
            --color-review-index-1: #54667a;
            --typo-review-index-font-weight-1: 600;
        }

        .lead-status-shell { position: relative; min-height: var(--dimension-review-index-1); width: 100%; overflow: visible; }
        .follow-loader { position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-review-index-1); background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: var(--space-review-index-1); }
        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-review-index-2); }
        .follow-spinner .dot { width: var(--dimension-review-index-2); height: var(--dimension-review-index-2); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: var(--color-review-index-1); font-weight: var(--typo-review-index-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .follow-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.reviews-ready .follow-content { opacity: 1; visibility: visible; }
        body.reviews-ready #review-status-loader { display: none; }

        .review-directory { margin: 0 auto; }
        .review-directory .box-typical-body { overflow: visible; }
        .review-directory .table-responsive { overflow-x: visible; overflow-y: visible; }
        .user-mgmt-header { display: flex; align-items: stretch; justify-content: space-between; gap: var(--space-review-index-1); flex-wrap: wrap; }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        #reviews-table { margin-top: var(--space-review-index-2); }
        #reviews-table th, #reviews-table td { padding: 6px 10px; vertical-align: middle; }
        #reviews-table tbody tr:nth-of-type(odd) { background-color: #f9fbfd; }
        #reviews-table td.actions-cell { text-align: right; white-space: nowrap; }
        #reviews-table .follow-action-dropdown .dropdown-menu { z-index: 1070 !important; }
        .review-thumb { width: var(--dimension-review-index-3); height: var(--dimension-review-index-3); object-fit: cover; border-radius: 6px; border: 1px solid #dbe5f1; display: inline-flex; align-items: center; justify-content: center; background: #f4f8fb; color: #8a99a8; }

        .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
        .dataTables_wrapper .follow-footer { display: flex; align-items: center; justify-content: space-between; gap: var(--space-review-index-1); }
        .dataTables_wrapper .follow-footer { margin-top: 10px; color: var(--color-review-index-1); font-size: 0.8125rem; }
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
                setTimeout(function () { document.body.classList.add('reviews-ready'); }, 150);
            });
        })();

        $(function () {
            $('#reviews-table').DataTable({
                processing: true, serverSide: true, searchDelay: 700, autoWidth: false,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: "{{ route('reviews.index', ['scope' => $activeScope]) }}",
                order: [[6, 'asc'], [2, 'asc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'profile_image', name: 'profile_image', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'designation', name: 'designation' },
                    { data: 'review', name: 'review' },
                    { data: 'rating', name: 'rating' },
                    { data: 'display_order', name: 'display_order' },
                    { data: 'featured', name: 'featured' },
                    { data: 'status', name: 'status' },
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
