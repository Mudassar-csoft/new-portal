
<div class="mobile-menu-left-overlay"></div>
	<nav class="side-menu">
		<ul class="side-menu-list">
			<li>
				<a href="{{ route('dashboard') }}">
					<img class="font-icon-dashboard" src="img/navbarIcons/dashboard.png" alt="Dashboard">
					<span class="lbl">Dashboard</span>
				</a>
			</li>
			<li class="brown with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/enquiry.JPG" alt="Leads">
					<span class="lbl">Leads Management</span></span>
				<ul>
					<li><a href="{{ route('leads.create') }}"><span class="lbl">Create New Lead</span></a></li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/classroom.webp" alt="Dashboard">
							<span class="lbl">Training Leads</span>
						</span>
						<ul>
							<li><a href="{{ route('leads.followups') }}" class="stage-link"><span class="lbl">Lead's Follow-up</span><span
										class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_followups'] ?? 0)) }}</span></a>
							</li>
							<li><a href="{{ route('leads.transfer') }}" class="stage-link"><span class="lbl">Transferred Leads</span><span
										class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_transfers'] ?? 0)) }}</span></a>
							</li>
							<li><a href="{{ route('leads.index') }}" class="stage-link"><span class="lbl">All Leads</span><span
										class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_all_leads'] ?? 0)) }}</span></a>
							</li>
							<!-- <li><a href="{{ route('web-leads.index') }}" class="stage-link"><span class="lbl">Web Leads</span><span
										class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['training_web_leads'] ?? 0)) }}</span></a>
							</li> -->

						</ul>
					</li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/meeting.webp" alt="CO leads">
							<span class="lbl">Coworking Space</span>
						</span>
						<ul>
							<li><a href="{{ route('leads.coworking.followups') }}" class="stage-link"><span class="lbl">Lead's Follow-up</span><span
										class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['coworking_followups'] ?? 0)) }}</span></a>
							</li>
						</ul>
					</li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/content-managing.webp"
								alt="Exam Leads">
							<span class="lbl">Exam Leads</span>
						</span>
						<ul>
							<li><a href="#" class="stage-link"><span class="lbl">Lead's Follow-up</span><span
										class="label label-custom label-pill label-danger stage-count">28</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Website Leads</span><span
										class="label label-custom label-pill label-danger stage-count">210</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Today</span><span
										class="label label-custom label-pill label-danger stage-count">140</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Contacted</span><span
										class="label label-custom label-pill label-danger stage-count">100</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Needs Analysis</span><span
										class="label label-custom label-pill label-danger stage-count">65</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Branch Visited</span><span
										class="label label-custom label-pill label-danger stage-count">24</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Proposal &amp; Negotiation</span><span
										class="label label-custom label-pill label-danger stage-count">18</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Registered</span><span
										class="label label-custom label-pill label-danger stage-count">44</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Booked Exams</span><span
										class="label label-custom label-pill label-danger stage-count">30</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Not Interested</span><span
										class="label label-custom label-pill label-danger stage-count">16</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Transferred Leads</span><span
										class="label label-custom label-pill label-danger stage-count">7</span></a></li>
							<li><a href="#" class="stage-link"><span class="lbl">All Leads</span><span
										class="label label-custom label-pill label-danger stage-count">712</span></a>
							</li>

						</ul>
					</li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/study-abroad.webp" alt="SA Leads">
							<span class="lbl">Study Abroad Leads</span>
						</span>
						<ul>
							<li><a href="#" class="stage-link"><span class="lbl">Lead's Follow-up</span><span
										class="label label-custom label-pill label-danger stage-count">32</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Website Leads</span><span
										class="label label-custom label-pill label-danger stage-count">180</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Today</span><span
										class="label label-custom label-pill label-danger stage-count">125</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Contacted</span><span
										class="label label-custom label-pill label-danger stage-count">90</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Needs Analysis</span><span
										class="label label-custom label-pill label-danger stage-count">58</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Branch Visited</span><span
										class="label label-custom label-pill label-danger stage-count">27</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Proposal &amp; Negotiation</span><span
										class="label label-custom label-pill label-danger stage-count">22</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Registered</span><span
										class="label label-custom label-pill label-danger stage-count">48</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Confirmed Visa Study</span><span
										class="label label-custom label-pill label-danger stage-count">19</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Not Interested</span><span
										class="label label-custom label-pill label-danger stage-count">21</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Transferred Leads</span><span
										class="label label-custom label-pill label-danger stage-count">9</span></a></li>
							<li><a href="#" class="stage-link"><span class="lbl">All Leads</span><span
										class="label label-custom label-pill label-danger stage-count">691</span></a>
							</li>

						</ul>
					</li>
				</ul>
			</li>
			<li class="purple with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/admission.webp" alt="Dashboard">
					<span class="lbl">Registration Management</span></span>
				<ul>
					<li><a href="{{ route('registration.status') }}" class="stage-link"><span class="lbl">All Registration</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['all_registrations'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('voucher.preview') }}"><span class="lbl">Fee Voucher Preview</span></a></li>
				</ul>
			</li>
			<li class="gold orange with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/admissions.webp" alt="Admissions">
					<span class="lbl">Admission Management</span></span>
				<ul>
					<li><a href="{{ route('admission.status') }}" class="stage-link"><span class="lbl">All Admissions</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['all_admissions'] ?? 0)) }}</span></a></li>
				</ul>
			</li>
			<li class="magenta with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/students.webp" alt="Dashboard">
					<span class="lbl">Student Management</span></span>
				<ul>
					{{-- <li><a href="{{ route('student.attendance.index') }}" class="stage-link"><span class="lbl">Attendance</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_attendance'] ?? 0)) }}</span></a></li> --}}
					<li><a href="{{ route('student.records.index', ['scope' => 'active']) }}" class="stage-link"><span class="lbl">Active</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_active'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'frozen']) }}" class="stage-link"><span class="lbl">Frozen</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_frozen'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'concluded']) }}" class="stage-link"><span class="lbl">Concluded</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_concluded'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'incomplete']) }}" class="stage-link"><span class="lbl">Incomplete</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_incomplete'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'suspended']) }}" class="stage-link"><span class="lbl">Suspended</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_suspended'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'admission_cancelled']) }}" class="stage-link"><span class="lbl">Cancelled</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_admission_cancelled'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'dropped']) }}" class="stage-link"><span class="lbl">Dropped</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_dropped'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'all_students']) }}" class="stage-link"><span class="lbl">All Students</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_all'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('student.records.index', ['scope' => 'alumni']) }}" class="stage-link"><span class="lbl">Alumni</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['student_alumni'] ?? 0)) }}</span></a></li>
				</ul>
			</li>
			<li class="blue with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/batch-time.webp" alt="Dashboard">
					<span class="lbl">Batches &amp; Time Table</span></span>
				<ul>
					<li><a href="{{ route('batch.create') }}" class="stage-link"><span class="lbl">Create Batch</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_create'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.index', ['scope' => 'upcoming']) }}" class="stage-link"><span class="lbl">Upcoming</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_upcoming'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.index', ['scope' => 'recently_started']) }}" class="stage-link"><span class="lbl">Recently Started</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_recently_started'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.index', ['scope' => 'in_progress']) }}" class="stage-link"><span class="lbl">In Progress</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_in_progress'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.index', ['scope' => 'recently_ended']) }}" class="stage-link"><span class="lbl">Recently Ended</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_recently_ended'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.index', ['scope' => 'completed']) }}" class="stage-link"><span class="lbl">Completed</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_completed'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.index') }}" class="stage-link"><span class="lbl">All Batches</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_all'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('batch.timetable.index') }}" class="stage-link"><span class="lbl">Manage Time Table</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['batch_timetable'] ?? 0)) }}</span></a></li>
				</ul>
			</li>
			<li class="green with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/courses.webp" alt="Dashboard">
					<span class="lbl">Programmes</span></span>
				<ul>
					<li><a href="{{ route('program.create') }}" class="stage-link"><span class="lbl">Create Program</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_create'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('program.index', ['scope' => 'ongoing']) }}" class="stage-link"><span class="lbl">Ongoing</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_ongoing'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('program.index', ['scope' => 'suspended']) }}" class="stage-link"><span class="lbl">Suspended</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_suspended'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('program.index', ['scope' => 'discounted']) }}" class="stage-link"><span class="lbl">Discounted</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_discounted'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('program.index') }}" class="stage-link"><span class="lbl">All Programmes</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['program_all'] ?? 0)) }}</span></a></li>
				</ul>
			</li>
			<li class="orange-red with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/campuses.webp" alt="Dashboard">
					<span class="lbl">Campuses / Franchise</span></span>
				<ul>
					<li><a href="{{ route('campus.create') }}" class="stage-link"><span class="lbl">Create Campus / Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_create'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('campus.index', ['scope' => 'campuses']) }}" class="stage-link"><span class="lbl">All Campuses</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_company'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('campus.index', ['scope' => 'franchise']) }}" class="stage-link"><span class="lbl">All Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_franchise'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('campus.index', ['scope' => 'suspended_campuses']) }}" class="stage-link"><span class="lbl">Suspended Campuses</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_suspended_company'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('campus.index', ['scope' => 'suspended_franchise']) }}" class="stage-link"><span class="lbl">Suspended Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_suspended_franchise'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('campus.index') }}" class="stage-link"><span class="lbl">All Campuses / Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['campus_all'] ?? 0)) }}</span></a></li>
				</ul>
			</li>
			<li class="grey with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/humanresource.webp" alt="Dashboard">
					<span class="lbl">Human Resources</span></span>
				<ul>
					<li><a href="{{ route('hrm.dashboard') }}" class="stage-link"><span class="lbl">HRM Dashboard</span></a></li>
					<li><a href="{{ route('hrm.employees.index') }}" class="stage-link"><span class="lbl">Employee Master / Profile</span></a></li>
					<li><a href="{{ route('hrm.masters.index') }}" class="stage-link"><span class="lbl">Departments / Designations / Holidays</span></a></li>
					<li><a href="{{ route('hrm.attendance.index') }}" class="stage-link"><span class="lbl">Attendance (Daily)</span></a></li>
					<li><a href="{{ route('hrm.leaves.index') }}" class="stage-link"><span class="lbl">Leave Management</span></a></li>
					<li><a href="{{ route('hrm.payroll.index') }}" class="stage-link"><span class="lbl">Payroll</span></a></li>
					<li><a href="{{ route('hrm.shifts.index') }}" class="stage-link"><span class="lbl">Shift / Timetable</span></a></li>
					<li><a href="{{ route('hrm.announcements.index') }}" class="stage-link"><span class="lbl">Announcements / Notifications</span></a></li>
					<li><a href="{{ route('hrm.documents.index') }}" class="stage-link"><span class="lbl">Documents</span></a></li>
				</ul>
			</li>
			<li class="gold with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/expense.webp" alt="Dashboard">
					<span class="lbl">Finance Management</span></span>
				<ul>
					<li><a href="{{ route('finance.dashboard') }}" class="stage-link"><span class="lbl">Dashboard</span></a></li>
					<li class="with-sub">
						<span>
							<span class="lbl">Expense</span>
						</span>
						<ul>
							<li><a href="{{ route('finance.expense.add') }}" class="stage-link"><span class="lbl">Add Expense</span></a></li>
							<li><a href="{{ route('finance.expense.types') }}" class="stage-link"><span class="lbl">Add Expense Type</span></a></li>
							<li class="with-sub">
								<span>
									<span class="lbl">Utility Bills</span>
								</span>
								<ul>
									<li><a href="{{ route('finance.utility.pay') }}" class="stage-link"><span class="lbl">Pay Bill</span></a></li>
									<li><a href="{{ route('finance.utility.types') }}" class="stage-link"><span class="lbl">Add Bill Type</span></a></li>
									<li><a href="{{ route('finance.utility.bills') }}" class="stage-link"><span class="lbl">Add New Bill</span></a></li>
								</ul>
							</li>
							<li><a href="{{ route('finance.rent.index') }}" class="stage-link"><span class="lbl">Building Rent Setup</span></a></li>
							<li><a href="{{ route('finance.expense.rent') }}" class="stage-link"><span class="lbl">Rent Expenses</span></a></li>
							<li><a href="{{ route('finance.expense.marketing') }}" class="stage-link"><span class="lbl ">Marketing</span></a></li>
							<li><a href="{{ route('finance.expense.assets') }}" class="stage-link"><span class="lbl ">Asset Purchase</span></a></li>
							<li><a href="{{ route('finance.expense.payroll') }}" class="stage-link"><span class="lbl ">Payroll</span></a></li>
							<li><a href="{{ route('finance.expense.all') }}" class="stage-link"><span class="lbl ">All Expenses</span></a></li>
						</ul>
					</li>
					<li><a href="{{ route('finance.payees') }}" class="stage-link"><span class="lbl">Supplier &amp; Payee</span></a></li>
					<li><a href="{{ route('finance.payables') }}" class="stage-link"><span class="lbl">Payables</span></a></li>
					<li><a href="{{ route('finance.receivables') }}" class="stage-link"><span class="lbl">Receivables</span></a></li>
				</ul>
			</li>
			<li class="teal with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/courses.webp" alt="Inventory">
					<span class="lbl">Inventory Management</span></span>
				<ul>
					<li><a href="{{ route('inventory.create') }}" class="stage-link"><span class="lbl">Feed Campus Inventory</span></a></li>
					<li><a href="{{ route('inventory.index') }}" class="stage-link"><span class="lbl">Campus Stock Register</span></a></li>
				</ul>
			</li>
			<li class="blue with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/certificate.webp" alt="Dashboard">
					<span class="lbl">Certificate Management</span></span>
				<ul>
					<li><a href="{{ route('certificate.create') }}" class="stage-link"><span class="lbl">Request Certificate</span></a></li>
					<li><a href="{{ route('certificate.index', ['scope' => 'requested']) }}" class="stage-link"><span class="lbl">Request for Approval</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_requested'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('certificate.index', ['scope' => 'approved']) }}" class="stage-link"><span class="lbl">Approved</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_approved'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('certificate.index', ['scope' => 'printing']) }}" class="stage-link"><span class="lbl">On Printing</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_printing'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('certificate.index', ['scope' => 'ready']) }}" class="stage-link"><span class="lbl">Ready</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_ready'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('certificate.index', ['scope' => 'delivered']) }}" class="stage-link"><span class="lbl">Delivered</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_delivered'] ?? 0)) }}</span></a></li>
					<li><a href="{{ route('certificate.index') }}" class="stage-link"><span class="lbl">All Certificates</span><span
								class="label label-custom label-pill label-danger stage-count">{{ number_format((int) ($sidebarCounts['certificate_all'] ?? 0)) }}</span></a></li>
				</ul>
			</li>
			<li class="green with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/user.webp" alt="Dashboard">
					<span class="lbl">User Management</span></span>
				<ul>
					<li><a href="{{ route('users.create') }}"><span class="lbl">Create User</span></a></li>
					<li><a href="{{ route('users.index') }}"><span class="lbl">Users</span></a></li>
					<li><a href="{{ route('roles.create') }}"><span class="lbl">Create Role</span></a></li>
					<li><a href="{{ route('roles.index') }}"><span class="lbl">Roles</span></a></li>
					<li><a href="{{ route('permissions.create') }}"><span class="lbl">Create Permission</span></a></li>
					<li><a href="{{ route('permissions.index') }}"><span class="lbl">Permissions</span></a></li>
					<li><a href="{{ route('login-logs.index') }}"><span class="lbl">User Activities</span></a></li>
				</ul>
			</li>
			<li class="gold orange with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/event.webp" alt="Dashboard">
					<span class="lbl">Event Management</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Create Event</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">In Planning</span><span
								class="label label-custom label-pill label-danger stage-count">8</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Upcoming</span><span
								class="label label-custom label-pill label-danger stage-count">16</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Completed</span><span
								class="label label-custom label-pill label-danger stage-count">27</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Cancelled</span><span
								class="label label-custom label-pill label-danger stage-count">3</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Events</span><span
								class="label label-custom label-pill label-danger stage-count">58</span></a></li>
				</ul>
			</li>
			<li class="magenta with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/marketing.webp" alt="Dashboard">
					<span class="lbl">Marketing Management</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Draft Campaigns</span><span
								class="label label-custom label-pill label-danger stage-count">12</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Scheduled</span><span
								class="label label-custom label-pill label-danger stage-count">9</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Running</span><span
								class="label label-custom label-pill label-danger stage-count">14</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Paused</span><span
								class="label label-custom label-pill label-danger stage-count">5</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Completed</span><span
								class="label label-custom label-pill label-danger stage-count">21</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Campaigns</span><span
								class="label label-custom label-pill label-danger stage-count">61</span></a></li>
				</ul>
			</li>
			<li class="brown with-sub">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/reports.webp" alt="Dashboard">
					<span class="lbl">Reports</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Leads &amp; Admissions</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Students &amp; Batches</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Programmes &amp; Campuses</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">HR &amp; Finance</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Marketing &amp; Events</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Certificates</span></a></li>
				</ul>
			</li>
			<li class="brown">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/website.webp" alt="Dashboard">
					<span class="lbl">Website</span></span>
			</li>
			<li class="brown">
				<span>
					<img class="font-icon-dashboard" src="img/navbarIcons/goto.webp" alt="Dashboard">
					<span class="lbl">Website Admin Panel</span></span>
			</li>
		</ul>
	</nav><!--.side-menu-->


	<style>
		.label.label-pill.label-custom{
			padding: 5px 7px !important ;
			font-size:13px !important;
			width:auto !important;
			margin-right:5px;
		}




	</style>
