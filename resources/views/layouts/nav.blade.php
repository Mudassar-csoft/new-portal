
<div class="mobile-menu-left-overlay"></div>
	<nav class="side-menu">
		{{-- DEBUG --}}
<!-- <div style="background:red;color:white">SIDEBAR LOADED</div> -->

		<ul class="side-menu-list">
			<li>
				<span style="padding-left:14px">
					<img class="font-icon-dashboard" src="img/navbarIcons/dashboard.png" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Dashboard</span></span>
			</li>                                   
			<li class="brown with-sub">
				<span style="padding-left:14px">
					<img class="font-icon-dashboard" src="img/navbarIcons/enquiry.JPG" alt="Leads"
					 	style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Leads Management</span></span>
				<ul>
					<li><a href="{{ route('leads.create') }}"><span class="lbl">Create New Lead</span></a></li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/classroom.webp" alt="Dashboard"
								style="height: 20px; width: 20px; margin-right: 8px;">
							<span class="lbl">Traning Leads</span>
						</span>
						<ul>
							<li><a href="{{ route('leads.followups') }}" class="stage-link"><span class="lbl">Lead's Follow-up</span><span
										class="label label-custom label-pill label-danger stage-count">35</span></a>
							</li>
							<li><a href="{{ route('leads.transfer') }}" class="stage-link"><span class="lbl">Transferred Leads</span><span
										class="label label-custom label-pill label-danger stage-count">1111</span></a>
							</li>
							<li><a href="{{ route('leads.index') }}" class="stage-link"><span class="lbl">All Leads</span><span
										class="label label-custom label-pill label-danger stage-count">1253455</span></a>
							</li>

						</ul>
					</li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/meeting.webp" alt="CO leads"
								style="height: 20px; width: 20px; margin-right: 8px;">
							<span class="lbl">Coworking Leads</span>
						</span>
						<ul>
							<li><a href="#"><span class="lbl">Lead's Follow-up</span></a></li>
							<li><a href="#" class="stage-link"><span class="lbl">Website Leads</span><span
										class="label label-custom label-pill label-danger stage-count">120</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Today</span><span
										class="label label-custom label-pill label-danger stage-count">95</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Contacted</span><span
										class="label label-custom label-pill label-danger stage-count">70</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Needs Analysis</span><span
										class="label label-custom label-pill label-danger stage-count">50</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Branch Visited</span><span
										class="label label-custom label-pill label-danger stage-count">32</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Proposal &amp; Negotiation</span><span
										class="label label-custom label-pill label-danger stage-count">18</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Registered</span><span
										class="label label-custom label-pill label-danger stage-count">12</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Confirmed Membership</span><span
										class="label label-custom label-pill label-danger stage-count">9</span></a></li>
							<li><a href="#" class="stage-link"><span class="lbl">Not Interested</span><span
										class="label label-custom label-pill label-danger stage-count">14</span></a>
							</li>
							<li><a href="#" class="stage-link"><span class="lbl">Transferred Leads</span><span
										class="label label-custom label-pill label-danger stage-count">6</span></a></li>
							<li><a href="#" class="stage-link"><span class="lbl">All Leads</span><span
										class="label label-custom label-pill label-danger stage-count">436</span></a>
							</li>

						</ul>
					</li>
					<li class="with-sub">
						<span>
							<img class="font-icon-dashboard" src="img/navbarIcons/content-managing.webp"
								alt="Exam Leads" style="height: 20px; width: 20px; margin-right: 8px;">
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
							<img class="font-icon-dashboard" src="img/navbarIcons/study-abroad.webp" alt="SA Leads"
								style="height: 20px; width: 20px; margin-right: 8px;">
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
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/admission.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Registration Management</span></span>
				<ul>
					<li><a href="{{ route('registration.status') }}" class="stage-link"><span class="lbl">All Registration</span><span
								class="label label-custom label-pill label-danger stage-count">3,105</span></a></li>
				</ul>
			</li>
			<li class="gold orange with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/admissions.webp" alt="Admissions"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Admission Management</span></span>
				<ul>
					<li><a href="{{ route('admission.status') }}" class="stage-link"><span class="lbl">All Admissions</span><span
								class="label label-custom label-pill label-danger stage-count">1,638</span></a></li>
				</ul>
			</li>
			<li class="magenta with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/students.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Student Management</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Attendance</span><span
								class="label label-custom label-pill label-danger stage-count">85</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Active</span><span
								class="label label-custom label-pill label-danger stage-count">620</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Frozen</span><span
								class="label label-custom label-pill label-danger stage-count">24</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Concluded</span><span
								class="label label-custom label-pill label-danger stage-count">140</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Incomplete</span><span
								class="label label-custom label-pill label-danger stage-count">30</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Suspended</span><span
								class="label label-custom label-pill label-danger stage-count">12</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Admission Cancelled</span><span
								class="label label-custom label-pill label-danger stage-count">9</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Dropped</span><span
								class="label label-custom label-pill label-danger stage-count">18</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Students</span><span
								class="label label-custom label-pill label-danger stage-count">938</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Alumni</span><span
								class="label label-custom label-pill label-danger stage-count">410</span></a></li>
				</ul>
			</li>
			<li class="blue with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/batch-time.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Batches &amp; Time Table</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Create Batch</span><span
								class="label label-custom label-pill label-danger stage-count">12</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Upcoming</span><span
								class="label label-custom label-pill label-danger stage-count">24</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Recently Started</span><span
								class="label label-custom label-pill label-danger stage-count">8</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">In Progress</span><span
								class="label label-custom label-pill label-danger stage-count">34</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Recently Ended</span><span
								class="label label-custom label-pill label-danger stage-count">6</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Completed</span><span
								class="label label-custom label-pill label-danger stage-count">52</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Batches</span><span
								class="label label-custom label-pill label-danger stage-count">136</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Manage Time Table</span><span
								class="label label-custom label-pill label-danger stage-count">18</span></a></li>
				</ul>
			</li>
			<li class="green with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/courses.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Programmes</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Create Program</span><span
								class="label label-custom label-pill label-danger stage-count">14</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Ongoing</span><span
								class="label label-custom label-pill label-danger stage-count">58</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Suspended</span><span
								class="label label-custom label-pill label-danger stage-count">7</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Programmes</span><span
								class="label label-custom label-pill label-danger stage-count">96</span></a></li>
				</ul>
			</li>
			<li class="orange-red with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/campuses.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Campuses / Franchise</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Create Campus / Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">5</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Campuses</span><span
								class="label label-custom label-pill label-danger stage-count">42</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">18</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Suspended Campuses</span><span
								class="label label-custom label-pill label-danger stage-count">3</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Suspended Franchise</span><span
								class="label label-custom label-pill label-danger stage-count">2</span></a></li>
				</ul>
			</li>
			<li class="grey with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/humanresource.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Human Resource  s</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">New Hires</span><span
								class="label label-custom label-pill label-danger stage-count">6</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Active Staff</span><span
								class="label label-custom label-pill label-danger stage-count">180</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Leave Requests</span><span
								class="label label-custom label-pill label-danger stage-count">14</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Attendance</span><span
								class="label label-custom label-pill label-danger stage-count">35</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Performance Reviews</span><span
								class="label label-custom label-pill label-danger stage-count">9</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Staff</span><span
								class="label label-custom label-pill label-danger stage-count">215</span></a></li>
				</ul>
			</li>
			<li class="gold with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/expense.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Finance Management</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Invoices Pending</span><span
								class="label label-custom label-pill label-danger stage-count">18</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Invoices Paid</span><span
								class="label label-custom label-pill label-danger stage-count">74</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Expenses</span><span
								class="label label-custom label-pill label-danger stage-count">42</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Payroll</span><span
								class="label label-custom label-pill label-danger stage-count">9</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Pending Approvals</span><span
								class="label label-custom label-pill label-danger stage-count">11</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Finance</span><span
								class="label label-custom label-pill label-danger stage-count">154</span></a></li>
				</ul>
			</li>
			<li class="blue with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/certificate.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Certificate Management</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Request for Approval</span><span
								class="label label-custom label-pill label-danger stage-count">9</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Approved</span><span
								class="label label-custom label-pill label-danger stage-count">21</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">On Printing</span><span
								class="label label-custom label-pill label-danger stage-count">7</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Ready</span><span
								class="label label-custom label-pill label-danger stage-count">12</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Delivered</span><span
								class="label label-custom label-pill label-danger stage-count">44</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">All Certificates</span><span
								class="label label-custom label-pill label-danger stage-count">93</span></a></li>
				</ul>
			</li>
			<li class="green with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/user.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">User Management</span></span>
				<ul>
					<li><a href="#" class="stage-link"><span class="lbl">Add User</span><span
								class="label label-custom label-pill label-danger stage-count">5</span></a></li>
					<li><a href="#" class="stage-link"><span class="lbl">Manage Users</span><span
								class="label label-custom label-pill label-danger stage-count">140</span></a></li>
				</ul>
			</li>
			<li class="gold orange with-sub">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/event.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
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
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/marketing.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
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
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/reports.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
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
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/website.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Website</span></span>
			</li>
			<li class="brown">
				<span style="padding-left:13px">
					<img class="font-icon-dashboard" src="img/navbarIcons/goto.webp" alt="Dashboard"
						style="height: 20px; width: 20px; margin-right: 8px;">
					<span class="lbl">Website Admin Panel</span></span>
			</li>
			<li class="gold with-sub">
				<span>
					<i class="font-icon font-icon-edit"></i>
					<span class="lbl">Forms</span>
				</span>
				<ul>
					<li><a href="ui-form.html"><span class="lbl">Basic Inputs</span></a></li>
					<li><a href="ui-buttons.html"><span class="lbl">Buttons</span></a></li>
					<li><a href="ui-select.html"><span class="lbl">Select &amp; Tags</span></a></li>
					<li><a href="ui-checkboxes.html"><span class="lbl">Checkboxes &amp; Radios</span></a></li>
					<li><a href="ui-form-validation.html"><span class="lbl">Validation</span></a></li>
					<li><a href="typeahead.html"><span class="lbl">Typeahead</span></a></li>
					<li><a href="steps.html"><span class="lbl">Steps</span></a></li>
					<li><a href="ui-form-input-mask.html"><span class="lbl">Input Mask</span></a></li>
					<li><a href="form-flex-labels.html"><span class="lbl">Flex Labels</span></a></li>
					<li><a href="ui-form-extras.html"><span class="lbl">Extras</span></a></li>
				</ul>
			</li>
			<li class="blue-dirty">
				<a href="tables.html">
					<span class="glyphicon glyphicon-th"></span>
					<span class="lbl">Tables</span>
				</a>
			</li>
			<li class="magenta with-sub">
				<span>
					<span class="glyphicon glyphicon-list-alt"></span>
					<span class="lbl">Datatables</span>
				</span>
				<ul>
					<a href="datatables-net.html"><span class="lbl">Datatables.net</span></a>
			</li>
			<a href="bootstrap-datatables.html"><span class="lbl">Bootstrap Table</span></a></li>

			<!--<li><a href="datatables.html"><span class="lbl">Default</span></a></li>
	                <li><a href="datatables-fixed-columns.html"><span class="lbl">Fixed Columns</span></a></li>
	                <li><a href="datatables-reorder-rows.html"><span class="lbl">Reorder Rows</span></a></li>
	                <li><a href="datatables-reorder-columns.html"><span class="lbl">Reorder Columns</span></a></li>
	                <li><a href="datatables-resize-columns.html"><span class="lbl">Resize Columns</span></a></li>
	                <li><a href="datatables-mobile.html"><span class="lbl">Mobile</span></a></li>
	                <li><a href="datatables-filter-control.html"><span class="lbl">Filters</span></a></li>-->
		</ul>
		</li>
		<li class="green with-sub">
			<span>
				<i class="font-icon font-icon-widget"></i>
				<span class="lbl">Components</span>
			</span>
			<ul>
				<li><a href="widgets.html"><span class="lbl">Widgets</span></a></li>
				<li><a href="elements.html"><span class="lbl">Bootstrap UI</span></a></li>
				<li><a href="ui-datepicker.html"><span class="lbl">Date and Time Pickers</span></a></li>
				<li><a href="multipicker.html"><span class="lbl">Multi Picker</span></a></li>
				<li><a href="form-steps.html"><span class="lbl">Form Steps</span></a></li>
				<li><a href="components-upload.html"><span class="lbl">Upload</span></a></li>
				<li><a href="sweet-alerts.html"><span class="lbl">SweetAlert</span></a></li>
				<li><a href="color-picker.html"><span class="lbl">Color Picker</span></a></li>
				<li><a href="tabs.html"><span class="lbl">Tabs</span></a></li>
				<li><a href="panels.html"><span class="lbl">Panels</span></a></li>
				<li><a href="notifications.html"><span class="lbl">Notifications</span></a></li>
				<li><a href="range-slider.html"><span class="lbl">Sliders</span></a></li>
				<li><a href="editor-summernote.html"><span class="lbl">Editors</span></a></li>
				<li><a href="nestable.html"><span class="lbl">Nestable</span></a></li>
				<li><a href="blockui.html"><span class="lbl">BlockUI</span></a></li>
				<li><a href="alerts.html"><span class="lbl">Alerts</span></a></li>
				<li><a href="player.html"><span class="lbl">Players</span></a></li>
			</ul>
		</li>
		<!--   <li class="gold">
	            <a href="#">
	                <i class="font-icon font-icon-speed"></i>
	                <span class="lbl">Performance</span>
	            </a>
	        </li>-->
		<li class="pink-red">
			<a href="activity.html">
				<i class="font-icon font-icon-zigzag"></i>
				<span class="lbl">Activity</span>
			</a>
		</li>
		<li class="blue with-sub">
			<span>
				<i class="font-icon font-icon-user"></i>
				<span class="lbl">Profile</span>
			</span>
			<ul>
				<li><a href="profile.html"><span class="lbl">Version 1</span></a></li>
				<li><a href="profile-2.html"><span class="lbl">Version 2</span></a></li>
			</ul>
		</li>
		<li class="orange-red with-sub">
			<span>
				<i class="font-icon font-icon-help"></i>
				<span class="lbl">Support</span>
			</span>
			<ul>
				<li><a href="documentation.html"><span class="lbl">Docs (example)</span></a></li>
				<li><a href="faq.html"><span class="lbl">FAQ Simple</span></a></li>
				<li><a href="faq-search.html"><span class="lbl">FAQ Search</span></a></li>
			</ul>
		</li>
		<li class="red">
			<a href="contacts.html" class="label-right">
				<i class="font-icon font-icon-contacts"></i>
				<span class="lbl">Contacts</span>
				<span class="label label-custom label-pill label-danger">35</span>
			</a>
		</li>
		<li class="magenta opened">
			<a href="scheduler.html">
				<i class="font-icon font-icon-calend"></i>
				<span class="lbl">Calendar</span>
			</a>
		</li>
		<li class="grey with-sub">
			<span>
				<span class="glyphicon glyphicon-duplicate"></span>
				<span class="lbl">Pages</span>
			</span>
			<ul>
				<li><a href="email_templates.html"><span class="lbl">Email Templates</span></a></li>
				<li><a href="blank.html"><span class="lbl">Blank</span></a></li>
				<li><a href="empty.html"><span class="lbl">Empty List</span></a></li>
				<li><a href="prices.html"><span class="lbl">Prices</span></a></li>
				<li><a href="typography.html"><span class="lbl">Typography</span></a></li>
				<li><a href="sign-in.html"><span class="lbl">Login</span></a></li>
				<li><a href="sign-up.html"><span class="lbl">Register</span></a></li>
				<li><a href="reset-password.html"><span class="lbl">Reset Password</span></a></li>
				<li><a href="new-password.html"><span class="lbl">New Password</span></a></li>
				<li><a href="error-404.html"><span class="lbl">Error 404</span></a></li>
				<li><a href="error-500.html"><span class="lbl">Error 500</span></a></li>
				<li><a href="cards.html"><span class="lbl">Cards</span></a></li>
				<li><a href="avatars.html"><span class="lbl">Avatars</span></a></li>
				<li><a href="ribbons.html"><span class="lbl">Ribbons</span></a></li>
				<li><a href="icons-startui.html"><span class="lbl">Icons</span></a></li>
				<li><a href="invoice.html"><span class="lbl">Invoice</span></a></li>
				<li><a href="helpers.html"><span class="lbl">Helpers</span></a></li>
			</ul>
		</li>
		<li class="blue-dirty">
			<a href="list-tasks.html">
				<i class="font-icon font-icon-notebook"></i>
				<span class="lbl">Tasks</span>
			</a>
		</li>
		<li class="aquamarine">
			<a href="contacts-page.html">
				<i class="font-icon font-icon-mail"></i>
				<span class="lbl">Contact form</span>
			</a>
		</li>
		<li class="blue">
			<a href="files.html">
				<i class="font-icon glyphicon glyphicon-paperclip"></i>
				<span class="lbl">File Manager</span>
			</a>
		</li>
		<li class="gold">
			<a href="gallery.html">
				<i class="font-icon font-icon-picture-2"></i>
				<span class="lbl">Gallery</span>
			</a>
		</li>
		<li class="red">
			<a href="project.html">
				<i class="font-icon font-icon-case-2"></i>
				<span class="lbl">Project</span>
			</a>
		</li>
		<li class="brown with-sub">
			<span>
				<span class="font-icon font-icon-chart"></span>
				<span class="lbl">Charts</span>
			</span>
			<ul>
				<li><a href="charts-c3js.html"><span class="lbl">C3.js</span></a></li>
				<li><a href="charts-peity.html"><span class="lbl">Peity</span></a></li>
				<li><a href="charts-plottable.html"><span class="lbl">Plottable.js</span></a></li>
			</ul>
		</li>
		<li class="grey with-sub">
			<span>
				<span class="font-icon font-icon-burger"></span>
				<span class="lbl">Nested Menu</span>
			</span>
			<ul>
				<li><a href="#"><span class="lbl">Level 1</span></a></li>
				<li><a href="#"><span class="lbl">Level 1</span></a></li>
				<li class="with-sub">
					<span>
						<span class="lbl">Level 2</span>
					</span>
					<ul>
						<li><a href="#"><span class="lbl">Level 2</span></a></li>
						<li><a href="#"><span class="lbl">Level 2</span></a></li>
						<li class="with-sub">
							<span>
								<span class="lbl">Level 3</span>
							</span>
							<ul>
								<li><a href="#"><span class="lbl">Level 3</span></a></li>
								<li><a href="#"><span class="lbl">Level 3</span></a></li>
							</ul>
						</li> 
					</ul>
				</li>
			</ul>
		</li>
		</ul>

		<section>
			<header class="side-menu-title">Tags</header>
			<ul class="side-menu-list">
				<li>
					<a href="#">
						<i class="tag-color green"></i>
						<span class="lbl">Website</span>
					</a>
				</li>
				<li>
					<a href="#">
						<i class="tag-color grey-blue"></i>
						<span class="lbl">Bugs/Errors</span>
					</a>
				</li>
				<li>
					<a href="#">
						<i class="tag-color red"></i>
						<span class="lbl">General Problem</span>
					</a>
				</li>
				<li>
					<a href="#">
						<i class="tag-color pink"></i>
						<span class="lbl">Questions</span>
					</a>
				</li>
				<li>
					<a href="#">
						<i class="tag-color orange"></i>
						<span class="lbl">Ideas</span>
					</a>
				</li>
			</ul>
		</section>
	</nav><!--.side-menu-->

