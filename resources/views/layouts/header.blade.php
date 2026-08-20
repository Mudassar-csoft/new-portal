@php
    $impersonatorUserId = (int) session('impersonator_user_id', 0);
    $isImpersonating = $impersonatorUserId > 0;
@endphp
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
                  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                  data-poll-url="{{ route('header.notifications') }}" data-poll-interval-ms="60000">
                  <i class="font-icon-alarm"></i>
                  @php($headerNotificationTotal = (int) ($notificationTotal ?? ((int) ($webLeadNotificationTotal ?? 0) + (int) ($followupNotificationCount ?? 0) + (int) ($invoiceOverdueNotificationCount ?? 0))))
                  <span id="notification-total-badge" class="notification-total-badge" style="font-size:0.625rem !important;" @if($headerNotificationTotal <= 0) hidden @endif>{{ $headerNotificationTotal > 99 ? '99+' : $headerNotificationTotal }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-notif m-0 p-0" id="notification-menu" aria-labelledby="dd-notification">
                  @include('layouts.partials.notification-menu')
                </div>
						</div>

            @php($dashboardCampusTitle = $activeDashboardCampus ? ($activeDashboardCampus->code . ' - ' . $activeDashboardCampus->name) : (($dashboardAllowsAllCampuses ?? false) ? 'All Branches' : 'Assigned Campus'))
            @if(($dashboardAllowsAllCampuses ?? false) || ($dashboardCampuses ?? collect())->isNotEmpty())
            <div class="dropdown dropdown-campus">
                          <button class="header-alarm dropdown-toggle active" type="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" title="{{ $dashboardCampusTitle }}">
                            <img class="camppus-branch" src="img/campuses.webp" alt="campus">
                          </button>
                          <div class="dropdown-menu p-2 campus-dropdown-menu">
                            <!-- <input type="text" class="form-control mb-2" id="locationSearch" placeholder="Search Campus">
                            <div class="campus-dropdown-caption px-2 pb-2">
                              <span class="text-muted">Current:</span>
                              <strong>{{ $activeDashboardCampus ? ($activeDashboardCampus->code . ' - ' . $activeDashboardCampus->name) : 'All Branches' }}</strong>
                            </div> -->
                            @if($dashboardAllowsAllCampuses ?? false)
                            <a class="dropdown-item campus-dropdown-item @if(!$activeDashboardCampus) active @endif" href="{{ route('dashboard', ['campus_id' => 0]) }}">
                              <img class="campus-item-icon" src="img/campuses.webp" alt="campus">All Branches
                            </a>
                            @endif
                            @foreach(($dashboardCampuses ?? collect()) as $campus)
                              <a class="dropdown-item campus-dropdown-item @if(($activeDashboardCampus->id ?? null) === $campus->id) active @endif" href="{{ route('dashboard', ['campus_id' => $campus->id]) }}">
                                <img class="campus-item-icon" src="img/campuses.webp" alt="campus">{{ $campus->code }}-{{ $campus->name }}
                              </a>
                            @endforeach
                          </div>
                        </div>
                        @endif
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
                                  @if($isImpersonating)
                                      <div style="margin-top: 6px;">
                                          <span style="display: inline-block; padding: 4px 10px; border-radius: 999px; background: #fff1f2; color: #b91c1c; font-size: var(--typo-layouts-header-font-size-1); font-weight: var(--typo-layouts-header-font-weight-2); letter-spacing: 0.02em;">
                                              Impersonating User
                                          </span>
                                      </div>
                                  @endif
                                </div>
                              </div>
                            </div>
                            <div class="dp-main-menu ">
                                @if($isImpersonating)
                                    <form method="POST" action="{{ route('users.leave-impersonation') }}" class="m-0">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="dropdown-item ml-2 m-1 login-dropdown-item text-danger" style="width: calc(100% - 16px); text-align: left; border: 1px solid #f2c6cb; background: #fff7f7; cursor: pointer;">
                                        <i class="fas fa-rotate-left me-2"></i> Return to Admin
                                      </button>
                                    </form>
                                @endif
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
								 <form class="site-header-search" method="GET" action="{{ route('student-search.index') }}" data-live-search="custom">
	                                <input type="text" name="q" class="site-header-search-input" placeholder="Name, phone, CNIC, roll no..." value="{{ request()->routeIs('student-search.index') ? request()->query('q') : '' }}" autocomplete="off" enterkeyhint="search"/>
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
        :root {
            --typo-layouts-header-font-size-1: 12px;
            --typo-layouts-header-font-weight-2: 700;
            --typo-layouts-header-line-height-3: 36px;
            --typo-layouts-header-font-size-4: 16px;
            --typo-layouts-header-line-height-5: 1;
            --typo-layouts-header-line-height-6: 36px;
            --typo-layouts-header-line-height-7: 32px;
            --typo-layouts-header-line-height-8: 16px;
            --typo-layouts-header-font-size-9: 11px;
            --typo-layouts-header-font-weight-10: 600;
            --typo-layouts-header-font-weight-11: 500;
        }

.site-header .dropdown.dropdown-campus .dropdown-toggle{
    height: 36px;
    line-height: var(--typo-layouts-header-line-height-3);
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
	font-size: var(--typo-layouts-header-font-size-4) !important;
	line-height: var(--typo-layouts-header-line-height-5) !important;
}

.site-header .header-alarm .font-icon-alarm,
.site-header .header-alarm .font-icon-alarm::before{
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    font-family: startui !important;
    font-style: normal !important;
    font-weight: 400 !important;
    color: #adb7be !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.site-header .header-alarm .font-icon-alarm::before{
    content: "\62";
}

.site-header .dropdown a.dropdown-toggle {
    line-height: var(--typo-layouts-header-line-height-6) !important;
}
/* .site-header .header-alarm.active:after{
	display: none !important;
} */
.site-header .dropdown-campus .dropdown-toggle i{
	font-size: var(--typo-layouts-header-font-size-4) !important;
    line-height: var(--typo-layouts-header-line-height-5) !important;
}
.site-header .dropdown-campus .dropdown-toggle .camppus-branch{
	display:block;
	width:25px !important;
	height:25px !important;
	object-fit:contain;
	margin:0 auto;
}
/* .font-icon-search{
	    font-size: 1.125rem !important;
		line-height: var(--typo-layouts-header-line-height-5) !important;

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
    line-height: var(--typo-layouts-header-line-height-7) !important;
}
.site-header-search-input::placeholder{
    line-height: var(--typo-layouts-header-line-height-7) !important;
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
    line-height: var(--typo-layouts-header-line-height-7) !important;
}
.site-header-search-btn .font-icon-search{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    width:16px !important;
    height:16px !important;
    line-height: var(--typo-layouts-header-line-height-8) !important;
}
.site-header-search-btn .font-icon-search:before{
    display:block !important;
    position:static !important;
    top:auto !important;
    line-height: var(--typo-layouts-header-line-height-8) !important;
    vertical-align:middle !important;
}
.fa-classic,
.fa-regular,
.fa-solid,
.far,
.fas {
    font-family: "Font Awesome 6 Free" !important;
}
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
    line-height: var(--typo-layouts-header-line-height-3);
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
    font-size: var(--typo-layouts-header-font-size-9);
    font-weight: var(--typo-layouts-header-font-weight-2);
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
	font-size: var(--typo-layouts-header-font-size-1);
	max-height: calc(100vh - 96px);
	overflow-y: auto;
	overflow-x: hidden;
}
.notif-accordion{
	max-height: calc(100vh - 120px);
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
	font-weight: var(--typo-layouts-header-font-weight-10);
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
	font-size: var(--typo-layouts-header-font-size-9);
	font-weight: var(--typo-layouts-header-font-weight-2);
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
	max-height: none;
	overflow-y: visible !important;
	overflow-x: auto;
}
.notif-accordion-panel .notification-table{
	margin-bottom: 0;
	table-layout: fixed;
}
.notif-accordion-panel .notification-table thead th{
	position: sticky;
	top: 0;
	background: #f9fbfd;
	z-index: 1;
}
.notification-see-more{
	padding: 9px 12px 10px;
	text-align: center;
	border-top: 1px solid #edf2f7;
	background: #fff;
}
.notification-see-more a{
	color: #0099f8;
	font-size: var(--typo-layouts-header-font-size-9);
	font-weight: var(--typo-layouts-header-font-weight-2);
	text-decoration: none !important;
}
.notification-see-more a:hover{
	color: #007ed0;
}
/* .site-logo img{
    transition:0.3s;
}

.site-logo img:hover{
    transform:scale(1.05);
} */
.notification-table td,
.notification-table th{
	text-align:left !important;
  padding: 8px 3px !important;
  text-wrap: nowrap !important;
  max-width:100px;
  height: 34px;
  line-height: 18px;
  overflow: hidden;
  text-overflow: ellipsis;
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
  font-weight: var(--typo-layouts-header-font-weight-11);
  white-space: nowrap;
}

.lead-tabs .nav-link.active {
  background: #0d6efd;
  color: #fff;
}

.lead-tabs .count {
  background: #dc3545;
  color: #fff;
  font-size: var(--typo-layouts-header-font-size-1);
  min-width: 18px;
  height: 18px;
  line-height: 18px;
  text-align: center;
  border-radius: 50%;
}
.dropdown-menu.show {
  /* display: block !important; */

    inset: auto !important;
    top: auto !important;
    right: 80px !important;
    transform: none !important;
    z-index: 999999 !important;
    /* left: -62px !important; */
}
.dropdown{
    position: relative;
}
.notification-name-link {
	color: #0082c6;
	font-weight: var(--typo-layouts-header-font-weight-11);
	text-decoration: none;
}

.notification-name-link:hover {
	color: #0082c6;
	text-decoration: underline;
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
	font-size: 0.75rem !important;
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
    line-height: var(--typo-layouts-header-line-height-6) !important;
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
    font-size: 1.375rem !important;
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
    font-weight: var(--typo-layouts-header-font-weight-10);
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
  var notificationMenu = document.getElementById('notification-menu');
  var notificationBadge = document.getElementById('notification-total-badge');
  var headerDropdownToggles = document.querySelectorAll('.site-header [data-toggle="dropdown"]');
  var notificationPollUrl = notificationToggle ? notificationToggle.getAttribute('data-poll-url') : '';
  var notificationPollInterval = notificationToggle ? parseInt(notificationToggle.getAttribute('data-poll-interval-ms') || '60000', 10) : 60000;
  var notificationRequestInFlight = false;

  function resetNotificationAccordion() {
    if (!notificationMenu) {
      return;
    }

    notificationMenu.querySelectorAll('.notif-accordion-item').forEach(function(item) {
      item.classList.remove('active');
    });

    notificationMenu.querySelectorAll('.notif-accordion-panel').forEach(function(panel) {
      panel.classList.remove('show');
    });

    notificationMenu.querySelectorAll('.notif-accordion-toggle').forEach(function(button) {
      button.setAttribute('aria-expanded', 'false');
    });
  }

  function closeNotificationDropdown() {
    if (!notificationDropdown || !notificationMenu || !notificationToggle) {
      return;
    }

    notificationDropdown.classList.remove('open', 'show');
    notificationMenu.classList.remove('show');
    notificationToggle.setAttribute('aria-expanded', 'false');
    resetNotificationAccordion();
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

  function bindNotificationAccordionToggle(target) {
    if (!target || !notificationMenu) {
      return;
    }

    var targetId = target.getAttribute('data-target');
    var parentItem = target.closest('.notif-accordion-item');
    var targetPanel = targetId ? notificationMenu.querySelector(targetId) : null;
    var isOpen = parentItem && parentItem.classList.contains('active');

    resetNotificationAccordion();

    if (!isOpen && parentItem && targetPanel) {
      parentItem.classList.add('active');
      targetPanel.classList.add('show');
      target.setAttribute('aria-expanded', 'true');
    }
  }

  function syncNotificationBadge(total) {
    if (!notificationBadge) {
      return;
    }

    var count = Math.max(parseInt(total || 0, 10), 0);

    if (count > 0) {
      notificationBadge.hidden = false;
      notificationBadge.textContent = count > 99 ? '99+' : String(count);
    } else {
      notificationBadge.hidden = true;
      notificationBadge.textContent = '';
    }
  }

  function syncNotificationMenu(menuHtml) {
    if (!notificationMenu || typeof menuHtml !== 'string') {
      return;
    }

    notificationMenu.innerHTML = menuHtml;
    resetNotificationAccordion();
  }

  function pollNotificationBell() {
    if (!notificationPollUrl || !notificationMenu || notificationRequestInFlight || document.visibilityState === 'hidden') {
      return;
    }

    notificationRequestInFlight = true;

    fetch(notificationPollUrl, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function(response) {
        if (!response.ok) {
          throw new Error('Notification polling failed.');
        }

        return response.json();
      })
      .then(function(payload) {
        syncNotificationBadge(payload.notification_total || 0);
        syncNotificationMenu(payload.menu_html || '');
      })
      .catch(function() {
        // Keep the existing notification state when polling fails.
      })
      .finally(function() {
        notificationRequestInFlight = false;
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

      var accordionToggle = e.target.closest('.notif-accordion-toggle');
      if (accordionToggle) {
        e.preventDefault();
        bindNotificationAccordionToggle(accordionToggle);
      }
    });

    document.addEventListener('click', function() {
      closeNotificationDropdown();
    });

    if (notificationPollInterval > 0) {
      window.setInterval(pollNotificationBell, notificationPollInterval);
    }
  }

  document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
      pollNotificationBell();
    }
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
