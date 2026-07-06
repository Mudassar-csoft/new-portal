<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Student Portal - Career Institute</title>

    <link href="{{ asset('theme/img/favicon.144x144.png') }}" rel="apple-touch-icon" type="image/png" sizes="144x144">
    <link href="{{ asset('theme/img/favicon.114x114.png') }}" rel="apple-touch-icon" type="image/png" sizes="114x114">
    <link href="{{ asset('theme/img/favicon.72x72.png') }}" rel="apple-touch-icon" type="image/png" sizes="72x72">
    <link href="{{ asset('theme/img/favicon.57x57.png') }}" rel="apple-touch-icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.ico') }}" rel="shortcut icon">

    <link rel="stylesheet" href="{{ asset('theme/lib/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/main.css') }}">
    <style>
        :root {
            --dimension-student-portal-1: 100%;
            --dimension-student-portal-2: 160px;
            --dimension-student-portal-3: 96px;
            --dimension-student-portal-4: none;
            --space-student-portal-1: 12px;
            --space-student-portal-2: 16px;
            --space-student-portal-3: 24px;
            --space-student-portal-4: 4px;
            --space-student-portal-5: 6px;
            --color-student-portal-1: #00a8ff;
            --color-student-portal-2: #1f2d3d;
            --color-student-portal-3: #41546a;
            --color-student-portal-4: #6b7a90;
            --color-student-portal-5: #e6edf5;
            --color-student-portal-6: #f2f5f9;
            --color-student-portal-7: #fff;
            --color-student-portal-8: rgba(0, 0, 0, 0.08);
        }

        :root {
            --dimension-student-portal-1: 100%;
            --dimension-student-portal-2: 160px;
            --dimension-student-portal-3: 96px;
            --dimension-student-portal-4: none;
            --space-student-portal-1: 12px;
            --space-student-portal-2: 16px;
            --space-student-portal-3: 24px;
            --space-student-portal-4: 4px;
            --space-student-portal-5: 6px;
            --typo-student-portal-font-size-1: 12px;
            --typo-student-portal-font-size-2: 20px;
            --typo-student-portal-font-weight-3: 600;
        }0___

         * {
    font-family: 'Proxima Nova', sans-serif;
    font-size: var(--typo-student-portal-font-size-1);
    margin: 0;
    padding: 0;

}
        body {
            background: var(--color-student-portal-6);
        }

        .portal-wrap {
            padding: 24px 24px 40px;
        }

        .portal-header {
            background: var(--color-student-portal-7)fff;
            border-radius: 12px;
            box-shadow: 0 10px 24px var(--color-student-portal-8);
            padding: 16px 20px;
            margin-bottom: var(--space-student-portal-3);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: var(--space-student-portal-1);
        }

        .portal-brand img {
            height: 40px;
            width: auto;
        }

        .portal-title {
            font-size: var(--typo-student-portal-font-size-2);
            font-weight: 700;
            color: var(--color-student-portal-2);
        }

        .portal-subtitle {
            font-size: var(--typo-student-portal-font-size-1);
            color: var(--color-student-portal-4);
        }

        .portal-header-actions {
            display: flex;
            align-items: center;
            gap: var(--space-student-portal-1);
        }

        .portal-header-actions .btn {
            padding: 6px 14px;
        }

        .portal-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: var(--space-student-portal-3);
        }

        .student-card {
            background: var(--color-student-portal-7);
            border-radius: 12px;
            box-shadow: 0 10px 24px var(--color-student-portal-8);
            overflow: hidden;
        }

        .student-cover {
            height: var(--dimension-student-portal-2);
            background-color: var(--color-student-portal-1);
        }

        .student-avatar {
            width: var(--dimension-student-portal-3);
            height: var(--dimension-student-portal-3);
            border-radius: 50%;
            border: 4px solid var(--color-student-portal-7);
            background: #eef4fb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -48px auto 12px;
            overflow: hidden;
        }

        .student-avatar img {
            width: var(--dimension-student-portal-1);
            height: var(--dimension-student-portal-1);
            object-fit: cover;
        }

        .student-info {
            text-align: center;
            padding: 0 16px 18px;
        }

        .student-name {
            font-size: var(--typo-student-portal-font-size-2);
            font-weight: var(--typo-student-portal-font-weight-3);
            margin-bottom: var(--space-student-portal-4);
        }

        .student-phone {
            color: var(--color-student-portal-4);
            margin-bottom: var(--space-student-portal-2);
        }

        .student-actions {
            margin-bottom: var(--space-student-portal-2);
        }

        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: var(--dimension-student-portal-2);
            background: var(--color-student-portal-7);
            border: 1px solid var(--color-student-portal-5);
            border-radius: 10px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
            padding: 8px 0;
            display: none;
            z-index: 5;
        }

        .action-menu.is-open {
            display: block;
        }

        .action-item {
            display: block;
            padding: 10px 16px;
            color: var(--color-student-portal-2);
            text-decoration: none;
            font-weight: var(--typo-student-portal-font-weight-3);
        }

        .action-item:hover {
            background: var(--color-student-portal-6);
            color: var(--color-student-portal-2);
            text-decoration: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid var(--color-student-portal-5);
        }

        .stat-item {
            padding: 12px 8px;
            text-align: center;
            border-right: 1px solid var(--color-student-portal-5);
        }

        .stat-item:last-child {
            border-right: 0;
        }

        .stat-label {
            font-size: var(--typo-student-portal-font-size-1);
            color: var(--color-student-portal-4);
            margin-bottom: var(--space-student-portal-4);
        }

        .stat-value {
            font-size: 1rem;
            font-weight: var(--typo-student-portal-font-weight-3);
            color: var(--color-student-portal-2);
        }

        .portal-tabs {
            background: var(--color-student-portal-7);
            border-radius: 12px;
            box-shadow: 0 10px 24px var(--color-student-portal-8);
            padding: 16px 20px 20px;
        }

        .tab-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .tab-link {
            text-align: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: var(--typo-student-portal-font-weight-3);
            color: var(--color-student-portal-3);
            background: var(--color-student-portal-6);
            cursor: pointer;
            border: 1px solid transparent;
        }
        .bootstrap-table .table thead th, .fixed-table-body .table thead th, .table thead th {
    border-bottom: none;
    padding-top: var(--space-student-portal-5);
    padding-bottom: var(--space-student-portal-5);

}
.table td {
    padding-bottom: 5px;
}
        .tab-link.is-active {
            background: var(--color-student-portal-1);
            color: var(--color-student-portal-7);
            border-color: var(--color-student-portal-1);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.is-active {
            display: block;
        }

        .table thead th {
            background: var(--color-student-portal-1);
            color: var(--color-student-portal-7);
            border: 0;
            font-weight: var(--typo-student-portal-font-weight-3);
            font-size: 0.8125rem;
            text-transform: uppercase;
        }

        .portal-tabs .table {
            width: auto;
            min-width: var(--dimension-student-portal-1);
            max-width: var(--dimension-student-portal-4);
            table-layout: auto;
        }

        .portal-tabs .table th,
        .portal-tabs .table td {
            width: auto;
            min-width: 0;
            max-width: var(--dimension-student-portal-4);
        }

        .badge-status {
            background: var(--color-student-portal-1);
            color: var(--color-student-portal-7);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: var(--typo-student-portal-font-size-1);
            font-weight: var(--typo-student-portal-font-weight-3);
        }

        .info-list {
            width: var(--dimension-student-portal-1);
            border: 1px solid var(--color-student-portal-5);
            border-radius: 10px;
            overflow: hidden;
        }

        .info-row {
            display: grid;
            grid-template-columns: 220px 1fr;
            padding: 8px 16px;
            border-bottom: 1px solid var(--color-student-portal-5);
            background: var(--color-student-portal-7);
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-label {
            font-weight: var(--typo-student-portal-font-weight-3);
            color: var(--color-student-portal-2);
        }

        .info-value {
            color: var(--color-student-portal-3);
        }

        @media (max-width: 992px) {
            .portal-grid {
                grid-template-columns: 1fr;
            }

            .info-row {
                grid-template-columns: 1fr;
                gap: var(--space-student-portal-5);
            }
        }
    </style>
</head>
<body>
    @php
        $shortcutLabels = [
            'attendance' => 'Attendance',
            'active' => 'Active Students',
            'frozen' => 'Frozen Students',
            'concluded' => 'Concluded Students',
            'incomplete' => 'Incomplete Students',
            'suspended' => 'Suspended Students',
            'admission_cancelled' => 'Cancelled',
            'dropped' => 'Dropped Students',
            'all_students' => 'All Students',
            'alumni' => 'Alumni',
        ];
        $selectedShortcut = $shortcutLabels[request('section')] ?? null;
    @endphp
    <div class="portal-wrap">
        <div class="portal-header">
            <div class="portal-brand">
                <img src="{{ asset('theme/img/career-institute-logo.webp') }}" alt="Career Institute">
                <div>
                    <div class="portal-title">Career Institute</div>
                    <div class="portal-subtitle">
                        Student Portal{{ $selectedShortcut ? ' | ' . $selectedShortcut : '' }}
                    </div>
                </div>
            </div>
            <div class="portal-header-actions">
                <button class="btn btn-primary btn-rounded">Support</button>
            </div>
        </div>

        <div class="portal-grid">
            <div class="student-card">
                <div class="student-cover"></div>
                <div class="student-avatar">
                    <img src="{{ asset('theme/img/avatar-2-64.png') }}" alt="Student">
                </div>
                <div class="student-info">
                    <div class="student-name">Muhammad Bilal</div>
                    <div class="student-phone">0314 842 2262</div>
                    <div class="student-actions">
                        <div class="action-dropdown">
                            <button class="btn btn-primary btn-rounded" type="button" id="actionToggle">Action</button>
                            <div class="action-menu" id="actionMenu">
                                <a class="action-item" href="#">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-label">Total Fee</div>
                        <div class="stat-value">26000</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Pending Fee</div>
                        <div class="stat-value">0</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Total Course</div>
                        <div class="stat-value">1</div>
                    </div>
                </div>
            </div>

            <div class="portal-tabs">
                <div class="tab-links">
                    <div class="tab-link is-active" data-tab="admission">Admission History</div>
                    <div class="tab-link" data-tab="account">Account History</div>
                    <div class="tab-link" data-tab="personal">Personal Information</div>
                </div>

                <div class="tab-panel is-active" id="admission">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Course Title</th>
                                    <th>Roll Number</th>
                                    <th>Fee Status</th>
                                    <th>Total Fee</th>
                                    <th>Batch History</th>
                                    <th>Campus History</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Microsoft Office Management</td>
                                    <td>CIFSD02-OMT11-26-01</td>
                                    <td><span class="badge-status">Clear</span></td>
                                    <td>24000</td>
                                    <td><a href="#">View Batch History</a></td>
                                    <td><a href="#">View Campus History</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-panel" id="account">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Course Title</th>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Installment</th>
                                    <th>Fee Status</th>
                                    <th>Due Date</th>
                                    <th>Collected At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Registration Fee</td>
                                    <td>Registration</td>
                                    <td>2000</td>
                                    <td>0</td>
                                    <td><span class="badge-status">Paid</span></td>
                                    <td>2026-01-16</td>
                                    <td>2026-01-16</td>
                                </tr>
                                <tr>
                                    <td>Microsoft Office Management</td>
                                    <td>Full Fee</td>
                                    <td>0</td>
                                    <td>24000</td>
                                    <td><span class="badge-status">Paid</span></td>
                                    <td>2026-01-16</td>
                                    <td>2026-01-16</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-panel" id="personal">
                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-label">Contact</div>
                            <div class="info-value">0314 842 2262</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">18-Feb-2007</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">CNIC</div>
                            <div class="info-value">3310271521027</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Registration No</div>
                            <div class="info-value">CIFSD02-0126-1059</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Guardian Name</div>
                            <div class="info-value">Ghulam Sabir</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Postal Address</div>
                            <div class="info-value">RazaAbad Bazar No #2 Gulzar Colony Street #7</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Gender</div>
                            <div class="info-value">Male</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Qualification</div>
                            <div class="info-value">FA(IT)</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email Address</div>
                            <div class="info-value">ranabilal1234@gmail.com</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Registration Date</div>
                            <div class="info-value">16-Jan-2026</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('theme/js/lib/jquery/jquery-3.2.1.min.js') }}"></script>
    <script>
        (function () {
            var links = document.querySelectorAll('.tab-link');
            var panels = document.querySelectorAll('.tab-panel');

            var setActive = function (name) {
                links.forEach(function (link) {
                    link.classList.toggle('is-active', link.dataset.tab === name);
                });
                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.id === name);
                });
            };

            links.forEach(function (link) {
                link.addEventListener('click', function () {
                    setActive(link.dataset.tab);
                });
            });

            var actionToggle = document.getElementById('actionToggle');
            var actionMenu = document.getElementById('actionMenu');

            if (actionToggle && actionMenu) {
                actionToggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    actionMenu.classList.toggle('is-open');
                });

                document.addEventListener('click', function () {
                    actionMenu.classList.remove('is-open');
                });
            }
        })();
    </script>
</body>
</html>
