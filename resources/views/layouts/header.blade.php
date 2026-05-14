<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<header class="site-header">
		<div class="container-fluid">
			<a href="{{ url('/') }}" class="site-logo">
        <img class="hidden-lg-down" src="img/mobile-logo.webp" alt="Career-Institute"
        style="height: 31px; width: 31px;">
				<img class="hidden-md-down" src="img/logo-career.webp" alt="Career-Institute"
					style="height: 35px; width: 200px;">
			</a>

			<button id="show-hide-sidebar-toggle" class="show-hide-sidebar">
				<span>toggle menu</span>
			</button>

			<button class="hamburger hamburger--htla">
				<span>toggle menu</span>
			</button>
			<div class="site-header-content">
				<div class="site-header-content-in">
					<div class="site-header-shown">
            <div class="dropdown dropdown-notification notif">
                <a href="#" class="header-alarm dropdown-toggle active" id="dd-notification"
                  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="font-icon-alarm"></i>
                  @php($notificationTotal = (int) ($webLeadNotificationTotal ?? 0) + (int) ($followupNotificationCount ?? 0))
                  @if($notificationTotal > 0)
                    <span class="notification-total-badge">{{ $notificationTotal > 99 ? '99+' : $notificationTotal }}</span>
                  @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-notif m-0 p-0" aria-labelledby="dd-notification">
                  @php($quickLeads = $webLeadNotifications['quick_lead'] ?? collect())
                  @php($websiteEnrollments = $webLeadNotifications['website_enrollment'] ?? collect())
                  @php($websiteAdmissions = $webLeadNotifications['website_admission'] ?? collect())
                  @php($brochureDownloads = $webLeadNotifications['brochure_download'] ?? collect())
                  @php($FeeAlert = $webLeadNotifications['Fee_Alert'] ?? collect())
                  @php($followupItems = $followupNotifications ?? collect())

                  <div class="notif-accordion">
                    <div class="notif-accordion-item notif-hover-card active">
                      <button class="notif-accordion-toggle" type="button" data-target="#notif-quick-leads" aria-expanded="true">
                        <span>Quick Leads</span>
                        <span class="count">{{ $webLeadNotificationCounts['quick_lead'] ?? 0 }}</span>
                      </button>
                      <div class="notif-accordion-panel show" id="notif-quick-leads">
                        @if ($quickLeads->isEmpty())
                          <div class="text-center p-4 text-muted">No quick leads notifications.</div>
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
                    </div>

                    <div class="notif-accordion-item notif-hover-card">
                      <button class="notif-accordion-toggle" type="button" data-target="#notif-enrollments" aria-expanded="false">
                        <span>Website Enrollments</span>
                        <span class="count">{{ $webLeadNotificationCounts['website_enrollment'] ?? 0 }}</span>
                      </button>
                      <div class="notif-accordion-panel" id="notif-enrollments">
                        @if ($websiteEnrollments->isEmpty())
                          <div class="text-center p-4 text-muted">No website enrollments notifications.</div>
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
                    </div>

                    <div class="notif-accordion-item notif-hover-card">
                      <button class="notif-accordion-toggle" type="button" data-target="#notif-admissions" aria-expanded="false">
                        <span>Website Admissions</span>
                        <span class="count">{{ $webLeadNotificationCounts['website_admission'] ?? 0 }}</span>
                      </button>
                      <div class="notif-accordion-panel" id="notif-admissions">
                        @if ($websiteAdmissions->isEmpty())
                          <div class="text-center p-4 text-muted">No website admissions notifications.</div>
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
                    </div>

                    <div class="notif-accordion-item notif-hover-card">
                      <button class="notif-accordion-toggle" type="button" data-target="#notif-brochures" aria-expanded="false">
                        <span>Brochure Downloads</span>
                        <span class="count">{{ $webLeadNotificationCounts['brochure_download'] ?? 0 }}</span>
                      </button>
                      <div class="notif-accordion-panel" id="notif-brochures">
                        @if ($brochureDownloads->isEmpty())
                          <div class="text-center p-4 text-muted">No brochure download notifications.</div>
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
                    <div class="notif-accordion-item notif-hover-card">
                      <button class="notif-accordion-toggle" type="button" data-target="#notif-fee-alert" aria-expanded="false">
                        <span>Fee Alert</span>
                        <span class="count">{{ $webLeadNotificationCounts['Fee_Alert'] ?? 0 }}</span>
                      </button>
                      <div class="notif-accordion-panel" id="notif-fee-alert">
                        @if ($FeeAlert->isEmpty())
                          <div class="text-center p-4 text-muted">No Fee Alert notifications.</div>
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
                                @foreach ($FeeAlert as $webLead)
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
                    <div class="notif-accordion-item notif-hover-card">
                      <button class="notif-accordion-toggle" type="button" data-target="#notif-follow-up" aria-expanded="false">
                        <span>Follow Up</span>
                        <span class="count">{{ $followupNotificationCount ?? 0 }}</span>
                      </button>
                      <div class="notif-accordion-panel" id="notif-follow-up">
                        @if ($followupItems->isEmpty())
                          <div class="text-center p-4 text-muted">No Follow Up notifications.</div>
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
                                @foreach ($followupItems as $followup)
                                  <tr class="notification-clickable" data-href="{{ route('leads.show', $followup->lead_id) }}">
                                    <td>{{ $followup->lead->name ?? 'N/A' }}</td>
                                    <td>{{ optional($followup->next_action_date)->format('d-M-y') ?? 'N/A' }}</td>
                                    <td>{{ optional($followup->next_action_date)->format('h:i A') ?? 'N/A' }}</td>
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
            
            <div class="dropdown dropdown-campus">
                          <button class="header-alarm dropdown-toggle active" type="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" title="{{ $activeDashboardCampus ? ($activeDashboardCampus->code . ' - ' . $activeDashboardCampus->name) : 'All Branches' }}">
                            <img class="camppus-branch" src="img/campuses.webp" alt="campus">
                          </button>
                          <div class="dropdown-menu p-2 campus-dropdown-menu">
                            <!-- <input type="text" class="form-control mb-2" id="locationSearch" placeholder="Search Campus">
                            <div class="campus-dropdown-caption px-2 pb-2">
                              <span class="text-muted">Current:</span>
                              <strong>{{ $activeDashboardCampus ? ($activeDashboardCampus->code . ' - ' . $activeDashboardCampus->name) : 'All Branches' }}</strong>
                            </div> -->
                            <a class="dropdown-item campus-dropdown-item @if(!$activeDashboardCampus) active @endif" href="{{ route('dashboard', ['campus_id' => 0]) }}">
                              <img class="campus-item-icon" src="img/campuses.webp" alt="campus">All Branches
                            </a>
                            @foreach(($dashboardCampuses ?? collect()) as $campus)
                              <a class="dropdown-item campus-dropdown-item @if(($activeDashboardCampus->id ?? null) === $campus->id) active @endif" href="{{ route('dashboard', ['campus_id' => $campus->id]) }}">
                                <img class="campus-item-icon" src="img/campuses.webp" alt="campus">{{ $campus->code }}-{{ $campus->name }}
                              </a>
                            @endforeach
                          </div>
                        </div>
                        <div class=" dropdown user-menu">
                         
                        <button class="dropdown-toggle" id="dd-user-menu" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                            <img src="{{ auth()->user()?->avatar_path ? asset('storage/' . auth()->user()->avatar_path) : asset('theme/img/avatar-2-64.png') }}" alt="Profile" style="object-fit: cover;">
	                        </button>
                          <div class="dropdown-menu dropdown-menu-right profile-dropdown" aria-labelledby="notify">
                            <div class="user-profile-section login-drop">
                              <div class="media mx-auto">
                                <img src="{{ auth()->user()?->avatar_path ? asset('storage/' . auth()->user()->avatar_path) : asset('theme/img/avatar-2-64.png') }}" alt="Profile" class="img">
                                <div class="media-body">
                                  <h3>
                                            @if(auth()->check())
                                        {{ auth()->user()->name }}
                                      @else
                                        Guest 
                                      @endif
                                  </h3>
                                </div>
                              </div>
                            </div>
                            <div class="dp-main-menu ">
                                <a href="{{ route('profile.show') }}" class="dropdown-item p-1 ml-2 m-1 login-dropdown-item">
                                  <i class="fas fa-user me-2 "></i> Profile
                                </a>
                                <a href="{{ route('profile.change-password') }}" class="dropdown-item ml-2 m-1 p-1 login-dropdown-item">
                                  <i class="fas fa-key me-2"></i> Change Password
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                  @csrf
                                  <button type="submit" class="dropdown-item ml-2 m-1 text-danger login-dropdown-item" style="width: calc(100% - 16px); text-align: left; border: 1px solid #e3eaf3; background: white; cursor: pointer;">
                                    <i class="fas fa-sign-out-alt me-2"></i> Log Out
                                  </button>
                                </form>
                              </div>
                            <!-- </div> -->
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
                              <button type="button" class="create">
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
                          <!-- <div class="dropdown dropdown-typical">
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
                          </div> -->
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
							<div class="site-header-search-container">
								 <form class="site-header-search" method="GET" action="{{ route('student-search.index') }}">
	                                <input type="text" name="q" class="site-header-search-input" placeholder="Name, phone, roll no..." value="{{ request()->routeIs('student-search.index') ? request()->query('q') : '' }}" autocomplete="off"/>
	                                <button type="submit" class="site-header-search-btn">
	                                    <span class="font-icon-search"></span>
	                                </button>
	                                <div class="overlay"></div>
	                </form>
							</div>
                          <div class="dropdown add-lead">
                              <button class="btn btn-rounded dropdown-toggle" id="dd-header-add" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                Add New
	                            </button>

                             <div class="dropdown-menu p-2" style="background-color:#f7f9fc"; aria-labelledby="dd-header-add">

                    <!-- <input type="text" class="form-control mb-2" id="leadSearch" placeholder="Select Lead"> -->

                    <a class="dropdown-item p-2 add-new-dropdown-item" href="{{ route('leads.create') }}">Lead</a>
                    <a class="dropdown-item p-2 add-new-dropdown-item" href="{{ route('registration.create') }}">Registration</a>
                    <a class="dropdown-item p-2 add-new-dropdown-item" href="{{ route('admission.create') }}">Admission / Enroll</a>

                </div>
            </div>
						</div><!--.site-header-collapsed-in-->
					</div><!--.site-header-collapsed-->
				</div><!--site-header-content-in-->
		  </div>
      <!--.site-header-content-->
		</div><!--.container-fluid-->
	</header><!--.site-header-->



	<style>
.site-header .dropdown.dropdown-campus .dropdown-toggle{
    height: 36px;
    line-height: 36px;
    width: 36px;
    padding: 0;
}
.site-header .dropdown.dropdown-campus {
  /* margin-left: 12px; */
  float:left;
}
@media (min-width: 1057px) {
    .site-header .site-header-collapsed .site-header-collapsed-in{
        display:flex;
        align-items:center;
        justify-content:flex-start;
        gap:16px;
        margin-top:-2px;
    }
    .site-header .site-header-search-container{
        order:1;
        flex:0 0 280px;
        width:280px;
        margin-right:auto;
    }
    .site-header .site-header-search-container .site-header-search,
    .site-header .site-header-collapsed .site-header-search.closed{
        width:100% !important;
        border-color:#c5d6de !important;
    }
    .site-header .site-header-collapsed .site-header-search .overlay,
    .site-header .site-header-collapsed .site-header-search.closed .overlay{
        display:none !important;
    }
    .site-header .site-header-collapsed .site-header-search input[type="text"],
    .site-header .site-header-collapsed .site-header-search.closed input[type="text"]{
        opacity:1 !important;
    }
    .site-header .site-header-collapsed .add-lead{
        order:2;
        flex:0 0 auto;
        margin-left:0;
    }
    .site-header .site-header-collapsed .add-lead .btn{
        white-space:nowrap;
        height:30px !important;
        min-height:30px !important;
        line-height:28px !important;
        padding-top:0 !important;
        padding-bottom:0 !important;
    }
}
/* .site-header-search button{
	    line-height: 22px !important;
} */
.font-icon-alarm{
	font-size:16px !important;
	line-height:1 !important;
}
.site-header .dropdown a.dropdown-toggle {
    line-height: 36px !important;
}
/* .site-header .header-alarm.active:after{
	display: none !important;
} */
.site-header .dropdown-campus .dropdown-toggle i{
	font-size: 16px !important;
    line-height: 1 !important;
}
.site-header .dropdown-campus .dropdown-toggle .camppus-branch{
	display:block;
	width:25px !important;
	height:25px !important;
	object-fit:contain;
	margin:0 auto;
}
/* .font-icon-search{
	    font-size: 18px !important;
		line-height:1 !important;

} */
.site-header-search{
    display:flex !important;
    align-items:center !important;
    height:34px !important;
    position:relative;
    padding-right:35px !important;
}
.site-header-search-input{
    display:block !important;
    width:100% !important;
    height:32px !important;
    padding:0 0 0 14px !important;
    margin:0 !important;
    border:none !important;
    background:transparent !important;
    line-height:32px !important;
}
.site-header-search-input::placeholder{
    line-height:32px !important;
}
.site-header-search-btn{
    position:absolute !important;
    right:0 !important;
    top:50% !important;
    transform:translateY(-50%) !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    width:35px !important;
    height:32px !important;
    padding:0 !important;
    margin:0 !important;
    line-height:32px !important;
}
.site-header-search-btn .font-icon-search{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    width:16px !important;
    height:16px !important;
    line-height:16px !important;
}
.site-header-search-btn .font-icon-search:before{
    display:block !important;
    position:static !important;
    top:auto !important;
    line-height:16px !important;
    vertical-align:middle !important;
}
.fa-classic,
.fa-regular,
.fa-solid,
.far,
.fas {
    font-family: "Font Awesome 6 Free" !important;
}
.fa-solid,
.dropdown-campus{
  font-weight:500 !important;
  color: #adb7be;
}
.site-header .dropdown-campus .dropdown-toggle{
  height: 36px !important;
  width: 36px !important;
  background: white !important;
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
.site-header .user-menu.dropdown .dropdown-toggle{
display: flex;
    align-items: center;
    justify-content: center;
    color: #adb7be;
    width: 36px;
    height: 34px;
    padding: 0;
    border: none;
    background: 0 0;
    line-height: 36px;
}
.site-header .dropdown.dropdown-notification {
    float: left;
   
}

@media (min-width: 1057px) {
	.site-header > .container-fluid {
		display: flex;
		align-items: center;
		gap: 3px;
	}

	.site-header .site-logo,
	.site-header .show-hide-sidebar,
	.site-header .hamburger {
		flex: 0 0 auto;
	}

	.site-header .site-header-content {
		float: none !important;
		height: auto !important;
		padding: 5px 0 !important;
		width: auto !important;
		flex: 1 1 auto;
		min-width: 0;
		margin-left: 0 !important;
	}

	.site-header .site-header-content .site-header-content-in {
		margin-left: 0 !important;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		min-width: 0;
	}

	.site-header .site-header-shown {
		float: none !important;
		display: flex;
		align-items: center;
		gap: 10px;
		flex: 0 0 auto;
		order: 2;
	}

	.site-header .site-header-collapsed {
		float: none !important;
		width: auto !important;
		margin-right: 0 !important;
		flex: 1 1 auto;
		order: 1;
		min-width: 0;
	}

	.site-header .site-header-collapsed .site-header-collapsed-in {
		margin-right: 0 !important;
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 12px;
		min-width: 0;
	}
}
.dropdown-toggle::after {
    display: inline-block;
    width: 0;
    height: 0;
    margin-left: .255em;
    vertical-align: .255em;
    content: "";
    border-top: .3em solid;
    border-right: .3em solid transparent;
    border-bottom: 0;
    border-left: .3em solid transparent;
}
/* .site-header .site-header-content .site-header-content-in {
	margin-left: 300px !important;
} */

@media (max-width: 767px) {
	.site-header .site-header-content {
		margin-left: -80px !important;
	}

	.site-header .site-header-content .site-header-content-in {
		margin-left: 80px !important;
	}
    .site-header .site-header-collapsed .site-header-collapsed-in{
        flex-direction:column;
        align-items:stretch;
        gap:12px;
        margin-top:-2px;
    }
    .site-header .site-header-search-container,
    .site-header .site-header-collapsed .add-lead{
        width:100%;
        flex-basis:auto;
        margin-left:0;
        margin-right:0;
    }
    .site-header .site-header-collapsed .add-lead .btn{
        width:100%;
    }
}
/* .site-header .header-alarm,
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
    cursor: pointer;
    z-index: 121;
} */
.notification-total-badge{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border: solid 1px #fff;
    background: #fa424a;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    border-radius: 999px;
    position: absolute;
    top: -2px;
    right: -4px;
    box-sizing: border-box;
    box-shadow: 0 1px 3px rgba(0,0,0,0.18);
    pointer-events: none;
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
.dropdown-menu-notif{
	width: 720px;
	max-width: calc(100vw - 32px);
	font-size: 12px;
	overflow: hidden;
}
.notif-accordion{
	max-height: 392px;
	overflow-y: auto;
	padding: 12px;
	background: #f7f9fc;
}
.notif-accordion-item{
	background: #fff;
	border: 1px solid #e3eaf3;
	border-radius: 12px;
	overflow: hidden;
	transition: background-color .2s ease, border-color .2s ease, transform .2s ease, box-shadow .2s ease;
}
.notif-accordion-item + .notif-accordion-item{
	margin-top: 4px;
}
.notif-hover-card:hover{
	background: #eef5ff;
	border-color: #cfe0f5;
	transform: translateY(-1px);
	box-shadow: 0 3px 10px rgba(15, 54, 88, 0.08);
}
[class*=" font-icon-"]:before, [class^=font-icon-]:before {
    font-family: startui !important;
    font-style: normal !important;
    font-weight: 400 !important;
    font-variant: normal !important;
    text-transform: none !important;
    speak: none;
    line-height: inherit;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    vertical-align: middle;
    position: relative;
    top: 0px;
}
.notif-accordion-toggle{
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 10px;
	width: 100%;
	padding: 12px 14px;
	background: transparent;
	border: 0;
	color: #2d3a48;
	font-weight: 600;
	cursor: pointer;
}
.notif-accordion-item.active .notif-accordion-toggle{
	background: #eaf3ff;
	color: #0a6fd1;
}
.notif-accordion-toggle .count{
	min-width: 22px;
	height: 22px;
	padding: 0 6px;
	border-radius: 999px;
	background: #dc3545;
	color: #fff;
	font-size: 11px;
	font-weight: 700;
	line-height: 22px;
	text-align: center;
}
.notif-accordion-panel{
	display: none;
	background: #fff;
	border-top: 1px solid #edf2f7;
}
.notif-accordion-panel.show{
	display: block;
}
.notif-accordion-panel .table-responsive{
	max-height: 310px;
	overflow-y: auto;
}
.notif-accordion-panel .notification-table{
	margin-bottom: 0;
}
.notif-accordion-panel .notification-table thead th{
	position: sticky;
	top: 0;
	background: #f9fbfd;
	z-index: 1;
}
/* .site-logo img{
    transition:0.3s;
}

.site-logo img:hover{
    transform:scale(1.05);
} */
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
    /* margin: 0 0 0 12px !important; */
    height: 34px;
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
    
    width: 274px !important;
}
.site-header .user-menu.dropdown .profile-dropdown {
    width: 274px !important;
    min-width: 274px !important;
    max-width: calc(100vw - 20px);
}

.site-header .user-menu.dropdown .profile-dropdown .dropdown-item,
.site-header .user-menu.dropdown .profile-dropdown .login-dropdown-item {
    display: block;
    width: auto;
    padding: 4px 12px !important;
}

.site-header .user-menu.dropdown .profile-dropdown .dp-main-menu {
    padding: 0 2px 2px;
}
.dp-main-menu{
  background-color: #f7f9fc;
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
.login-dropdown-item{
	background-color:white;
	border-radius:5%;
	box-shadow: 0 1px 1px rgba(0, 0, 0, 0.12);
	border: 1px solid #e3eaf3;
	padding: 10px 12px !important;
	transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
}
.login-dropdown-item:hover{
	background: #eef5ff;
	border-color: #cfe0f5;
	transform: translateY(-1px);
}
.dropdown-menu.p-1.show {
    width: 366px !important;
    left: -73px  !important;
}
.campus-dropdown-caption{
	border-bottom: 1px solid #eef2f5;
	font-size: 12px !important;
	margin-bottom: 4px;
}
.campus-dropdown-menu{
	background-color:#f7f9fc;
	min-width: 260px;
	width: 320px;
}
.campus-dropdown-item{
	background-color:white;
	border-radius:5%;
	box-shadow: 0 1px 1px rgba(0, 0, 0, 0.12);
	border: 1px solid #e3eaf3;
	margin-bottom:4px;
	padding: 5px 12px !important;
	transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
}
.campus-dropdown-menu .dropdown-item:last-child{
	margin-bottom: 0;
}
.campus-item-icon{
	width: 16px;
	margin-right: 8px;
	color: #00a8ff;
	text-align: center;
}
.campus-dropdown-item:hover{
	background: #eef5ff;
	border-color: #cfe0f5;
	transform: translateY(-1px);
}
.add-new-dropdown-item{
	background-color:white;
	border-radius:5%;
	box-shadow: 0 1px 1px rgba(0, 0, 0, 0.12);
	border: 1px solid #e3eaf3;
	margin-bottom:4px;
	padding: 4px 12px !important;
	transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
}
.add-new .dropdown-menu .dropdown-item:last-child{
	margin-bottom: 0;
}
.add-new-dropdown-item:hover{
	background: #eef5ff;
	border-color: #cfe0f5;
	transform: translateY(-1px);
}
.dropdown-campus .dropdown-item.active,
.dropdown-campus .dropdown-item:active{
	background: #eaf4ff;
	color: #0a6fd1;
	border-color: #bfd8fb;
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



.site-header .dropdown.dropdown-notification .dropdown-menu-notif {
	position: fixed !important;
	top: 80px !important;
	right: 12px !important;
	left: auto !important;
	transform: none !important;
	z-index: 9999 !important;
}

.site-header .user-menu.dropdown .profile-dropdown {
	position: absolute !important;
	top: calc(100% - 2px) !important;
	right: 10px !important;
	left: auto !important;
	transform: none !important;
}

.site-header .user-menu.dropdown .profile-dropdown {
	width: 274px !important;
	min-width: 274px !important;
	max-width: calc(100vw - 20px);
}
.site-header .header-alarm,
.site-header .dropdown-campus .dropdown-toggle,
.site-header .user-menu.dropdown .dropdown-toggle{
    width: 34px !important;
    height: 32px !important;
    min-width: 36px;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    line-height: 36px !important;
    text-align: center;
}
.site-header .header-alarm::after,
.site-header .dropdown-campus .dropdown-toggle::after,
.site-header .user-menu.dropdown .dropdown-toggle::after{
    display: none !important;
}
.site-header .user-menu.dropdown .dropdown-toggle::after{
    display: inline-block !important;
    width: 0;
    height: 0;
    margin-left: 6px;
    vertical-align: middle;
    content: "";
    border-top: .3em solid;
    border-right: .3em solid transparent;
    border-bottom: 0;
    border-left: .3em solid transparent;
}
.site-header .header-alarm i,
.site-header .dropdown-campus .dropdown-toggle i{
    font-size: 22px !important;
}
.site-header .user-menu.dropdown .dropdown-toggle img{
    width: 32px;
    height: 31px;
    display: block;
    object-fit: cover;
    border-radius: 50%;
    margin: 0;
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
@media (max-width: 760px) {
    .dropdown-menu-notif{
        width: calc(100vw - 24px);
    }
    .site-header .dropdown.dropdown-notification .dropdown-menu-notif {
        right: 12px !important;
        top: 42px !important;
    }
    .site-header .user-menu.dropdown .profile-dropdown {
        right: 12px !important;
    }
    .notif-accordion{
        max-height: 70vh;
    }
}

.site-header .dropdown-toggle,
.site-header .dropdown-menu,
.site-header .dropdown-item,
.site-header .nav-link,
.site-header .header-alarm,
.site-header .dropdown-campus .dropdown-toggle,
.site-header .login-dropdown-item,
.site-header .campus-dropdown-item,
.site-header .add-new-dropdown-item,
.site-header .notif-accordion-item,
.site-header .notif-accordion-toggle,
.site-header .media {
	/* border-radius: 0 !important; */
}

.site-header .dropdown-toggle,
.site-header .dropdown-menu,
.site-header .dropdown-item,
.site-header .nav-link,
.site-header .header-alarm,
.site-header .dropdown-campus .dropdown-toggle,
.site-header .login-dropdown-item,
.site-header .campus-dropdown-item,
.site-header .add-new-dropdown-item,
.site-header .notif-accordion-item,
.site-header .notif-accordion-toggle,
.site-header .media {
	border: 0 !important;
	/* border-color: transparent !important;                  */
	box-shadow: none !important;
}

	</style>


<script>
document.addEventListener("DOMContentLoaded", function () {
  var notificationToggle = document.getElementById('dd-notification');
  var notificationDropdown = notificationToggle ? notificationToggle.closest('.dropdown-notification') : null;
  var notificationMenu = notificationDropdown ? notificationDropdown.querySelector('.dropdown-menu-notif') : null;
  var headerDropdownToggles = document.querySelectorAll('.site-header [data-toggle="dropdown"]');

  function closeNotificationDropdown() {
    if (!notificationDropdown || !notificationMenu || !notificationToggle) {
      return;
    }

    notificationDropdown.classList.remove('open', 'show');
    notificationMenu.classList.remove('show');
    notificationToggle.setAttribute('aria-expanded', 'false');
  }

  function closeOtherHeaderDropdowns(exceptToggle) {
    headerDropdownToggles.forEach(function(toggle) {
      if (toggle === exceptToggle || toggle === notificationToggle) {
        return;
      }

      var dropdown = toggle.closest('.dropdown');
      if (!dropdown) {
        return;
      }

      dropdown.classList.remove('open', 'show', 'dropup', 'dropdown-action-menu');

      dropdown.querySelectorAll('.dropdown-menu').forEach(function(menu) {
        menu.classList.remove('show', 'dropdown-menu-upward');
      });

      toggle.setAttribute('aria-expanded', 'false');
    });
  }

  headerDropdownToggles.forEach(function(toggle) {
    if (toggle === notificationToggle) {
      return;
    }

    toggle.addEventListener('click', function() {
      closeNotificationDropdown();
    });
  });

  if (notificationToggle && notificationDropdown && notificationMenu) {
    notificationToggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var isOpen = notificationDropdown.classList.contains('open');

      closeNotificationDropdown();
      closeOtherHeaderDropdowns(notificationToggle);

      if (!isOpen) {
        notificationDropdown.classList.add('open');
        notificationMenu.classList.add('show');
        notificationToggle.setAttribute('aria-expanded', 'true');
      }
    });

    notificationMenu.addEventListener('click', function(e) {
      e.stopPropagation();
    });

    document.addEventListener('click', function() {
      closeNotificationDropdown();
    });
  }

  document.querySelectorAll('.notif-accordion-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      var targetId = this.getAttribute('data-target');
      var parentItem = this.closest('.notif-accordion-item');
      var targetPanel = targetId ? document.querySelector(targetId) : null;
      var isOpen = parentItem && parentItem.classList.contains('active');

      document.querySelectorAll('.notif-accordion-item').forEach(function(item) {
        item.classList.remove('active');
      });

      document.querySelectorAll('.notif-accordion-panel').forEach(function(panel) {
        panel.classList.remove('show');
      });

      document.querySelectorAll('.notif-accordion-toggle').forEach(function(button) {
        button.setAttribute('aria-expanded', 'false');
      });

      if (!isOpen && parentItem && targetPanel) {
        parentItem.classList.add('active');
        targetPanel.classList.add('show');
        this.setAttribute('aria-expanded', 'true');
      }
    });
  });

  document.querySelectorAll('.notification-clickable').forEach(function(row) {
    row.addEventListener('click', function() {
      var href = this.getAttribute('data-href');
      if (href) {
        window.location.href = href;
      }
    });
  });

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
