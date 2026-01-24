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
        body {
            background: #f2f5f9;
        }

        .portal-wrap {
            padding: 24px 24px 40px;
        }

        .portal-header {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .portal-brand img {
            height: 40px;
            width: auto;
        }

        .portal-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2d3d;
        }

        .portal-subtitle {
            font-size: 12px;
            color: #6b7a90;
        }

        .portal-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .portal-header-actions .btn {
            padding: 6px 14px;
        }

        .portal-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
        }

        .student-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .student-cover {
            height: 160px;
            background: linear-gradient(135deg, #0e7ad1, #6fd0ff);
        }

        .student-avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 4px solid #fff;
            background: #eef4fb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -48px auto 12px;
            overflow: hidden;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-info {
            text-align: center;
            padding: 0 16px 18px;
        }

        .student-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .student-phone {
            color: #6b7a90;
            margin-bottom: 16px;
        }

        .student-actions {
            margin-bottom: 16px;
        }

        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 160px;
            background: #fff;
            border: 1px solid #e6edf5;
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
            color: #1f2d3d;
            text-decoration: none;
            font-weight: 600;
        }

        .action-item:hover {
            background: #f2f5f9;
            color: #1f2d3d;
            text-decoration: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid #e6edf5;
        }

        .stat-item {
            padding: 12px 8px;
            text-align: center;
            border-right: 1px solid #e6edf5;
        }

        .stat-item:last-child {
            border-right: 0;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7a90;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 600;
            color: #1f2d3d;
        }

        .portal-tabs {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
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
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #41546a;
            background: #f2f5f9;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .tab-link.is-active {
            background: #0e7ad1;
            color: #fff;
            border-color: #0e7ad1;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.is-active {
            display: block;
        }

        .table thead th {
            background: #0e7ad1;
            color: #fff;
            border: 0;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }

        .badge-status {
            background: #19a974;
            color: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .info-list {
            width: 100%;
            border: 1px solid #e6edf5;
            border-radius: 10px;
            overflow: hidden;
        }

        .info-row {
            display: grid;
            grid-template-columns: 220px 1fr;
            padding: 12px 16px;
            border-bottom: 1px solid #e6edf5;
            background: #fff;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #1f2d3d;
        }

        .info-value {
            color: #41546a;
        }

        @media (max-width: 992px) {
            .portal-grid {
                grid-template-columns: 1fr;
            }

            .info-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="portal-wrap">
        <div class="portal-header">
            <div class="portal-brand">
                <img src="{{ asset('theme/img/career-institute-logo.webp') }}" alt="Career Institute">
                <div>
                    <div class="portal-title">Career Institute</div>
                    <div class="portal-subtitle">Student Portal</div>
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
                                    <th>Registration</th>
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
