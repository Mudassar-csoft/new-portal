@extends('layouts.theme')

@section('title', ($moduleTitle ?? 'Lead') . ' Transfers')

@section('content')
    <div class="follow-shell">
        <div id="transfer-loader" class="follow-loader">
            <div class="follow-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading transfer grid...</p>
        </div>

        <div id="transfer-content" class="follow-content">
            <div class="box-typical box-typical-dashboard panel panel-default follow-card">
                <header class="box-typical-header panel-heading d-flex justify-content-between">
                    <div>
                        <h3 class="panel-title">Lead Management | <span class="text-muted ">{{ $moduleTitle ?? 'Lead' }} Transfers</span></h3>
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <div class="table-responsive overflow-hidden">
                        <table class="table table-hover table-striped text-left" id="transfer-grid">
                            <thead>
                                <tr>
                                    <th class="text-left w-auto p-1">Sr#</th>
                                    <th class="text-left w-auto p-1">Name</th>
                                    <th class="text-left w-auto p-1">Program</th>
                                    <th class="text-left w-auto p-1">Phone</th>
                                    <th class="text-left w-auto p-1">From</th>
                                    <th class="text-left w-auto p-1">To </th>
                                    <!-- <th class="text-left w-auto p-1">Status</th> -->
                                    <!-- <th class="text-left w-auto p-1">Requested By</th> -->
                                    <!-- <th class="text-left w-auto p-1">Requested At</th> -->
                                    <!-- <th class="text-left w-auto p-1">Approved By</th> -->
                                    <!-- <th class="text-left w-auto p-1">Approved At</th> -->
                                    <th class="text-left w-auto p-1">Response</th>
                                    <th class="text-left w-auto p-1">Action</th>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        .follow-shell {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        .follow-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: rgba(245, 247, 251, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10;
            gap: 12px;
        }

        .follow-spinner {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .follow-spinner .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }

        .follow-spinner .dot:nth-child(2) {
            animation-delay: 0.15s;
            background: #1f8ef1;
        }

        .follow-spinner .dot:nth-child(3) {
            animation-delay: 0.3s;
            background: #36b1ff;
        }

        .follow-loader p {
            margin: 0;
            color: #54667a;
            font-weight: 600;
        }

        .follow-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.transfer-ready .follow-content {
            opacity: 1;
            visibility: visible;
        }

        body.transfer-ready #transfer-loader {
            display: none;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.6;
            }
            40% {
                transform: translateY(-12px);
                opacity: 1;
            }
        }

        #transfer-grid thead th {
            background: #1fb2ff;
            color: #fff;
            border-color: #1aa4ea;
            font-weight: 600;
            vertical-align: middle;
        }

        #transfer-grid {
            border: 1px solid #d9e2ef;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }

        #transfer-grid th,
        #transfer-grid td {
            border-color: #d9e2ef;
            padding: 4px 8px;
            line-height: 1.2;
            height: 28px;
            vertical-align: middle;
        }

        #transfer-grid tbody tr:nth-of-type(odd) {
            background-color: #f5f6ff;
        }

        .lead-link {
            color: #0099f8;
            font-weight: 700;
            text-decoration: none !important;
        }

        .lead-link:hover {
            color: #007dcc;
            text-decoration: none !important;
        }
table#transfer-grid{
    width: auto !important;
}
 th {
    border: 1px solid #dee2e6 ;
}
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            setTimeout(function () {
                document.body.classList.add('transfer-ready');
            }, 150);

            $('#transfer-grid').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: "{{ $ajaxUrl ?? route('leads.transfer') }}",
                order: [[7, 'desc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'lead_name', name: 'lead_name' },
                    { data: 'program', name: 'program', orderable: false, searchable: false },
                    { data: 'lead_phone', name: 'lead_phone' },
                    { data: 'from_campus', name: 'from_campus' },
                    { data: 'to_campus', name: 'to_campus' },
                    // { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                    // { data: 'requested_by', name: 'requested_by', orderable: false, searchable: false },
                    // { data: 'created_at', name: 'created_at' },
                    // { data: 'approved_by', name: 'approved_by', orderable: false, searchable: false },
                    // { data: 'approved_at', name: 'approved_at', orderable: false, searchable: false },
                    { data: 'reason', name: 'reason' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            var statusMessage = @json(session('status'));
            if (statusMessage && window.swal) {
                swal({
                    title: 'Success',
                    text: statusMessage,
                    type: 'success',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        })();
    </script>
@endpush
