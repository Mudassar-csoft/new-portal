@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $studentScopeTabs = [
            'active' => 'Active',
            'frozen' => 'Frozen',
            'concluded' => 'Concluded',
            'incomplete' => 'Incomplete',
            'suspended' => 'Suspended',
            'admission_cancelled' => 'Cancelled',
            'dropped' => 'Dropped',
            'all_students' => 'All Students',
            'alumni' => 'Alumni',
        ];

        $studentBadgeColors = [
            'active' => 'badge-success',
            'frozen' => 'badge-info',
            'concluded' => 'badge-primary',
            'incomplete' => 'badge-warning',
            'suspended' => 'badge-warning',
            'admission_cancelled' => 'badge-danger',
            'dropped' => 'badge-danger',
            'all_students' => 'badge-secondary',
            'alumni' => 'badge-default',
        ];

        $activeStudentScope = $scope ?? 'active';
        $campusFilters = $campusFilters ?? [];
        $programFilters = $programFilters ?? [];
        $scopeCounts = $scopeCounts ?? [];
        $selectedCampusFilterId = $selectedCampusFilterId ?? null;
        $selectedProgramFilterId = $selectedProgramFilterId ?? null;
        $scopeLinkFilters = array_filter([
            'campus_id' => $selectedCampusFilterId,
            'program_id' => $selectedProgramFilterId,
        ], fn ($value) => filled($value));
    @endphp

    <div class="lead-status-shell">
        <div id="student-status-loader" class="follow-loader">
            <div class="follow-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading students...</p>
        </div>

        <div id="student-status-content" class="follow-content">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default student-directory">
                <div class="follow-tab-bar">
                    @foreach ($studentScopeTabs as $scopeKey => $scopeLabel)
                        @php
                            $isActive = $activeStudentScope === $scopeKey;
                            $count = $scopeCounts[$scopeKey] ?? 0;
                        @endphp
                        <a href="{{ route('student.records.index', array_merge(['scope' => $scopeKey], $scopeLinkFilters)) }}" class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $scopeKey }}">
                            <span class="label-text">{{ $scopeLabel }}</span>
                            <span class="badge {{ $studentBadgeColors[$scopeKey] ?? 'badge-secondary' }}">{{ number_format((int) $count) }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <div class="student-records-filter-bar">
                        <div class="student-records-filter-field">
                            <label class="student-records-filter-label" for="student-records-campus-filter">Campus</label>
                            <select id="student-records-campus-filter" class="form-control">
                                <option value="">All Campuses</option>
                                @foreach ($campusFilters as $campusFilter)
                                    <option value="{{ $campusFilter['id'] }}" @selected((int) $selectedCampusFilterId === (int) $campusFilter['id'])>{{ $campusFilter['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="student-records-filter-field">
                            <label class="student-records-filter-label" for="student-records-program-filter">Program</label>
                            <select id="student-records-program-filter" class="form-control">
                                <option value="">All Programs</option>
                                @foreach ($programFilters as $programFilter)
                                    <option value="{{ $programFilter['id'] }}" @selected((int) $selectedProgramFilterId === (int) $programFilter['id'])>{{ $programFilter['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="student-records-table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Student</th>
                                    <th>Roll No</th>
                                    <th>Course</th>
                                    <!-- <th>Registration No</th> -->
                                    <th>Campus</th>
                                    <th>Primary Contact</th>
                                    <!-- <th>Certificate</th> -->
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="student-transfer-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="campus_id" value="">
        <input type="hidden" name="batch_id" value="">
        <input type="hidden" name="remarks" value="">
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --dimension-student-records-index-1: 100vh;
            --dimension-student-records-index-2: 12px;
            --space-student-records-index-1: 0 !important;
            --space-student-records-index-2: 12px;
            --space-student-records-index-3: 4px !important;
            --space-student-records-index-4: 8px;
            --color-student-records-index-1: #54667a;
            --typo-student-records-index-font-size-1: 12px;
        }

        .lead-status-shell {
            position: relative;
            min-height: var(--dimension-student-records-index-1);
            width: 100%;
            overflow: visible;
        }

        .follow-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: var(--dimension-student-records-index-1);
            background: rgba(245, 247, 251, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10;
            gap: var(--space-student-records-index-2);
        }

        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-student-records-index-4); }

        .follow-spinner .dot {
            width: var(--dimension-student-records-index-2);
            height: var(--dimension-student-records-index-2);
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }

        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s;  background: #36b1ff; }

        .follow-loader p { margin: 0; color: var(--color-student-records-index-1); font-weight: 600; }

        .follow-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.students-ready .follow-content { opacity: 1; visibility: visible; }
        body.students-ready #student-status-loader { display: none; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
            40% { transform: translateY(-12px); opacity: 1; }
        }

        .student-directory {
            margin: 0 auto;
            position: relative;
            overflow: visible;
        }

        .student-directory .box-typical-body { overflow: visible; }
        .student-directory .table-responsive { overflow-x: visible; overflow-y: visible; }

        .student-records-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 12px;
        }

        .student-records-filter-field {
            flex: 1 1 240px;
            max-width: 320px;
        }

        .student-records-filter-label {
            display: block;
            margin-bottom: 6px;
            color: #4f5d73;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .student-records-filter-field .form-control {
            height: 36px;
            border: 1px solid #d7e5f1;
            border-radius: 10px;
            box-shadow: none;
            color: #425466;
            background: #fff;
        }

        .with-side-menu .page-content,
        .with-side-menu .page-content > .container-fluid,
        .student-directory .dataTables_wrapper,
        .student-directory .dataTables_wrapper .table-responsive {
            overflow: visible !important;
        }

        #student-records-table {
            margin-top: var(--space-student-records-index-4);
        }

        #student-records-table th,
        #student-records-table td {
            padding: 6px 10px;
            vertical-align: middle;
        }

        #student-records-table tbody tr:nth-of-type(odd) {
            background-color: #f9fbfd;
        }

        #student-records-table td.actions-cell {
            text-align: right;
            white-space: nowrap;
            position: relative;
        }

        #student-records-table td.actions-cell .dropdown,
        #student-records-table td.actions-cell .student-action-dropdown,
        #student-records-table td.actions-cell .follow-action-dropdown {
            /* position: relative;
            z-index: 1065; */
        }

        /* Dropdown opens DOWNWARD, right edge aligned with the Actions button */
        #student-records-table .student-action-dropdown .dropdown-menu,
        #student-records-table .follow-action-dropdown .dropdown-menu {
            display: none;
            min-width: 220px;
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            bottom: auto !important;
            margin-top: var(--space-student-records-index-3);
            margin-right: var(--space-student-records-index-1);
            transform: none !important;
            z-index: 1070 !important;
        }

        #student-records-table .student-action-dropdown.show .dropdown-menu,
        #student-records-table .follow-action-dropdown.show .dropdown-menu,
        #student-records-table .student-action-dropdown .dropdown-menu.show,
        #student-records-table .follow-action-dropdown .dropdown-menu.show {
            display: block !important;
        }

        /* Flip upward if no room below */
        #student-records-table .student-action-dropdown .dropdown-menu.dropdown-menu-upward,
        #student-records-table .follow-action-dropdown .dropdown-menu.dropdown-menu-upward {
            top: auto !important;
            bottom: 100% !important;
            right: 0 !important;
            left: auto !important;
            margin-top: var(--space-student-records-index-1);
            margin-bottom: var(--space-student-records-index-3);
        }

        /* DataTables wrappers must not clip the dropdown */
        .student-directory .dataTables_wrapper,
        .student-directory .dataTables_scrollBody,
        .student-directory .dataTables_scrollHead,
        #student-records-table_wrapper {
            overflow: visible !important;
        }

        .student-directory .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label {
            margin: 3px;
            position: relative;
            font-size: 0;
        }

        .student-directory .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label::after {
            content: "\f002";
            font-family: FontAwesome;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa8b6;
            font-size: var(--typo-student-records-index-font-size-1);
            pointer-events: none;
        }

        .student-directory .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input {
            border: 1px solid #d9e2ef;
            border-radius: .25rem;
            padding: .25rem 32px .25rem .75rem;
            height: 26px;
            width: 240px;
            box-shadow: none;
        }

        .student-directory .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
        .student-directory .dataTables_wrapper .follow-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-student-records-index-2);
        }

        .student-directory .dataTables_wrapper .follow-footer {
            margin-top: 10px;
            margin-bottom: 0;
            color: var(--color-student-records-index-1);
            font-size: 0.8125rem;
        }

        .student-records-note {
            margin-top: 6px;
            color: #7b8794;
            font-size: var(--typo-student-records-index-font-size-1);
        }

        .sweet-alert .swal-transfer-grid {
            text-align: left;
            margin-top: 14px;
        }

        .sweet-alert .swal-transfer-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }

        .sweet-alert .swal-transfer-col {
            flex: 1 1 240px;
            min-width: 0;
        }

        .sweet-alert .swal-transfer-label {
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: 700;
            color: #4f5d73;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sweet-alert .swal-transfer-input,
        .sweet-alert .swal-transfer-select,
        .sweet-alert .swal-transfer-textarea {
            width: 100%;
            border: 1px solid #d9e2ef;
            border-radius: 6px;
            box-sizing: border-box;
            padding: 10px 12px;
            font-size: 14px;
            color: #243447;
            background: #fff;
            box-shadow: none;
        }

        .sweet-alert .swal-transfer-input[disabled] {
            background: #f5f8fc;
            color: #5b6c80;
            cursor: not-allowed;
        }

        .sweet-alert .swal-transfer-textarea {
            min-height: 92px;
            resize: vertical;
        }

        .sweet-alert .swal-transfer-note {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.5;
            color: #66788a;
        }

        @media (max-width: 767px) {
            .student-records-filter-field {
                max-width: none;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        (function () {
            function revealStudentPage() {
                setTimeout(function () {
                    document.body.classList.add('students-ready');
                }, 150);
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealStudentPage();
            });
        })();

        $(function () {
            var transferForm = document.getElementById('student-transfer-form');
            var $campusFilter = $('#student-records-campus-filter');
            var $programFilter = $('#student-records-program-filter');
            var studentRecordsUrl = @json(route('student.records.index', ['scope' => $scope]));

            var recordsTable = $('#student-records-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
                ajax: {
                    url: studentRecordsUrl,
                    data: function (data) {
                        data.campus_id = $campusFilter.val() || '';
                        data.program_id = $programFilter.val() || '';
                    }
                },
                order: [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'roll_number', name: 'roll_number' },
                    { data: 'program_name', name: 'program_name', orderable: false },
                    { data: 'campus_code', name: 'campus_code', orderable: false },
                    { data: 'phone', name: 'phone' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right actions-cell' },
                ]
            });

            $campusFilter.on('change', function () {
                recordsTable.ajax.reload();
            });

            $programFilter.on('change', function () {
                recordsTable.ajax.reload();
            });

            // Dropdown opens directly below the button using exact pixel coordinates
            // (position: fixed bypasses any ancestor overflow / CSS conflicts).
            function closeAllStudentDropdowns() {
                $('.student-action-dropdown.show, .follow-action-dropdown.show').each(function () {
                    var $d = $(this);
                    $d.removeClass('show');
                    $d.children('.dropdown-toggle').attr('aria-expanded', 'false');
                    $d.children('.dropdown-menu').removeClass('show').removeAttr('style');
                });
            }

            $(document).on('click.studentRecords', '.student-action-dropdown .dropdown-toggle, .follow-action-dropdown .dropdown-toggle', function (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                var $toggle = $(this);
                var $dropdown = $toggle.closest('.student-action-dropdown, .follow-action-dropdown');
                var $menu = $dropdown.children('.dropdown-menu');

                if (!$menu.length) {
                    return;
                }

                var wasOpen = $dropdown.hasClass('show');
                closeAllStudentDropdowns();

                if (wasOpen) {
                    return; // second click closes
                }

                // Compute button position in viewport coordinates
                var rect = $toggle.get(0).getBoundingClientRect();
                var rightOffset = Math.max(0, window.innerWidth - Math.round(rect.right));
                var topPos = Math.round(rect.bottom) + 4;

                // Apply inline styles — highest specificity, can't be overridden
                $menu.attr('style',
                    'position:fixed !important;' +
                    'top:' + topPos + 'px !important;' +
                    'right:' + rightOffset + 'px !important;' +
                    'left:auto !important;' +
                    'bottom:auto !important;' +
                    'margin:0 !important;' +
                    'transform:none !important;' +
                    'min-width:220px !important;' +
                    'display:block !important;' +
                    'z-index:99999 !important;'
                );

                $dropdown.addClass('show');
                $menu.addClass('show');
                $toggle.attr('aria-expanded', 'true');
            });

            $(document).on('click.studentRecords', function (event) {
                if ($(event.target).closest('.student-action-dropdown, .follow-action-dropdown').length) {
                    return;
                }
                closeAllStudentDropdowns();
            });

            $(window).on('resize.studentRecords scroll.studentRecords', function () {
                closeAllStudentDropdowns();
            });

            $('#student-records-table').on('draw.dt', function () {
                closeAllStudentDropdowns();
            });

            function escapeHtml(value) {
                return $('<div>').text(value == null ? '' : String(value)).html();
            }

            function findBatchById(batches, id) {
                var normalized = String(id || '');

                for (var i = 0; i < batches.length; i += 1) {
                    if (String(batches[i].id) === normalized) {
                        return batches[i];
                    }
                }

                return null;
            }

            function populateTransferBatchSelect(batches, campusId, selectedBatchId, batchSelectId, timingInputId) {
                var batchSelect = document.getElementById(batchSelectId || 'swal-transfer-batch');
                var timingInput = document.getElementById(timingInputId || 'swal-transfer-timing');

                if (!batchSelect || !timingInput) {
                    return;
                }

                var filtered = (batches || []).filter(function (batch) {
                    return String(batch.campus_id) === String(campusId || '');
                });

                batchSelect.innerHTML = '';

                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = filtered.length ? 'Select batch' : 'No batch available for this campus';
                batchSelect.appendChild(placeholder);

                filtered.forEach(function (batch) {
                    var option = document.createElement('option');
                    option.value = batch.id;
                    option.textContent = batch.label || ('Batch #' + batch.id);
                    batchSelect.appendChild(option);
                });

                if (selectedBatchId && findBatchById(filtered, selectedBatchId)) {
                    batchSelect.value = String(selectedBatchId);
                } else if (filtered.length) {
                    batchSelect.value = String(filtered[0].id);
                }

                var activeBatch = findBatchById(filtered, batchSelect.value);
                timingInput.value = activeBatch ? (activeBatch.timing || 'Timing not set') : 'Timing not set';
            }

            function handoffSweetAlert(nextAction) {
                swal.close();

                window.setTimeout(function () {
                    nextAction();
                }, 180);
            }

            function openStudentTransferModal(meta, storeUrl) {
                var admission = meta && meta.admission ? meta.admission : {};
                var campuses = Array.isArray(meta && meta.campuses) ? meta.campuses : [];
                var batches = Array.isArray(meta && meta.batches) ? meta.batches : [];

                if (!batches.length) {
                    swal({
                        title: 'No Batch Found',
                        text: 'No transfer batch is available for this student program.',
                        type: 'warning'
                    });

                    return;
                }

                var currentCampusId = String(admission.current_campus_id || '');
                var currentCampusHasBatch = batches.some(function (batch) {
                    return String(batch.campus_id) === currentCampusId;
                });
                var defaultCampusId = currentCampusHasBatch
                    ? currentCampusId
                    : String((batches[0] && batches[0].campus_id) || '');

                var campusOptions = ['<option value="">Select campus</option>'];
                campuses.forEach(function (campus) {
                    campusOptions.push(
                        '<option value="' + escapeHtml(campus.id) + '"' + (String(campus.id) === defaultCampusId ? ' selected' : '') + '>' +
                        escapeHtml(campus.label || ('Campus #' + campus.id)) +
                        '</option>'
                    );
                });

                swal({
                    title: 'Transfer Campus & Batch',
                    text:
                        '<div class="swal-transfer-grid">' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Student Name</label>' +
                                    '<input id="swal-transfer-student" class="swal-transfer-input" value="' + escapeHtml(admission.student_name || 'N/A') + '" disabled>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Current Campus</label>' +
                                    '<input id="swal-transfer-current-campus" class="swal-transfer-input" value="' + escapeHtml(admission.current_campus || 'N/A') + '" disabled>' +
                                '</div>' +
                            '</div>' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Program</label>' +
                                    '<input id="swal-transfer-program" class="swal-transfer-input" value="' + escapeHtml(admission.program || 'N/A') + '" disabled>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Current Batch Code</label>' +
                                    '<input id="swal-transfer-current-batch" class="swal-transfer-input" value="' + escapeHtml(admission.current_batch || 'N/A') + '" disabled>' +
                                '</div>' +
                            '</div>' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Transfer Campus</label>' +
                                    '<select id="swal-transfer-campus" class="swal-transfer-select">' + campusOptions.join('') + '</select>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Batch Code</label>' +
                                    '<select id="swal-transfer-batch" class="swal-transfer-select"></select>' +
                                '</div>' +
                            '</div>' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Timing</label>' +
                                    '<input id="swal-transfer-timing" class="swal-transfer-input" value="' + escapeHtml(admission.current_timing || 'Timing not set') + '" disabled>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Remarks</label>' +
                                    '<textarea id="swal-transfer-remarks" class="swal-transfer-textarea" placeholder="Enter transfer remarks"></textarea>' +
                                '</div>' +
                            '</div>' +
                            '<p class="swal-transfer-note">Paid fee stays on the previous campus. Only pending fee moves to the selected campus.</p>' +
                        '</div>',
                    html: true,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    confirmButtonText: 'Transfer',
                    cancelButtonText: 'Cancel'
                }, function () {
                    var campusId = $('#swal-transfer-campus').val();
                    var batchId = $('#swal-transfer-batch').val();
                    var remarks = ($('#swal-transfer-remarks').val() || '').trim();

                    if (!campusId) {
                        swal.showInputError('Please select a transfer campus.');
                        return false;
                    }

                    if (!batchId) {
                        swal.showInputError('Please select a batch code.');
                        return false;
                    }

                    if (!transferForm) {
                        swal.close();
                        return false;
                    }

                    transferForm.setAttribute('action', storeUrl);
                    transferForm.querySelector('input[name="campus_id"]').value = campusId;
                    transferForm.querySelector('input[name="batch_id"]').value = batchId;
                    transferForm.querySelector('input[name="remarks"]').value = remarks;

                    swal.close();
                    transferForm.submit();
                });

                setTimeout(function () {
                    var campusSelect = document.getElementById('swal-transfer-campus');
                    var batchSelect = document.getElementById('swal-transfer-batch');

                    if (!campusSelect || !batchSelect) {
                        return;
                    }

                    populateTransferBatchSelect(batches, campusSelect.value, '', 'swal-transfer-batch', 'swal-transfer-timing');

                    campusSelect.addEventListener('change', function () {
                        populateTransferBatchSelect(batches, campusSelect.value, '', 'swal-transfer-batch', 'swal-transfer-timing');
                    });

                    batchSelect.addEventListener('change', function () {
                        var timingInput = document.getElementById('swal-transfer-timing');
                        var selectedBatch = findBatchById(batches, batchSelect.value);

                        if (timingInput) {
                            timingInput.value = selectedBatch ? (selectedBatch.timing || 'Timing not set') : 'Timing not set';
                        }
                    });
                }, 0);
            }

            function openStudentReenrollModal(meta, storeUrl) {
                var admission = meta && meta.admission ? meta.admission : {};
                var campuses = Array.isArray(meta && meta.campuses) ? meta.campuses : [];
                var batches = Array.isArray(meta && meta.batches) ? meta.batches : [];

                if (!batches.length) {
                    swal({
                        title: 'No Batch Found',
                        text: 'No batch is available for this student program right now.',
                        type: 'warning'
                    });

                    return;
                }

                var currentCampusId = String(admission.current_campus_id || '');
                var currentBatchId = String(admission.current_batch_id || '');
                var currentCampusHasBatch = batches.some(function (batch) {
                    return String(batch.campus_id) === currentCampusId;
                });
                var defaultCampusId = currentCampusHasBatch
                    ? currentCampusId
                    : String((batches[0] && batches[0].campus_id) || '');

                var campusOptions = ['<option value="">Select campus</option>'];
                campuses.forEach(function (campus) {
                    campusOptions.push(
                        '<option value="' + escapeHtml(campus.id) + '"' + (String(campus.id) === defaultCampusId ? ' selected' : '') + '>' +
                        escapeHtml(campus.label || ('Campus #' + campus.id)) +
                        '</option>'
                    );
                });

                swal({
                    title: 'Enroll Now',
                    text:
                        '<div class="swal-transfer-grid">' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Student Name</label>' +
                                    '<input id="swal-reenroll-student" class="swal-transfer-input" value="' + escapeHtml(admission.student_name || 'N/A') + '" disabled>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Previous Campus</label>' +
                                    '<input id="swal-reenroll-campus-label" class="swal-transfer-input" value="' + escapeHtml(admission.current_campus || 'N/A') + '" disabled>' +
                                '</div>' +
                            '</div>' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Previous Program</label>' +
                                    '<input id="swal-reenroll-program" class="swal-transfer-input" value="' + escapeHtml(admission.program || 'N/A') + '" disabled>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Previous Batch</label>' +
                                    '<input id="swal-reenroll-batch-label" class="swal-transfer-input" value="' + escapeHtml(admission.current_batch || 'N/A') + '" disabled>' +
                                '</div>' +
                            '</div>' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Select Campus</label>' +
                                    '<select id="swal-reenroll-campus" class="swal-transfer-select">' + campusOptions.join('') + '</select>' +
                                '</div>' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Batch Code</label>' +
                                    '<select id="swal-reenroll-batch" class="swal-transfer-select"></select>' +
                                '</div>' +
                            '</div>' +
                            '<div class="swal-transfer-row">' +
                                '<div class="swal-transfer-col">' +
                                    '<label class="swal-transfer-label">Batch Timing</label>' +
                                    '<input id="swal-reenroll-timing" class="swal-transfer-input" value="' + escapeHtml(admission.current_timing || 'Timing not set') + '" disabled>' +
                                '</div>' +
                            '</div>' +
                            '<p class="swal-transfer-note">Any pending, bad debt, or cancelled fee row will be set to pending after enrollment.</p>' +
                         '</div>',
                    html: true,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    confirmButtonText: 'Enroll Now',
                    cancelButtonText: 'Cancel'
                }, function () {
                    var campusId = $('#swal-reenroll-campus').val();
                    var batchId = $('#swal-reenroll-batch').val();

                    if (!campusId) {
                        swal.showInputError('Please select a campus.');
                        return false;
                    }

                    if (!batchId) {
                        swal.showInputError('Please select a batch code.');
                        return false;
                    }

                    if (!transferForm) {
                        swal.close();
                        return false;
                    }

                    transferForm.setAttribute('action', storeUrl);
                    transferForm.querySelector('input[name="campus_id"]').value = campusId;
                    transferForm.querySelector('input[name="batch_id"]').value = batchId;
                    transferForm.querySelector('input[name="remarks"]').value = '';

                    swal.close();
                    transferForm.submit();
                });

                setTimeout(function () {
                    var campusSelect = document.getElementById('swal-reenroll-campus');
                    var batchSelect = document.getElementById('swal-reenroll-batch');

                    if (!campusSelect || !batchSelect) {
                        return;
                    }

                    populateTransferBatchSelect(batches, campusSelect.value, currentBatchId, 'swal-reenroll-batch', 'swal-reenroll-timing');

                    campusSelect.addEventListener('change', function () {
                        populateTransferBatchSelect(batches, campusSelect.value, '', 'swal-reenroll-batch', 'swal-reenroll-timing');
                    });

                    batchSelect.addEventListener('change', function () {
                        var timingInput = document.getElementById('swal-reenroll-timing');
                        var selectedBatch = findBatchById(batches, batchSelect.value);

                        if (timingInput) {
                            timingInput.value = selectedBatch ? (selectedBatch.timing || 'Timing not set') : 'Timing not set';
                        }
                    });
                }, 0);
            }

            $(document).on('click.studentTransfer', '.js-student-transfer:not([disabled])', function (event) {
                event.preventDefault();

                var metaUrl = $(this).data('transfer-meta-url');
                var storeUrl = $(this).data('transfer-store-url');

                if (!metaUrl || !storeUrl) {
                    return;
                }

                closeAllStudentDropdowns();

                swal({
                    title: 'Loading transfer details...',
                    text: 'Please wait.',
                    type: 'info',
                    showConfirmButton: false
                });

                $.getJSON(metaUrl)
                    .done(function (response) {
                        handoffSweetAlert(function () {
                            openStudentTransferModal(response, storeUrl);
                        });
                    })
                    .fail(function (xhr) {
                        var message = 'Unable to load transfer details right now.';

                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        handoffSweetAlert(function () {
                            swal({
                                title: 'Error',
                                text: message,
                                type: 'error'
                            });
                        });
                    });
            });

            $(document).on('click.studentReenroll', '.js-student-reenroll:not([disabled])', function (event) {
                event.preventDefault();

                var metaUrl = $(this).data('reenroll-meta-url');
                var storeUrl = $(this).data('reenroll-store-url');

                if (!metaUrl || !storeUrl) {
                    return;
                }

                closeAllStudentDropdowns();

                swal({
                    title: 'Loading enrollment details...',
                    text: 'Please wait.',
                    type: 'info',
                    showConfirmButton: false
                });

                $.getJSON(metaUrl)
                    .done(function (response) {
                        handoffSweetAlert(function () {
                            openStudentReenrollModal(response, storeUrl);
                        });
                    })
                    .fail(function (xhr) {
                        var message = 'Unable to load enrollment details right now.';

                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        handoffSweetAlert(function () {
                            swal({
                                title: 'Error',
                                text: message,
                                type: 'error'
                            });
                        });
                    });
            });

            $(document).on('submit.studentDrop', '.js-student-drop-form', function (event) {
                event.preventDefault();

                var form = this;
                var hiddenReasonInput = form.querySelector('input[name="drop_reason"]');

                if (!hiddenReasonInput) {
                    form.submit();
                    return;
                }

                closeAllStudentDropdowns();

                swal({
                    title: 'Drop Student',
                    text:
                        '<div class="swal-transfer-grid">' +
                            '<label class="swal-transfer-label" for="swal-drop-reason">Reason for dropping ' + escapeHtml($(form).data('student-name') || 'this student') + '</label>' +
                            '<textarea id="swal-drop-reason" class="swal-transfer-textarea" placeholder="Enter drop reason"></textarea>' +
                        '</div>',
                    html: true,
                    type: 'warning',
                    showCancelButton: true,
                    closeOnConfirm: false,
                    confirmButtonText: 'Drop Student',
                    cancelButtonText: 'Cancel'
                }, function () {
                    var reasonField = document.getElementById('swal-drop-reason');
                    var reason = reasonField ? reasonField.value.trim() : '';

                    if (!reason) {
                        swal.showInputError('Please enter the drop reason.');
                        return false;
                    }

                    hiddenReasonInput.value = reason;
                    swal.close();
                    form.submit();
                });
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

            var errorMessage = @json(session('error') ?: ($errors->any() ? $errors->first() : null));
            if (errorMessage) {
                swal({
                    title: 'Error',
                    text: errorMessage,
                    type: 'error'
                });
            }
        });
    </script>
@endpush
