<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<header class="site-header">
		<div class="container-fluid">
			<a href="{{ url('/') }}" class="site-logo">
				<img class="hidden-md-down" src="img/logo-career.webp" alt="Career-Institute"
					style="height: 40px; width: 222px; margin-top: 5px;">
				<img class="hidden-lg-down" src="img/mobile-logo.webp" alt="Career-Institute"
					style="height: 40px; width: 40px; margin-top: -5px;">
			</a>

			<button id="show-hide-sidebar-toggle" class="show-hide-sidebar padding" style="margin-left: 20px;">
				<span>toggle menu</span>
			</button>

			<button class="hamburger hamburger--htla">
				<span>toggle menu</span>
			</button>
			<div class="site-header-content">
				<div class="site-header-content-in" style="margin-top:-35px">
					<div class="site-header-shown">
						<div class="dropdown dropdown-notification notif ">
							<a href="#" class="header-alarm dropdown-toggle active" id="dd-notification"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="font-icon-alarm"></i>
								<span class="notification-total-badge">{{ (int) ($webLeadNotificationTotal ?? 0) }}</span>
							</a>
							<div class="dropdown-menu dropdown-menu-end dropdown-menu-notif m-0 p-0"
     style="min-width: 935px; font-size:12px;"
								aria-labelledby="dd-notification">
								<div class="dropdown-menu-notif-header w-ful">
									<div class="lead-tabs-wrapper w-100">
  <ul class="nav lead-tabs p-0 m-0 gap-0">
    <li class="nav-item">
      <a class="nav-link" href="#" data-target="#notif-coworking">
        Coworking FollowUp
        <span class="count">0</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="#" data-target="#notif-followup">
        Follow Up
        <span class="count">0</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link active" href="#" data-target="#notif-quick-leads">
        Quick Leads
        <span class="count">{{ $webLeadNotificationCounts['quick_lead'] ?? 0 }}</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="#" data-target="#notif-enrollments">
        Website Enrollments
        <span class="count">{{ $webLeadNotificationCounts['website_enrollment'] ?? 0 }}</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="#" data-target="#notif-admissions">
        Website Admissions
        <span class="count">{{ $webLeadNotificationCounts['website_admission'] ?? 0 }}</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="#" data-target="#notif-brochures">
        Brochure Downloads
        <span class="count">{{ $webLeadNotificationCounts['brochure_download'] ?? 0 }}</span>
      </a>
    </li>
  </ul>
</div>
									
								</div>
								<div class="dropdown-menu-notif-list">
                  <div class="tab-content notif-tab-content text-center">
                    <div class="tab-pane" id="notif-coworking">
                      <div class="text-center p-3 text-muted">No coworking follow-up notifications.</div>
                    </div>
                    <div class="tab-pane" id="notif-followup">
                      <div class="text-center p-3 text-muted">No follow-up notifications.</div>
                    </div>
                    <div class="tab-pane active show" id="notif-quick-leads">
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 notification-table ">
                          <thead>
                            <tr>
                              <th>Full Name</th>
                              <th>Date</th>
                              <th>Time</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>Ateeqa Mubarik</td>
                              <td>12-Feb-26</td>
                              <td>10:27 AM</td>
                            </tr>
                            <tr>
                              <td>Ali Raza</td>
                              <td>12-Feb-26</td>
                              <td>09:15 AM</td>
                            </tr>
                            <tr>
                              <td>Ali Raza</td>
                              <td>12-Feb-26</td>
                              <td>09:15 AM</td>
                            </tr>
                            <tr>
                              <td>Sara Khan</td>
                              <td>11-Feb-26</td>
                              <td>04:42 PM</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      @php($quickLeads = $webLeadNotifications['quick_lead'] ?? collect())
                      @if ($quickLeads->isEmpty())
                        <div class="text-center p-3 text-muted">No quick leads notifications.</div>
                      @else
                        <div class="table-responsive">
                          <table class="table table-sm mb-0 notification-table">
                            <thead>
                              <tr>
                                <th>Full Name</th>
                                <th>Date</th>
                                <th>Time</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($quickLeads as $webLead)
                                <tr class="notification-clickable" data-href="{{ route('web-leads.show', $webLead) }}">
                                  <td>{{ $webLead->full_name }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-y') ?? 'N/A' }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('h:i A') ?? 'N/A' }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      @endif
                    </div>
                    <div class="tab-pane" id="notif-enrollments">
                      @php($websiteEnrollments = $webLeadNotifications['website_enrollment'] ?? collect())
                      @if ($websiteEnrollments->isEmpty())
                        <div class="text-center p-3 text-muted">No website enrollments notifications.</div>
                      @else
                        <div class="table-responsive">
                          <table class="table table-sm mb-0 notification-table">
                            <thead>
                              <tr>
                                <th>Full Name</th>
                                <th>Date</th>
                                <th>Time</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($websiteEnrollments as $webLead)
                                <tr class="notification-clickable" data-href="{{ route('web-leads.show', $webLead) }}">
                                  <td>{{ $webLead->full_name }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-y') ?? 'N/A' }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('h:i A') ?? 'N/A' }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      @endif
                    </div>
                    <div class="tab-pane" id="notif-admissions">
                      @php($websiteAdmissions = $webLeadNotifications['website_admission'] ?? collect())
                      @if ($websiteAdmissions->isEmpty())
                        <div class="text-center p-3 text-muted">No website admissions notifications.</div>
                      @else
                        <div class="table-responsive">
                          <table class="table table-sm mb-0 notification-table">
                            <thead>
                              <tr>
                                <th>Full Name</th>
                                <th>Date</th>
                                <th>Time</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($websiteAdmissions as $webLead)
                                <tr class="notification-clickable" data-href="{{ route('web-leads.show', $webLead) }}">
                                  <td>{{ $webLead->full_name }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-y') ?? 'N/A' }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('h:i A') ?? 'N/A' }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      @endif
                    </div>
                    <div class="tab-pane" id="notif-brochures">
                      @php($brochureDownloads = $webLeadNotifications['brochure_download'] ?? collect())
                      @if ($brochureDownloads->isEmpty())
                        <div class="text-center p-3 text-muted">No brochure download notifications.</div>
                      @else
                        <div class="table-responsive">
                          <table class="table table-sm mb-0 notification-table">
                            <thead>
                              <tr>
                                <th>Full Name</th>
                                <th>Date</th>
                                <th>Time</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($brochureDownloads as $webLead)
                                <tr class="notification-clickable" data-href="{{ route('web-leads.show', $webLead) }}">
                                  <td>{{ $webLead->full_name }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-y') ?? 'N/A' }}</td>
                                  <td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('h:i A') ?? 'N/A' }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      @endif
                    </div>
                  </div>
								</div>
								<div class="dropdown-menu-notif-more">
									<a href="{{ route('web-leads.index') }}">See more</a>
								</div>
							</div>
						</div>

						<!-- <div class="dropdown dropdown-notification messages">
							<a href="#" class="header-alarm dropdown-toggle active" id="dd-messages"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="font-icon-mail"></i>
							</a>
							<div class="dropdown-menu dropdown-menu-right dropdown-menu-messages"
								aria-labelledby="dd-messages">
								<div class="dropdown-menu-messages-header">
									<ul class="nav" role="tablist">
										<li class="nav-item">
											<a class="nav-link active" data-toggle="tab" href="#tab-incoming"
												role="tab">
												Inbox
												<span class="label label-pill label-danger">8</span>
											</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-toggle="tab" href="#tab-outgoing"
												role="tab">Outbox</a>
										</li>
									</ul>
									<!--<button type="button" class="create">
	                                    <i class="font-icon font-icon-pen-square"></i>
	                                </button>-->
								<!-- </div>
								<div class="tab-content">
									<div class="tab-pane active" id="tab-incoming" role="tabpanel">
										<div class="dropdown-menu-messages-list">
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/photo-64-2.jpg" alt=""></span>
												<span class="mess-item-name">Tim Collins</span>
												<span class="mess-item-txt">Morgan was bothering about something!</span>
											</a>
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/avatar-2-64.png" alt=""></span>
												<span class="mess-item-name">Christian Burton</span>
												<span class="mess-item-txt">Morgan was bothering about something! Morgan
													was bothering about something.</span>
											</a>
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/photo-64-2.jpg" alt=""></span>
												<span class="mess-item-name">Tim Collins</span>
												<span class="mess-item-txt">Morgan was bothering about something!</span>
											</a>
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/avatar-2-64.png" alt=""></span>
												<span class="mess-item-name">Christian Burton</span>
												<span class="mess-item-txt">Morgan was bothering about
													something...</span>
											</a>
										</div>
									</div>
									<div class="tab-pane" id="tab-outgoing" role="tabpanel">
										<div class="dropdown-menu-messages-list">
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/avatar-2-64.png" alt=""></span>
												<span class="mess-item-name">Christian Burton</span>
												<span class="mess-item-txt">Morgan was bothering about something! Morgan
													was bothering about something...</span>
											</a>
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/photo-64-2.jpg" alt=""></span>
												<span class="mess-item-name">Tim Collins</span>
												<span class="mess-item-txt">Morgan was bothering about something! Morgan
													was bothering about something.</span>
											</a>
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/avatar-2-64.png" alt=""></span>
												<span class="mess-item-name">Christian Burtons</span>
												<span class="mess-item-txt">Morgan was bothering about something!</span>
											</a>
											<a href="#" class="mess-item">
												<span class="avatar-preview avatar-preview-32"><img
														src="img/photo-64-2.jpg" alt=""></span>
												<span class="mess-item-name">Tim Collins</span>
												<span class="mess-item-txt">Morgan was bothering about something!</span>
											</a>
										</div>
									</div>
								</div>
								<div class="dropdown-menu-notif-more">
									<a href="#">See more</a>
								</div>
							</div>
						</div> -->

						<div class="dropdown dropdown-campus">
							<button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true"
								aria-expanded="false" title="{{ $activeDashboardCampus ? ($activeDashboardCampus->code . ' - ' . $activeDashboardCampus->name) : 'All Campuses' }}">
								<i class="fa-solid fa-building"></i>
							</button>
							<div class="dropdown-menu p-1">
								 <input type="text" class="form-control mb-2" id="locationSearch" placeholder="Search Campus">
								<div class="campus-dropdown-caption px-2 pb-2">
									<span class="text-muted">Current:</span>
									<strong>{{ $activeDashboardCampus ? ($activeDashboardCampus->code . ' - ' . $activeDashboardCampus->name) : 'All Campuses' }}</strong>
								</div>
								<a class="dropdown-item @if(!$activeDashboardCampus) active @endif" href="{{ route('dashboard', ['campus_id' => 0]) }}">
									<i class="fa-solid fa-building campus-item-icon"></i>All Campuses
								</a>
								@foreach(($dashboardCampuses ?? collect()) as $campus)
									<a class="dropdown-item @if(($activeDashboardCampus->id ?? null) === $campus->id) active @endif" href="{{ route('dashboard', ['campus_id' => $campus->id]) }}">
										<i class="fa-solid fa-building campus-item-icon"></i>{{ $campus->code }}-{{ $campus->name }}
									</a>
								@endforeach
							</div>
						</div>


					
						<div class=" dropdown user-menu user-profile-dropdown profile-log m-0">
            <a href="#" class="nav-link  profile-log p-0 pt-1 user dropdown-toggle" id="notify" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" role="button">
              <img src="data:image/jpeg;base64,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" alt="" class="icon"  />
            </a>
            <div class="dropdown-menu profile-dropdown dropdown-menu-right" aria-labelledby="notify">
              <div class="user-profile-section login-drop">
                <div class="media mx-auto">
                  <img src="data:image/jpeg;base64,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" alt="" class="img">
                  <div class="media-body">
					<h4>
                    @if(auth()->check())
								{{ auth()->user()->name }}
							@else
								Guest 
							@endif
					</h4>
                  </div>
                </div>
              </div>
              <div class="dp-main-menu ">
               <div class="dp-main-menu "style = "line-height: 20px;">
               

  <a href="#" class="dropdown-item p-1  ml-2 m-1 ">
    <i class="fas fa-user me-2 "></i> Profile
  </a>

 

  <a href="#" class="dropdown-item  ml-2 m-1  p-1">
    <i class="fas fa-key me-2"></i> Change Password
  </a>

  <a href="#" class="dropdown-item  ml-2 m-1 text-danger">
    <i class="
	 fa-solid fa-right-from-bracket me-2"></i> Log Out
  </a>

</div>
              </div>
            </div>
          </div>



						
						<!-- <div class="dropdown user-menu">
							<button class="dropdown-toggle" id="dd-user-menu" type="button" data-toggle="dropdown"
								aria-haspopup="true" aria-expanded="false">
								<img src="img/avatar-2-64.png" alt="">
							</button>
							<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd-user-menu">
								<span class=" dropdown-item user-greeting">
							
						</span>
								<a class="dropdown-item" href="#"><span
										class="font-icon glyphicon glyphicon-user"></span>Profile</a>
								<a class="dropdown-item" href="#"><span
										class="font-icon glyphicon glyphicon-cog"></span>Settings</a>
								<a class="dropdown-item" href="#"><span
										class="font-icon glyphicon glyphicon-question-sign"></span>Help</a>
								<div class="dropdown-divider"></div>
								<form action="{{ route('logout') }}" method="POST">
									@csrf
									<button type="submit" class="dropdown-item">
										<span class="font-icon glyphicon glyphicon-log-out"></span>Logout
									</button>
								</form>
							</div>
						</div>-->

						<button type="button" class="burger-right">
							<i class="font-icon-menu-addl"></i>
						</button> 
					</div><!--.site-header-shown-->

					<div class="mobile-menu-right-overlay"></div>
					<div class="site-header-collapsed">
						<div class="site-header-collapsed-in">
							<div class="dropdown dropdown-typical">
								<div class="dropdown-menu" aria-labelledby="dd-header-sales">
									<a class="dropdown-item" href="#"><span
											class="font-icon font-icon-home"></span>Quant and Verbal</a>
									<a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real
										Gmat Test</a>
									<a class="dropdown-item" href="#"><span
											class="font-icon font-icon-speed"></span>Prep Official App</a>
									<a class="dropdown-item" href="#"><span
											class="font-icon font-icon-users"></span>CATprer Test</a>
									<a class="dropdown-item" href="#"><span
											class="font-icon font-icon-comments"></span>Third Party Test</a>
								</div>
							</div>
							<!-- <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-marketing" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-cogwheel"></span>
	                                <span class="lbl">Marketing automation</span>
	                            </a>
	
	                            <div class="dropdown-menu" aria-labelledby="dd-header-marketing">
	                                <a class="dropdown-item" href="#">Current Search</a>
	                                <a class="dropdown-item" href="#">Search for Issues</a>
	                                <div class="dropdown-divider"></div>
	                                <div class="dropdown-header">Recent issues</div>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                                <div class="dropdown-more">
	                                    <div class="dropdown-more-caption padding">more...</div>
	                                    <div class="dropdown-more-sub">
	                                        <div class="dropdown-more-sub-in">
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                            <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                                        </div>
	                                    </div>
	                                </div>
	                                <div class="dropdown-divider"></div>
	                                <a class="dropdown-item" href="#">Import Issues from CSV</a>
	                                <div class="dropdown-divider"></div>
	                                <div class="dropdown-header">Filters</div>
	                                <a class="dropdown-item" href="#">My Open Issues</a>
	                                <a class="dropdown-item" href="#">Reported by Me</a>
	                                <div class="dropdown-divider"></div>
	                                <a class="dropdown-item" href="#">Manage filters</a>
	                                <div class="dropdown-divider"></div>
	                                <div class="dropdown-header">Timesheet</div>
	                                <a class="dropdown-item" href="#">Subscribtions</a>
	                            </div>
	                        </div>
	                        <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-social" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-share"></span>
	                                <span class="lbl">Social media</span>
	                            </a>
	
	                            <div class="dropdown-menu" aria-labelledby="dd-header-social">
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                            </div>
	                        </div>
	                        <div class="dropdown dropdown-typical">
	                            <a href="#" class="dropdown-toggle no-arr">
	                                <span class="font-icon font-icon-page"></span>
	                                <span class="lbl">Projects</span>
	                                <span class="label label-pill label-danger">35</span>
	                            </a>
	                        </div> -->

							<!-- <div class="dropdown dropdown-typical">
	                            <a class="dropdown-toggle" id="dd-header-form-builder" data-target="#" href="http://example.com" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <span class="font-icon font-icon-pencil"></span>
	                                <span class="lbl">Form builder</span>
	                            </a>
	
	                            <div class="dropdown-menu" aria-labelledby="dd-header-form-builder">
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-home"></span>Quant and Verbal</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-cart"></span>Real Gmat Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-speed"></span>Prep Official App</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-users"></span>CATprer Test</a>
	                                <a class="dropdown-item" href="#"><span class="font-icon font-icon-comments"></span>Third Party Test</a>
	                            </div>
	                        </div> -->
							<div class="dropdown add-lead">
    <button class="btn btn-rounded dropdown-toggle" id="dd-header-add" type="button"
        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Add
    </button>

    <div class="dropdown-menu p-1" aria-labelledby="dd-header-add">

        <input type="text" class="form-control mb-2" id="leadSearch" placeholder="Select Lead">

        <a class="dropdown-item" href="{{ route('leads.create') }}">Create New Lead</a>
        <a class="dropdown-item" href="#">Create New Admission</a>
        <a class="dropdown-item" href="#">Create New Registration</a>

    </div>
</div>

							<div class="site-header-search-container">
								<form class="site-header-search closed">
									<input type="text" placeholder="Search" />
									<button type="submit">
										<span class="font-icon-search"></span>
									</button>
									<div class="overlay"></div>
								</form>
							</div>
						</div><!--.site-header-collapsed-in-->
					</div><!--.site-header-collapsed-->
				</div><!--site-header-content-in-->
			</div><!--.site-header-content-->
		</div><!--.container-fluid-->
	</header><!--.site-header-->



	<style>
.site-header .dropdown.dropdown-campus .dropdown-toggle{
	width: 32px !important;
	margin-left: 10px;
}
.site-header-search button{
	    line-height: 22px !important;
}
.font-icon-alarm{
	
	font-size:19px !important;
}
.site-header .dropdown a.dropdown-toggle {
  
    line-height: 36px !important;
}
.site-header .header-alarm.active:after{
	display: none !important;
}
.site-header .dropdown-campus .dropdown-toggle i{
	font-size: 18px !important;
}
.font-icon-search{
	    font-size: 18px !important;
		line-height:1 !important;

}
.site-header-search input[type=text] {
    padding: 0px 0 0 14px !important;
								}
		.fa-classic,
.fa-regular,
.fa-solid,
.far,
.fas {
    font-family: "Font Awesome 6 Free" !important;
}
.select2-dropdown .select2-dropdown--below{
	top: -21px;
}
		.nav.nav-pills .nav-link.active{
			color: black;
		}
		.nav.nav-pills .nav-link{
			border-radius:0.5rem !important;

		}
		.lead-tabs-wrapper {
  border: 1px solid #e5e5e5;
  overflow-x: auto;
  white-space: nowrap;
}
.site-header .user-menu.dropdown .dropdown-toggle img{
	margin: -3px -9px -5px 0 !important;
}
.site-header{
	height:70px;
	background:#ffffff;
	border-bottom:1px solid #e5e5e5;
	box-shadow:0 2px 8px rgba(0,0,0,0.05);
	padding:10px 15px 0 0 !important;
}
.site-header .header-alarm,
.site-header .dropdown-campus .dropdown-toggle,
.user-menu .nav-link{
	width:38px;
	height:38px;
	display:flex;
	align-items:center;
	justify-content:center;
	border-radius:50%;
	background:#f7f7f7;
	transition:0.2s;
	position: relative;
}
.notification-total-badge{
	position:absolute;
	top: -3px;
    right: 0px;
	min-width:18px;
	height:18px;
	padding:0 4px;
	border-radius:999px;
	background:#dc3545;
	color:#fff;
	font-size:10px !important;
	font-weight:700;
	    line-height: 15px;
	text-align:center;
	border:2px solid #fff;
}
.site-header .header-alarm:hover,
.site-header .dropdown-campus .dropdown-toggle:hover,
.user-menu .nav-link:hover{
	background:#eaf2ff;
	transform:translateY(-1px);
}
.site-header .dropdown-menu-notif-item{
	width: 600px !important;
}
.site-logo img{
    transition:0.3s;
}

.site-logo img:hover{
    transform:scale(1.05);
}
.notification-table td,
.notification-table th{
	text-align:center !important;
}
.lead-tabs {
  display: flex;
  flex-wrap: nowrap;
  margin: 0;
  padding: 0;
  overflow: hidden;
}


.lead-tabs .nav-item {
  flex: 0 0 auto;
  overflow: hidden !important;
}
.lead-tabs .nav-link.active {
  background: #0d6efd;
  color: #fff;
}
.lead-tabs .nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 5px;
  border-right: 1px solid #e5e5e5;
  border-radius: 0;
  background: #f8f9fa;
  color: #333;
  font-weight: 500;
  white-space: nowrap;
}

.lead-tabs .nav-link.active {
  background: #0d6efd;
  color: #fff;
}

.lead-tabs .count {
  background: #dc3545;
  color: #fff;
  font-size: 12px;
  min-width: 18px;
  height: 18px;
  line-height: 18px;
  text-align: center;
  border-radius: 50%;
}
.dropdown-menu.show {
    /* left: -62px !important; */
}
.dropdown{
    position: relative;
}
.notification-clickable {
	cursor: pointer;
}

.notification-clickable:hover td {
	background: #eef5ff;
}

.site-header .user-menu.dropdown{
    margin: 0px 0px 0px 10px!important;
	    height: 25px;
}
img.icon {
    width: 27px !important;
    height: 27px !important;
    margin-left: -10px;
}
/* img.icon{
    width:32px !important;
    height:32px !important;
    border-radius:50%;
} */
	
    .col-sm-6 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 50%;
        flex: 0 0 49%;
        max-width: 50%;
    }
.user-profile-dropdown{
    /* margin-left: 20px; */
    margin-right: 15px;
    display: flex;
    justify-content: center;
    align-items: center;

}

.profile-log{
    height: 32px !important;
    width: 32px !important;
border-radius:50%;
/* box-shadow: 0 2px 4px rgba(0,0,0,0.10); */
text-align:center;
border:2px solid #ddd;


}

.user-profile-dropdown .dropdown-toggle::after,
.user-profile-dropdown .nav-link.user::after {
    display: none !important;
    content: none !important;
}
.login-drop{

    /* left: -104px !important; */
    
    width: 253px !important;
}
.user-menu .nav-link{
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #f1f1f1;
    display: flex;
    align-items: center;
    justify-content: center;
	border:1px solid black;
}
.dropdown-menu.p-1.show {
    width: 244px !important;
    left: -73px  !important;
}
.campus-dropdown-caption{
	border-bottom: 1px solid #eef2f5;
	font-size: 12px !important;
	margin-bottom: 4px;
}
.campus-item-icon{
	width: 16px;
	margin-right: 8px;
	color: #00a8ff;
	text-align: center;
}
.dropdown-campus .dropdown-item.active,
.dropdown-campus .dropdown-item:active{
	background: #eaf4ff;
	color: #0a6fd1;
}
.dropdown-menu{
    
    /* max-width: 18rem; */
    right:0px;
    left: auto;
	width:fit-content;
    /* min-width: 11rem; */
    padding: 0;
    border-radius: 5px;
}

.media{
    background-color: #00a8ff;
    text-align: center;
    padding-top: 10px;
	    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-radius: 5px;
}
img.img {
    width: 92px !important;
    height: 92px !important;
    border-radius: 50%;
}
.media-body{
    color: white ;
    padding: 15px;
}


.media-body h5{
    font-weight: 600;
}
img.icon{
    width: 35px;
    height: 35px;
    margin-left: -10px;

}

	</style>


<script>
  document.querySelectorAll('.lead-tabs .nav-link').forEach(function(tab) {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var targetId = this.getAttribute('data-target');

      // remove active from all
      document.querySelectorAll('.lead-tabs .nav-link')
        .forEach(el => el.classList.remove('active'));

      // add active to clicked
      this.classList.add('active');

      document.querySelectorAll('.notif-tab-content .tab-pane').forEach(function(pane) {
        pane.classList.remove('active');
        pane.classList.remove('show');
      });

      if (targetId) {
        var targetPane = document.querySelector(targetId);
        if (targetPane) {
          targetPane.classList.add('active');
          targetPane.classList.add('show');
        }
      }

    });
  });
document.addEventListener("DOMContentLoaded", function () {

  function setupSearch(inputId){
    const input = document.getElementById(inputId);
    if(!input) return;

    const items = input.parentElement.querySelectorAll(".dropdown-item");

    input.addEventListener("keyup", function(){
      const filter = this.value.toLowerCase();

      items.forEach(function(item){
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(filter) ? "" : "none";
      });
    });
  }

  setupSearch("locationSearch"); // location dropdown
  setupSearch("leadSearch");     // lead dropdown

});
</script>
