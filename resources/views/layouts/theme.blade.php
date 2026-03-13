<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>@yield('title', 'StartUI - Premium Bootstrap 4 Admin Dashboard Template')</title>

	<base href="{{ asset('theme') }}/">

	<link href="img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
	<link href="img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
	<link href="img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
	<link href="img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
	<link href="img/favicon.png" rel="icon" type="image/png">
	<link href="img/favicon.ico" rel="shortcut icon">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" href="css/lib/font-awesome/font-awesome.min.css">
	<link rel="stylesheet" href="css/lib/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">
			<link rel="stylesheet" href="css/custom-responsive.css">

	@stack('styles')
	<style>
		/* =========================================================
   Base (Mobile First)
   ========================================================= */
* {
  font-family: "Montserrat", sans-serif !important;
  font-size: 0.75rem; /* 12px */
  margin: 0;
  padding: 0;
}

body,
p,
span,
label,
input,
textarea,
select,
button {
  font-size: 13px !important; 
  line-height: 1.5;
}

button,
.btn,
a.btn,
input[type="button"],
input[type="submit"] {
  padding: 0.375rem 0.75rem; /* 6px 12px */
  height: 32px; /* keep px */
  line-height: 1rem;
}

hr {
  margin: 1rem 0 !important;
}
.fa-classic,
.fa-regular,
.fa-solid,
.far,
.fas {
font-family: "Font Awesome 6 Free" !important;
}
.user-shell{
	padding:0px !important;
}
.box-typical .panel-title, .form-label{
	font-size:12px;
}
.btn.btn-default {
  background-color: #00a8ff;
  border-color: #00a8ff;
}

.follow-table thead th {
background: #00a8ff !important;
text-align: left !important;
}
.table td,{
text-align: left !important;
	padding-left:2px !important;
}

body,
html,
button,
input:not([type="radio"]):not([type="checkbox"]):not([type="range"]),
select,
textarea {
color: #343434;
font-family: 'Proxima Nova', sans-serif;
line-height: 1.4;
min-height:32px !important;
text-rendering: optimizeLegibility;
-moz-osx-font-smoothing: grayscale;
-webkit-font-smoothing: antialiased;
}

/* =========================================================
   Tables
   ========================================================= */
.table {
  width: 100%;
  max-width: 100%;
  margin-left: 0.1875rem !important; 
}

.table th {
  padding: 0.6875rem 0.3125rem 0.625rem !important;
}
.table th{
height: 16px !important;

}
.table td,
.odd,
.even {
  height: 28px !important;
  font-size: 13px !important; 
}

.table a {
border-bottom: 1px solid #e9ecef;
}

.bootstrap-table .table td,
.fixed-table-body .table td,
.table td,
tr {
  height: 29px !important;
  padding: 0.1875rem 0.3125rem !important; /* 3px 5px */
  text-align: left !important;
}

.box-typical .panel-heading {
padding: 0.4375rem 1.25rem;
}

.mb-3,
.my-3 {
margin-bottom: 0 !important;
}
.side-menu-list {
margin: -18px 0 20px;
}

.side-menu-list .lbl {
font-size: 12px !important;
font-weight: 600 !important;
line-height: 1.3;
}

.side-menu .stage-link {
display: flex;
align-items: center;
justify-content: space-between;
gap: 8px;
}

.side-menu .stage-count {
min-width: 32px;
width: 80px;
text-align: center;
color: #fff;
background-color: #6c757d;
border-color: #6c757d;
}

.side-menu-list a,
.side-menu-list li > span {
padding: 6px 12px 6px 46px;
}

/* =========================================================
   Sidebar Stage Pills
   ========================================================= */
/* Module-specific pill tones */
.side-menu .brown .stage-count { background-color: #c77d16; border-color: #c77d16; }
.side-menu .purple .stage-count { background-color: #6f42c1; border-color: #6f42c1; }
.side-menu .orange .stage-count { background-color: #ff9800; border-color: #ff9800; }
.side-menu .magenta .stage-count { background-color: #e83e8c; border-color: #e83e8c; }
.side-menu .blue .stage-count { background-color: #0088cc; border-color: #0088cc; }
.side-menu .green .stage-count { background-color: #28a745; border-color: #28a745; }
.side-menu .orange-red .stage-count { background-color: #ff5722; border-color: #ff5722; }
.side-menu .teal .stage-count { background-color: #00897b; border-color: #00897b; }
.side-menu .gold .stage-count { background-color: #d4a017; border-color: #d4a017; }

.site-header .site-header-collapsed .site-header-collapsed-in {
margin-right: 122px !important;
}

.site-header .dropdown .btn.dropdown-toggle {
height: 28px;
}
.site-header .dropdown-menu-notif .dropdown-menu-notif-list {
    height: 150px;
    overflow: auto;
}
.site-header .user-greeting {
margin-right: 10px;
color: #6c7a89;
font-size: 14px;
display: inline-flex;
align-items: center;
}

.login-logs .box-typical-body {
padding: 10px 16px !important;
}

/* =========================================================
   DataTables
   ========================================================= */
.dataTables_wrapper .dataTables_filter input,
.login-logs .dataTables_filter input {
  border: 1px solid #d9e2ef;
  border-radius: 0.3125rem !important; 
  padding: 0.0625rem 0.75rem !important; 
  height: 24px !important;
  width: 12.5rem;
  max-width: 12.5rem;
  box-shadow: none;
}
		#users-table_filter input {
			height: 24px !important;
			padding: 2px !important;
			width:200px;
		}
div.dataTables_wrapper div.dataTables_info {
padding-top: 1em;
}
div.dataTables_wrapper div.dataTables_filter input{
	width:200px;
}div.dataTables_wrapper div.dataTables_filter label {
    font-weight: 0px !important;
    white-space: nowrap;
    text-align: left;}
.dataTables_length {
padding-left: 10px !important;
}
.dataTables_wrapper .follow-controls, .dataTables_wrapper .follow-footer {
    display: flex;
    align-items: center;
    justify-content: space-between !important;
    /* gap: 488px !important; */
    margin-bottom: 4px !important;
}
.dataTables_wrapper .table-responsive {
overflow-x: auto;
}

.dataTables_wrapper table {
width: 100%;
}

.table-responsive {
text-align: left !important;
}
.followup-table-wrapper {
height: 26vh !important;
}

.follow-controls {
padding: 0 10px;
}

.follow-footer {
padding: 0 12px !important;
}

.row {
  align-items: center !important;
  margin-left: -0.5rem;  /* -8px */
  margin-right: -0.5rem; /* -8px */
}

.row > [class*="col-"] {
padding-left: 0.5rem;
padding-right: 0.5rem;
}

.form-row {
display: block;
padding-left: 15px;
margin-right: 2px;
}

.form-row > .col,
.form-row > [class*=col-] {
padding-right: 3px;
padding-left: 5px;
}



.form-radio {
flex-direction: column;
align-items: flex-start !important;
}

.gender {
margin-right: 0 !important;
}

.leave-button {
margin: 0 !important;
}

/* =========================================================
   Forms
   ========================================================= */
.form-label{
font-size:12px !important;
font-weight:600;
text-transform:uppercase;
margin-bottom:0.1875rem;
color:#535558 !important;
}

.select2-container--arrow .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
border: solid 1px #d8e2e7;
border-radius: .25rem;
font-size: 1rem;
line-height: 1.5;
color: #343434;
padding: .375rem 25px .375rem 1rem;
min-height: 32px !important;
background: #fff;
}

/* =========================================================
   Action Dropdowns
   ========================================================= */
.follow-action-dropdown,
.registration-action-dropdown,
.admission-action-dropdown,
.permission-action-dropdown,
.user-action-dropdown,
.role-action-dropdown,
.action-cell {
position: relative;
}

.action-cell {
min-width: 110px;
white-space: nowrap;
}

.table .action-cell > .dropdown,
.table td.actions-cell > .dropdown,
.table .permission-action-dropdown,
.table .user-action-dropdown,
.table .role-action-dropdown {
display: flex;
justify-content: center;
}

.dropdown-menu.action-key,
.follow-action-dropdown .dropdown-menu,
.registration-action-dropdown .dropdown-menu {
font-size: 12px !important;
min-width: 180px;
position: absolute;
left: -73px !important;
z-index: 9999;
}

.follow-action-dropdown .dropdown-menu {
top: 0 !important;
left: auto !important;
right: calc(100% + 2px) !important;
margin: 0 !important;
transform: none !important;
}

.registration-action-dropdown .dropdown-menu,
.admission-action-dropdown .dropdown-menu{
top: 0 !important;
left: auto !important;
right: calc(90% + 2px) !important;
margin: 0 !important;
transform: none !important;
}


.table .permission-action-dropdown .dropdown-menu.action-key,
.table .user-action-dropdown .dropdown-menu.action-key,
.table .role-action-dropdown .dropdown-menu.action-key {
position: absolute;
top: calc(100% - 33px) !important;
left: -94px !important;
right: auto !important;
transform: translate3d(-50%, 0, 0) !important;
z-index: 9999;
}

.table .permission-action-dropdown .dropdown-menu.action-key.dropdown-menu-upward,
.table .user-action-dropdown .dropdown-menu.action-key.dropdown-menu-upward,
.table .role-action-dropdown .dropdown-menu.action-key.dropdown-menu-upward {
top: 0 !important;
left: 50% !important;
right: auto !important;
transform: translate3d(-50%, -10%, 20px) !important;
}

.table .action-cell .dropdown-toggle,
.table td.actions-cell .dropdown-toggle,
.table .follow-action-dropdown .dropdown-toggle,
.table .registration-action-dropdown .dropdown-toggle,
.table .admission-action-dropdown .dropdown-toggle,
.table .permission-action-dropdown .dropdown-toggle,
.table .user-action-dropdown .dropdown-toggle,
.table .role-action-dropdown .dropdown-toggle {
height: 22px !important;
padding: 1px 8px !important;
min-width: 68px !important;
font-size: 13px !important;
line-height: 1.2 !important;
text-align: center !important;
}

/* =========================================================
   Sidebar + Page Layout
   ========================================================= */
.with-side-menu .side-menu {
width: 240px;
}

.with-side-menu .page-content {
margin-left: 0;
margin-top: -0.125rem; /* -2px */
  padding: 6.875rem 1rem 1.5rem; 
height: auto;
overflow: hidden !important;
}

.with-side-menu .page-content > .container-fluid {
max-width: 100%;
margin: 0 auto;
padding-left: 7px;
padding-right: 12px;
height: 100%;
overflow: hidden !important;
}

.menu-left-hidden .page-content,
body.sidebar-hidden .page-content {
margin-left: 0 !important;
padding-left: 16px;
padding-right: 47px;
}

.menu-left-hidden .page-content > .container-fluid,
body.sidebar-hidden .page-content > .container-fluid {
max-width: 100%;
padding-left: 8px;
padding-right: 8px;
}

body.sidebar-hidden .side-menu {
left: -280px;
}

/* =========================================================
   Validation + Alerts
   ========================================================= */
.welcome-swal .sweet-alert p {
color: #28a745;
font-size: 13px;
font-weight: 600;
}

.field-error {
color: #e53935;
font-size: 12px;
margin-top: 6px;
}

.form-control.is-invalid,
.form-control-range.is-invalid {
border-color: #e53935;
box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
}

.radio-group.is-invalid,
.gender-options.is-invalid {
border: 1px solid #e53935;
border-radius: 6px;
padding: 6px 10px;
}

.lead-form .form-check-input[type="radio"], .lead-form .form-check-input[type="checkbox"] {
    min-height: 14px !important;
    height: auto !important;
}

input[type="checkbox"] {
-webkit-appearance: none;
-moz-appearance: none;
appearance: none;
width: 13px;
height: 13px;
border: 2px solid grey;
border-radius: 3px;
background-color: #fff;
cursor: pointer;
position: relative;
}

input[type="checkbox"]:checked {
background-color: #00a8ff;
border-color: #00a8ff;
}

input[type="checkbox"]:checked::after {
content: "";
position: absolute;
left: 3px;
top: 1px;
width: 4px;
height: 8px;
border: solid #fff;
border-width: 0 2px 2px 0;
transform: rotate(45deg);
}

.dropdown-item {

padding: 0.375rem 0.375rem !important;
font-size: 0.8125rem;
border-bottom: none !important;
}


/* =========================================================
   Dashboard Cards/Charts
   ========================================================= */
/* .chart-statistic-box .chart-container {
    margin-left: -39%; 
} */

/* .chart-txt {
    width: 39% !important;
}

.chart-statistic-box .chart-container .chart-container-in {
    margin-left: 39% !important; 
} */

.box-typical.box-typical-dashboard .box-typical-body {
overflow: hidden;
}

.box-typical.box-typical-dashboard {
margin: 0 0 5px !important;
}

.box-typical.box-typical-dashboard .box-typical-header {
display: flex;
}

/* =========================================================
   Breakpoints
   ========================================================= */
/* >= 576px */
@media (min-width: 576px) {
.with-side-menu .page-content {
padding: 110px 24px 24px;
}

.with-side-menu .page-content > .container-fluid {
padding-left: 7px;
padding-right: 12px;
}
}

/* >= 768px */
@media (min-width: 768px) {
.form-row {
display: flex;

}
}
/* <div class="form-group col-md-6 col-lg-3 d-flex mt-4">
                        <button type="submit" class="btn btn-inline btn-primary-outline p-2" >Filter</button>
                        <a href="{{ route('hrm.employees.index') }}" class="btn btn-inline btn-danger-outline p-2" >Reset</a>
                        </div> */
/* =====================================
   CUSTOM GRID SYSTEM (SAFE VERSION)
   ===================================== */

/* Default Mobile ( <768px ) */
.custom-col-1 
.custom-col-2,
.custom-col-3,
.custom-col-4,
.custom-col-5,
.custom-col-8 {
  flex: 0 0 100%;
  max-width: 100%;
  margin-bottom: 10px;
}

/* Medium Devices ( ≥768px ) */
@media (min-width: 768px) {
.custom-col-1 {
    flex: 0 0 50%;
    max-width: 50%;
  }
  .custom-col-2 {
    flex: 0 0 16%;
    max-width: 16%;
  }

  .custom-col-3 {
    flex: 0 0 25%;
    max-width: 25%;
  }

  .custom-col-4 {
    flex: 0 0 33.333%;
    max-width: 33.333%;
  }

  .custom-col-5 {
    flex: 0 0 41.666%;
    max-width: 41.666%;
  }

  .custom-col-8 {
    flex: 0 0 66.666%;
    max-width: 66.666%;
  }

}

/* Large Devices ( ≥992px ) */
@media (min-width: 992px) {
	.custom-col-1 {
    flex: 0 0 25%;
    max-width: 25%;
  }
  .custom-col-2 { flex: 0 0 14%; max-width: 14%; }

  .custom-col-3 { flex: 0 0 22%; max-width: 22%; }

  .custom-col-4 { flex: 0 0 30%; max-width: 30%; }

  .custom-col-5 { flex: 0 0 38%; max-width: 38%; }

  .custom-col-8 { flex: 0 0 60%; max-width: 60%; }

}

/* Extra Large ( ≥1200px ) */
@media (min-width: 1200px) {

.custom-col-1 { flex: 0 0 10.666%; max-width: 12.666%; }
  .custom-col-2 { flex: 0 0 15.666%; max-width: 18.666%; }

  .custom-col-3 { flex: 0 0 25%; max-width: 23%; }

  .custom-col-4 { flex: 0 0 33.333%; max-width: 33.333%; }

  .custom-col-5 { flex: 0 0 20%; max-width: 20%; }

  .custom-col-8 { flex: 0 0 66.666%; max-width: 66.666%; }

}

/* >= 992px */
@media (min-width: 992px) {
/* =========================================================
   Sidebar + Page Layout
   ========================================================= */
.with-side-menu .side-menu {
width: 244px;
}

.with-side-menu .page-content {
margin-left: 210px;
padding: 100px 32px 32px;
}

.with-side-menu .page-content > .container-fluid {
max-width: 1440px;
padding-left: 7px;
padding-right: 28px;
}

.side-menu-list .lbl {
font-size: 13px !important;
}

.side-menu-list a,
.side-menu-list li > span {
padding-right: 5px;
}

/* =========================================================
   Tables
   ========================================================= */
.table {
width: 98%;
max-width: 98%;
margin-left: 8px;
}

/* >= 1200px */
@media (min-width: 1200px) {
/* =========================================================
   Sidebar + Page Layout
   ========================================================= */
.with-side-menu .side-menu {
width: 244px;
}
}

/* >= 1368px */
@media (min-width: 1368px) {
/* =========================================================
   Dashboard Cards/Charts
   ========================================================= */
/* .chart-statistic-box .chart-container {
margin-left: 0;
} */
}@media (min-width: 992px) {
    .with-side-menu .page-content {
        
        padding: 65px 32px 32px ;
    }
}


	</style>
</head>

<body class="with-side-menu control-panel control-panel-compact">

	@include('layouts.header')
	@include('layouts.nav')

	<div class="page-content">
		<div class="container-fluid">
			@yield('content')
		</div><!--.container-fluid-->
	</div><!--.page-content-->

	@include('layouts.taskbar')

	<script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
	<script src="js/lib/popper/popper.min.js"></script>
	<script src="js/lib/tether/tether.min.js"></script>
	<script src="js/lib/bootstrap/bootstrap.min.js"></script>
	<script src="js/plugins.js"></script>
	<script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<script src="js/app.js"></script>
	<script>
		$(function () {
			$(document).on('shown.bs.dropdown', '.dropdown', function () {
				var $dropdown = $(this);
				var $menu = $dropdown.find('.dropdown-menu').first();
				if (!$menu.length) return;

				$menu.removeClass('dropdown-menu-upward');
				$dropdown.removeClass('dropup');

				var rect = this.getBoundingClientRect();
				var menuHeight = $menu.outerHeight() || $menu.get(0).scrollHeight || 180;
				var needsUpward = (window.innerHeight - rect.bottom) < (menuHeight + 8);

				if (needsUpward) {
					if ($menu.hasClass('action-key')) {
						$menu.addClass('dropdown-menu-upward');
					} else {
						$dropdown.addClass('dropup');
					}
				}
			});

			$(document).on('hidden.bs.dropdown', '.dropdown', function () {
				$(this).removeClass('dropup');
				$(this).find('.dropdown-menu').removeClass('dropdown-menu-upward');
			});
		});
	</script>
	@if(session('welcome'))
		<script>
			(function () {
				if (!window.swal) return;
				var name = @json(session('welcome'));
				swal({
					title: 'Welcome back',
					text: name,
					type: 'success',
					customClass: 'welcome-swal',
					timer: 2000,
					showConfirmButton: false
				});
			})();
		</script>
	@endif
	@if(session('error'))
		<script>
			(function () {
				if (!window.swal) return;
				swal({
					title: 'Error',
					text: @json(session('error')),
					type: 'error'
				});
			})();
		</script>
		<script>
$(document).ready(function () {

    $('#show-hide-sidebar-toggle, .hamburger').on('click', function (e) {
        e.preventDefault();

        $('body').toggleClass('sidebar-hidden');
    });

});
</script>
		
	@endif
	@stack('scripts')
	
</body>

</html>
