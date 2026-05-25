@php
    $authUser = auth()->user();
    $isAdmin = $authUser?->isAdmin() ?? false;

    $can = function (string ...$permissions) use ($authUser): bool {
        return $authUser?->hasAnyPermission($permissions) ?? false;
    };

    $canModule = function (string $moduleKey) use ($authUser): bool {
        return $authUser?->canAccessModule($moduleKey) ?? false;
    };

    $showLeadModule = $canModule('lead-management') || $canModule('training-leads') || $canModule('web-leads');
    $showRegistrationModule = $canModule('registration-management');
    $showAdmissionModule = $canModule('admission-management');
    $showStudentModule = $canModule('student-management');
    $showBatchModule = $canModule('batch-management');
    $showProgrammeModule = $canModule('programme-management');
    $showCampusModule = $canModule('campus-management');
    $showHrmModule = $canModule('human-resources');
    $showFinanceModule = $canModule('finance-management');
    $showInventoryModule = $canModule('inventory-management');
    $showCertificateModule = $canModule('certificate-management');
    $showUserModule = $canModule('user-management') || $canModule('role-management') || $canModule('permission-management');
@endphp

<div class="mobile-menu-left-overlay"></div>
<nav class="side-menu">
    <ul class="side-menu-list">
        @if($can('dashboard.view'))
            <li>
                <a href="{{ route('dashboard') }}">
                    <img class="font-icon-dashboard" src="img/navbarIcons/dashboard.png" alt="Dashboard">
                    <span class="lbl">Dashboard</span>
                </a>
            </li>
        @endif

        @if($showLeadModule)
            <li class="brown with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/enquiry.JPG" alt="Leads">
                    <span class="lbl">Leads Management</span>
                </span>
                <ul>
                    @if($can('lead.create'))
                        <li><a href="{{ route('leads.create') }}"><span class="lbl">Create New Lead</span></a></li>
                    @endif

                    @if($can('lead.followup.view') || $can('lead.view') || $can('lead.transfer.approve'))
                        <li class="with-sub">
                            <span>
                                <img class="font-icon-dashboard" src="img/navbarIcons/classroom.webp" alt="Training Leads">
                                <span class="lbl">Training Leads</span>
                            </span>
                            <ul>
                                @if($can('lead.followup.view'))
                                    <li><a href="{{ route('leads.followups') }}" class="stage-link"><span class="lbl">Lead's Follow-up</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_followups'] ?? 0)) }}</span></a></li>
                                @endif
                                @if($can('lead.view', 'lead.transfer.approve'))
                                    <li><a href="{{ route('leads.transfer') }}" class="stage-link"><span class="lbl">Transferred Leads</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_transfers'] ?? 0)) }}</span></a></li>
                                @endif
                                @if($can('lead.view'))
                                    <li><a href="{{ route('leads.index') }}" class="stage-link"><span class="lbl">All Leads</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_all_leads'] ?? 0)) }}</span></a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if($can('lead.followup.view'))
                        <li class="with-sub">
                            <span>
                                <img class="font-icon-dashboard" src="img/navbarIcons/meeting.webp" alt="Coworking Space">
                                <span class="lbl">Coworking Space</span>
                            </span>
                            <ul>
                                <li><a href="{{ route('leads.coworking.followups') }}" class="stage-link"><span class="lbl">Lead's Follow-up</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['coworking_followups'] ?? 0)) }}</span></a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="with-sub">
                        <span>
                            <img class="font-icon-dashboard" src="img/navbarIcons/content-managing.webp" alt="Exam Leads">
                            <span class="lbl">Exam Leads</span>
                        </span>
                        <ul>
                            <li><a href="#" class="stage-link"><span class="lbl">Lead's Follow-up</span><span class="label label-custom label-pill label-danger stage-count">28</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Website Leads</span><span class="label label-custom label-pill label-danger stage-count">210</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Today</span><span class="label label-custom label-pill label-danger stage-count">140</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Contacted</span><span class="label label-custom label-pill label-danger stage-count">100</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Needs Analysis</span><span class="label label-custom label-pill label-danger stage-count">65</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Branch Visited</span><span class="label label-custom label-pill label-danger stage-count">24</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Proposal &amp; Negotiation</span><span class="label label-custom label-pill label-danger stage-count">18</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Registered</span><span class="label label-custom label-pill label-danger stage-count">44</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Booked Exams</span><span class="label label-custom label-pill label-danger stage-count">30</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Not Interested</span><span class="label label-custom label-pill label-danger stage-count">16</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Transferred Leads</span><span class="label label-custom label-pill label-danger stage-count">7</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">All Leads</span><span class="label label-custom label-pill label-danger stage-count">712</span></a></li>
                        </ul>
                    </li>

                    <li class="with-sub">
                        <span>
                            <img class="font-icon-dashboard" src="img/navbarIcons/study-abroad.webp" alt="Study Abroad Leads">
                            <span class="lbl">Study Abroad Leads</span>
                        </span>
                        <ul>
                            <li><a href="#" class="stage-link"><span class="lbl">Lead's Follow-up</span><span class="label label-custom label-pill label-danger stage-count">32</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Website Leads</span><span class="label label-custom label-pill label-danger stage-count">180</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Today</span><span class="label label-custom label-pill label-danger stage-count">125</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Contacted</span><span class="label label-custom label-pill label-danger stage-count">90</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Needs Analysis</span><span class="label label-custom label-pill label-danger stage-count">58</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Branch Visited</span><span class="label label-custom label-pill label-danger stage-count">27</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Proposal &amp; Negotiation</span><span class="label label-custom label-pill label-danger stage-count">22</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Registered</span><span class="label label-custom label-pill label-danger stage-count">48</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Confirmed Visa Study</span><span class="label label-custom label-pill label-danger stage-count">19</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Not Interested</span><span class="label label-custom label-pill label-danger stage-count">21</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">Transferred Leads</span><span class="label label-custom label-pill label-danger stage-count">9</span></a></li>
                            <li><a href="#" class="stage-link"><span class="lbl">All Leads</span><span class="label label-custom label-pill label-danger stage-count">691</span></a></li>
                        </ul>
                    </li>

                    @if($can('web-lead.view'))
                        <li><a href="{{ route('web-leads.index') }}"><span class="lbl">Web Leads</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showRegistrationModule)
            <li class="purple with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/admission.webp" alt="Registration">
                    <span class="lbl">Registration Management</span>
                </span>
                <ul>
                    @if($can('registration.view'))
                        <li><a href="{{ route('registration.status') }}" class="stage-link"><span class="lbl">All Registration</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['all_registrations'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('voucher.preview') }}"><span class="lbl">Fee Voucher Preview</span></a></li>
                    @endif
                    @if($can('registration.create'))
                        <li><a href="{{ route('registration.create') }}"><span class="lbl">Create Registration</span></a></li>
                        <li><a href="{{ route('coworking-registrations.create') }}"><span class="lbl">Create Coworking Registration</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showAdmissionModule)
            <li class="gold orange with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/admissions.webp" alt="Admissions">
                    <span class="lbl">Admission Management</span>
                </span>
                <ul>
                    @if($can('admission.view'))
                        <li><a href="{{ route('admission.status') }}" class="stage-link"><span class="lbl">All Admissions</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['all_admissions'] ?? 0)) }}</span></a></li>
                    @endif
                    @if($can('admission.create'))
                        <li><a href="{{ route('admission.create') }}"><span class="lbl">Create Admission</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showStudentModule)
            <li class="magenta with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/students.webp" alt="Student Management">
                    <span class="lbl">Student Management</span>
                </span>
                <ul>
                    @if($can('student.view'))
                        <li><a href="{{ route('student.records.index', ['scope' => 'active']) }}" class="stage-link"><span class="lbl">Active</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_active'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'frozen']) }}" class="stage-link"><span class="lbl">Frozen</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_frozen'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'concluded']) }}" class="stage-link"><span class="lbl">Concluded</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_concluded'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'incomplete']) }}" class="stage-link"><span class="lbl">Incomplete</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_incomplete'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'suspended']) }}" class="stage-link"><span class="lbl">Suspended</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_suspended'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'admission_cancelled']) }}" class="stage-link"><span class="lbl">Cancelled</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_admission_cancelled'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'dropped']) }}" class="stage-link"><span class="lbl">Dropped</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_dropped'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'all_students']) }}" class="stage-link"><span class="lbl">All Students</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_all'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('student.records.index', ['scope' => 'alumni']) }}" class="stage-link"><span class="lbl">Alumni</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_alumni'] ?? 0)) }}</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showBatchModule)
            <li class="blue with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/batch-time.webp" alt="Batches">
                    <span class="lbl">Batches &amp; Time Table</span>
                </span>
                <ul>
                    @if($can('batch.create'))
                        <li><a href="{{ route('batch.create') }}" class="stage-link"><span class="lbl">Create Batch</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_create'] ?? 0)) }}</span></a></li>
                    @endif
                    @if($can('batch.view'))
                        <li><a href="{{ route('batch.index', ['scope' => 'upcoming']) }}" class="stage-link"><span class="lbl">Upcoming</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_upcoming'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('batch.index', ['scope' => 'recently_started']) }}" class="stage-link"><span class="lbl">Recently Started</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_recently_started'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('batch.index', ['scope' => 'in_progress']) }}" class="stage-link"><span class="lbl">In Progress</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_in_progress'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('batch.index', ['scope' => 'recently_ended']) }}" class="stage-link"><span class="lbl">Recently Ended</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_recently_ended'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('batch.index', ['scope' => 'completed']) }}" class="stage-link"><span class="lbl">Completed</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_completed'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('batch.index') }}" class="stage-link"><span class="lbl">All Batches</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_all'] ?? 0)) }}</span></a></li>
                    @endif
                    @if($can('batch-timetable.view'))
                        <li><a href="{{ route('batch.timetable.index') }}" class="stage-link"><span class="lbl">Manage Time Table</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_timetable'] ?? 0)) }}</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showProgrammeModule)
            <li class="green with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/courses.webp" alt="Programmes">
                    <span class="lbl">Programmes</span>
                </span>
                <ul>
                    @if($can('program.create'))
                        <li><a href="{{ route('program.create') }}" class="stage-link"><span class="lbl">Create Program</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_create'] ?? 0)) }}</span></a></li>
                    @endif
                    @if($can('program.view'))
                        <li><a href="{{ route('program.index', ['scope' => 'ongoing']) }}" class="stage-link"><span class="lbl">Ongoing</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_ongoing'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('program.index', ['scope' => 'suspended']) }}" class="stage-link"><span class="lbl">Suspended</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_suspended'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('program.index', ['scope' => 'discounted']) }}" class="stage-link"><span class="lbl">Discounted</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_discounted'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('program.index') }}" class="stage-link"><span class="lbl">All Programmes</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_all'] ?? 0)) }}</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showCampusModule)
            <li class="orange-red with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/campuses.webp" alt="Campuses">
                    <span class="lbl">Campuses / Franchise</span>
                </span>
                <ul>
                    @if($can('campus.create'))
                        <li><a href="{{ route('campus.create') }}" class="stage-link"><span class="lbl">Create Campus / Franchise</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_create'] ?? 0)) }}</span></a></li>
                    @endif
                    @if($can('campus.view'))
                        <li><a href="{{ route('campus.index', ['scope' => 'campuses']) }}" class="stage-link"><span class="lbl">All Campuses</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_company'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('campus.index', ['scope' => 'franchise']) }}" class="stage-link"><span class="lbl">All Franchise</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_franchise'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('campus.index', ['scope' => 'suspended_campuses']) }}" class="stage-link"><span class="lbl">Suspended Campuses</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_suspended_company'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('campus.index', ['scope' => 'suspended_franchise']) }}" class="stage-link"><span class="lbl">Suspended Franchise</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_suspended_franchise'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('campus.index') }}" class="stage-link"><span class="lbl">All Campuses / Franchise</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_all'] ?? 0)) }}</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showHrmModule)
            <li class="grey with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/humanresource.webp" alt="Human Resources">
                    <span class="lbl">Human Resources</span>
                </span>
                <ul>
                    @if($can('hrm_dashboard.view', 'hrm.dashboard.view'))
                        <li><a href="{{ route('hrm.dashboard') }}" class="stage-link"><span class="lbl">HRM Dashboard</span></a></li>
                    @endif
                    @if($can('hrm_employee.view', 'hrm_employee.create', 'hrm_employee.update', 'hrm_employee.manage_status', 'hrm.employee.view', 'hrm.employee.create', 'hrm.employee.update'))
                        <li><a href="{{ route('hrm.employees.index') }}" class="stage-link"><span class="lbl">Employee Master / Profile</span></a></li>
                    @endif
                    @if($can('hrm_department.view', 'hrm_department.create', 'hrm_designation.create', 'hrm_leave.manage_type', 'hrm_holiday.view', 'hrm_holiday.manage', 'hrm.master.view', 'hrm.master.create', 'hrm.master.update'))
                        <li><a href="{{ route('hrm.masters.index') }}" class="stage-link"><span class="lbl">Departments / Designations / Holidays</span></a></li>
                    @endif
                    @if($can('hrm_attendance.view', 'hrm_attendance.checkin', 'hrm_attendance.checkout', 'hrm_attendance.request', 'hrm_attendance.approve', 'hrm_attendance.import', 'hrm.attendance.view', 'hrm.attendance.create', 'hrm.attendance.update'))
                        <li><a href="{{ route('hrm.attendance.index') }}" class="stage-link"><span class="lbl">Attendance (Daily)</span></a></li>
                    @endif
                    @if($can('hrm_leave.view', 'hrm_leave.request', 'hrm_leave.approve', 'hrm_leave.manage_type', 'hrm_leave.manage_balance', 'hrm.leave.view', 'hrm.leave.create', 'hrm.leave.update'))
                        <li><a href="{{ route('hrm.leaves.index') }}" class="stage-link"><span class="lbl">Leave Management</span></a></li>
                    @endif
                    @if($can('hrm_payroll.view', 'hrm_payroll.process', 'hrm_payroll.close', 'hrm_payroll.manage_structure', 'hrm.payroll.view', 'hrm.payroll.create', 'hrm.payroll.update'))
                        <li><a href="{{ route('hrm.payroll.index') }}" class="stage-link"><span class="lbl">Payroll</span></a></li>
                    @endif
                    @if($can('hrm_shift.view', 'hrm_shift.manage', 'hrm_shift.assign', 'hrm.shift.view', 'hrm.shift.create', 'hrm.shift.update'))
                        <li><a href="{{ route('hrm.shifts.index') }}" class="stage-link"><span class="lbl">Shift / Timetable</span></a></li>
                    @endif
                    @if($can('hrm_announcement.view', 'hrm_announcement.create', 'hrm_announcement.publish', 'hrm.announcement.view', 'hrm.announcement.create', 'hrm.announcement.update'))
                        <li><a href="{{ route('hrm.announcements.index') }}" class="stage-link"><span class="lbl">Announcements / Notifications</span></a></li>
                    @endif
                    @if($can('hrm_document.view', 'hrm_document.upload', 'hrm_document.manage', 'hrm.document.view', 'hrm.document.create', 'hrm.document.update'))
                        <li><a href="{{ route('hrm.documents.index') }}" class="stage-link"><span class="lbl">Documents</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showFinanceModule)
            <li class="gold with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/expense.webp" alt="Finance Management">
                    <span class="lbl">Finance Management</span>
                </span>
                <ul>
                    @if($can('finance.dashboard.view'))
                        <li><a href="{{ route('finance.dashboard') }}" class="stage-link"><span class="lbl">Dashboard</span></a></li>
                    @endif

                    @if($can('finance.expense.view', 'finance.expense.create', 'finance.utility.view', 'finance.utility.create', 'finance.bill.view', 'finance.bill.create', 'finance.rent.view', 'finance.rent.create', 'finance.payroll.view', 'finance.payroll.create'))
                        <li class="with-sub">
                            <span><span class="lbl">Expense</span></span>
                            <ul>
                                @if($can('finance.expense.create'))
                                    <li><a href="{{ route('finance.expense.add') }}" class="stage-link"><span class="lbl">Add Expense</span></a></li>
                                    <li><a href="{{ route('finance.expense.types') }}" class="stage-link"><span class="lbl">Add Expense Type</span></a></li>
                                @endif

                                @if($can('finance.utility.view', 'finance.utility.create', 'finance.bill.view', 'finance.bill.create'))
                                    <li class="with-sub">
                                        <span><span class="lbl">Utility Bills</span></span>
                                        <ul>
                                            @if($can('finance.utility.create'))
                                                <li><a href="{{ route('finance.utility.pay') }}" class="stage-link"><span class="lbl">Pay Bill</span></a></li>
                                                <li><a href="{{ route('finance.utility.types') }}" class="stage-link"><span class="lbl">Add Bill Type</span></a></li>
                                            @endif
                                            @if($can('finance.bill.view', 'finance.bill.create'))
                                                <li><a href="{{ route('finance.utility.bills') }}" class="stage-link"><span class="lbl">Add New Bill</span></a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif

                                @if($can('finance.rent.view', 'finance.rent.create'))
                                    <li><a href="{{ route('finance.rent.index') }}" class="stage-link"><span class="lbl">Building Rent Setup</span></a></li>
                                @endif
                                @if($can('finance.expense.view', 'finance.rent.view'))
                                    <li><a href="{{ route('finance.expense.rent') }}" class="stage-link"><span class="lbl">Rent Expenses</span></a></li>
                                @endif
                                @if($can('finance.expense.view'))
                                    <li><a href="{{ route('finance.expense.marketing') }}" class="stage-link"><span class="lbl">Marketing</span></a></li>
                                    <li><a href="{{ route('finance.expense.assets') }}" class="stage-link"><span class="lbl">Asset Purchase</span></a></li>
                                    <li><a href="{{ route('finance.expense.all') }}" class="stage-link"><span class="lbl">All Expenses</span></a></li>
                                @endif
                                @if($can('finance.payroll.view', 'finance.payroll.create'))
                                    <li><a href="{{ route('finance.expense.payroll') }}" class="stage-link"><span class="lbl">Payroll</span></a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if($can('finance.payee.view', 'finance.payee.create'))
                        <li><a href="{{ route('finance.payees') }}" class="stage-link"><span class="lbl">Supplier &amp; Payee</span></a></li>
                    @endif
                    @if($can('finance.payable.view'))
                        <li><a href="{{ route('finance.payables') }}" class="stage-link"><span class="lbl">Payables</span></a></li>
                    @endif
                    @if($can('finance.receivable.view', 'finance.receivable.create'))
                        <li><a href="{{ route('finance.receivables') }}" class="stage-link"><span class="lbl">Receivables</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showInventoryModule)
            <li class="teal with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/courses.webp" alt="Inventory">
                    <span class="lbl">Inventory Management</span>
                </span>
                <ul>
                    @if($can('inventory.create'))
                        <li><a href="{{ route('inventory.create') }}" class="stage-link"><span class="lbl">Feed Campus Inventory</span></a></li>
                    @endif
                    @if($can('inventory.view'))
                        <li><a href="{{ route('inventory.index') }}" class="stage-link"><span class="lbl">Campus Stock Register</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showCertificateModule)
            <li class="blue with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/certificate.webp" alt="Certificate Management">
                    <span class="lbl">Certificate Management</span>
                </span>
                <ul>
                    @if($can('certificate.create'))
                        <li><a href="{{ route('certificate.create') }}" class="stage-link"><span class="lbl">Request Certificate</span></a></li>
                    @endif
                    @if($can('certificate.view'))
                        <li><a href="{{ route('certificate.index', ['scope' => 'requested']) }}" class="stage-link"><span class="lbl">Request for Approval</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_requested'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('certificate.index', ['scope' => 'approved']) }}" class="stage-link"><span class="lbl">Approved</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_approved'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('certificate.index', ['scope' => 'printing']) }}" class="stage-link"><span class="lbl">On Printing</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_printing'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('certificate.index', ['scope' => 'ready']) }}" class="stage-link"><span class="lbl">Ready</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_ready'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('certificate.index', ['scope' => 'delivered']) }}" class="stage-link"><span class="lbl">Delivered</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_delivered'] ?? 0)) }}</span></a></li>
                        <li><a href="{{ route('certificate.index') }}" class="stage-link"><span class="lbl">All Certificates</span><span class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_all'] ?? 0)) }}</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($showUserModule)
            <li class="green with-sub">
                <span>
                    <img class="font-icon-dashboard" src="img/navbarIcons/user.webp" alt="User Management">
                    <span class="lbl">User Management</span>
                </span>
                <ul>
                    @if($can('user.create'))
                        <li><a href="{{ route('users.create') }}"><span class="lbl">Create User</span></a></li>
                    @endif
                    @if($can('user.view'))
                        <li><a href="{{ route('users.index') }}"><span class="lbl">Users</span></a></li>
                    @endif
                    @if($can('role.create', 'role.manage'))
                        <li><a href="{{ route('roles.create') }}"><span class="lbl">Create Role</span></a></li>
                    @endif
                    @if($can('role.view', 'role.manage'))
                        <li><a href="{{ route('roles.index') }}"><span class="lbl">Roles</span></a></li>
                    @endif
                    @if($can('permission.create', 'permission.manage'))
                        <li><a href="{{ route('permissions.create') }}"><span class="lbl">Create Permission</span></a></li>
                    @endif
                    @if($can('permission.view', 'permission.manage'))
                        <li><a href="{{ route('permissions.index') }}"><span class="lbl">Permissions</span></a></li>
                    @endif
                    @if($can('user.view'))
                        <li><a href="{{ route('login-logs.index') }}"><span class="lbl">User Activities</span></a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if($isAdmin)
            <li class="gold orange">
                <a href="{{ route('admin.coming-soon', ['module' => 'event-management']) }}">
                    <img class="font-icon-dashboard" src="img/navbarIcons/event.webp" alt="Event Management">
                    <span class="lbl">Event Management</span>
                </a>
            </li>

            <li class="magenta">
                <a href="{{ route('admin.coming-soon', ['module' => 'marketing-management']) }}">
                    <img class="font-icon-dashboard" src="img/navbarIcons/marketing.webp" alt="Marketing Management">
                    <span class="lbl">Marketing Management</span>
                </a>
            </li>

            <li class="brown">
                <a href="{{ route('admin.coming-soon', ['module' => 'reports']) }}">
                    <img class="font-icon-dashboard" src="img/navbarIcons/reports.webp" alt="Reports">
                    <span class="lbl">Reports</span>
                </a>
            </li>

            <li class="brown">
                <a href="https://career.edu.pk/" target="_blank" rel="noopener noreferrer">
                    <img class="font-icon-dashboard" src="img/navbarIcons/website.webp" alt="Website">
                    <span class="lbl">Website</span>
                </a>
            </li>

            <li class="brown">
                <a href="https://www.career.edu.pk/website-admin-login" target="_blank" rel="noopener noreferrer">
                    <img class="font-icon-dashboard" src="img/navbarIcons/goto.webp" alt="Website Admin Panel">
                    <span class="lbl">Website Admin Panel</span>
                </a>
            </li>
        @endif
    </ul>
</nav>

<style>
    .label.label-pill.label-custom {
        padding: 5px 7px !important;
        font-size: 13px !important;
        width: auto !important;
        margin-right: 5px;
    }
</style>
