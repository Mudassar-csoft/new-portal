@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="box-typical box-typical-dashboard panel panel-default student-directory">
                    <header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="panel-title mb-0 form-label">{{ $pageTitle }}</h3>
                            <!-- <small class="text-muted">{{ $pageDescription }}</small> -->
                        </div>
                    </header>
                    <div class="box-typical-body panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="student-records-table">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Student</th>
                                        <th>Roll No</th>
                                        <th>Registration No</th>
                                        <th>Campus</th>
                                        <th>Programme</th>
                                        <th>Batch</th>
                                        <th>Admission Date</th>
                                        <th>Status</th>
                                        <th>Certificate</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
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
        .student-directory {
            max-width: 1450px;
            margin: 0 auto;
            overflow: visible;
        }

        .student-directory .box-typical-body {
            padding: 16px;
            overflow: visible;
        }

        .student-directory .table-responsive {
            overflow-x: visible;
            overflow-y: visible;
        }

        #student-records-table {
            margin-top: 8px;
            border: 1px solid #d9e2ef;
            border-radius: 6px;
            background: #fff;
        }

        #student-records-table thead th {
            background: #1fb2ff;
            color: #fff;
            border-color: #1aa4ea;
            font-weight: 600;
            vertical-align: middle;
        }

        #student-records-table th,
        #student-records-table td {
            border-color: #d9e2ef;
            padding: 6px 10px;
            vertical-align: middle;
            border-right: 1px solid #d9e2ef;
            border-bottom: 1px solid #d9e2ef;
        }

        #student-records-table tbody tr:nth-of-type(odd) {
            background-color: #f5f6ff;
        }

        #student-records-table td.actions-cell {
            text-align: right;
            white-space: nowrap;
        }

        #student-records-table .dropdown-menu {
            z-index: 1050;
        }

        .student-directory .dataTables_wrapper {
            border-top: 1px solid #d9e2ef;
            padding-top: 12px;
        }

        .student-directory .dataTables_wrapper .dataTables_filter label {
            margin: 3px;
            position: relative;
            font-size: 0;
        }

        .student-directory .dataTables_wrapper .dataTables_filter label::after {
            content: "\f002";
            font-family: FontAwesome;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa8b6;
            font-size: 12px;
            pointer-events: none;
        }

        .student-directory .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d9e2ef;
            border-radius: .25rem;
            padding: .25rem 32px .25rem .75rem;
            height: 26px;
            width: 240px;
            box-shadow: none;
        }

        .student-directory .dataTables_wrapper .follow-controls,
        .student-directory .dataTables_wrapper .follow-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .student-directory .dataTables_wrapper .follow-footer {
            margin-top: 10px;
            margin-bottom: 0;
            color: #54667a;
            font-size: 13px;
        }

        .student-records-note {
            margin-top: 6px;
            color: #7b8794;
            font-size: 12px;
        }
    </style>
@endpush

@push('scripts')
    <script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            $('#student-records-table').DataTable({
                processing: true,
                serverSide: true,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: "{{ route('student.records.index', ['scope' => $scope]) }}",
                order: [[7, 'desc']],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'roll_number', name: 'roll_number' },
                    { data: 'registration_number', name: 'registration_number' },
                    { data: 'campus_code', name: 'campus_code', orderable: false },
                    { data: 'program_name', name: 'program_name', orderable: false },
                    { data: 'batch_name', name: 'batch_name', orderable: false },
                    { data: 'admission_date', name: 'admission_date' },
                    { data: 'status_badge', name: 'student_status', orderable: false, searchable: false },
                    { data: 'certificate_status', name: 'certificate_status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right actions-cell' },
                ]
            });

            var statusMessage = @json(session('status'));
            if (statusMessage) {
                swal({
                    title: 'Success',
                    text: statusMessage,
                    type: 'success',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        });
    </script>
@endpush
