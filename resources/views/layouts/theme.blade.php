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
  font-size: 14px; /* 12px */
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
.fa,
.fas {
font-family: "Font Awesome 6 Free" !important;
}
.user-shell{
	padding:0px !important;
}
.box-typical .panel-title{
	font-size: 22px !important;
  font-weight: 500 !important;
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
/* font-family: 'Proxima Nova', sans-serif; */
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
padding: 0.8375rem 1.25rem;
}

.mb-3,
.my-3 {
margin-bottom: 0 !important;
}
.side-menu-list {
margin: -79px 0 20px;
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
.campus-title{
	font-size: 22px !important;
}
.dataTables_wrapper .dataTables_filter label {
            position: relative;
            margin: 0;
            font-size: 0;
        }
		.dataTables_filter label{
    font-size:0;
}

.dataTables_filter input{
    font-size:13px;
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
padding: 9px 12px 6px 46px;
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
margin-right: 132px !important;
}

.site-header .dropdown .btn.dropdown-toggle {
height: 28px;
}
.site-header .dropdown-menu-notif .dropdown-menu-notif-list {
    height: 150px;
    overflow: hidden;
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
    margin-bottom: 8px !important;
	padding: 10px;
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
padding: 4px 12px !important;
display: flex;
    align-items: baseline !important;
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
font-weight:500 !important;
text-transform:uppercase;
margin-bottom:0.1875rem;
color: inherit !important;
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

.table .permission-action-dropdown .dropdown-menu.action-key,
.table .user-action-dropdown .dropdown-menu.action-key,
.table .role-action-dropdown .dropdown-menu.action-key {
    top: auto !important;
    bottom: 100% !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
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

.select2-container--arrow .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #343434;
    padding: .375rem 25px .375rem 1rem;
    min-height: 32px;
    background: #fff
}
.form-label{
    font-size: 11px;
    font-weight: 500 ;
    color: #343a40!important;
    text-transform: uppercase;
    margin-bottom: 3px;
    
}
.text-dark {
    color: #343a40 !important;
}

body, button, html, input, select, textarea {
    color: #343434;
    height: 32px;
    font-family: 'Proxima Nova', sans-serif;
    line-height: 1.4;
    text-rendering: optimizeLegibility;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    -o-font-smoothing: antialiased;
}
		.follow-shell {
			position: relative;
			min-height: 100vh;
			width: 100%;
			overflow: hidden;
		}

		.follow-loader {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 100vh;
			background: rgba(245, 247, 251, 0.95);
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			z-index: 10;
			gap: 12px;
		}

		.follow-spinner {
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.follow-spinner .dot {
			width: 12px;
			height: 12px;
			border-radius: 50%;
			background: #12a0ff;
			animation: bounce 0.9s ease-in-out infinite;
		}

		.follow-spinner .dot:nth-child(2) {
			animation-delay: 0.15s;
			background: #1f8ef1;
		}

		.follow-spinner .dot:nth-child(3) {
			animation-delay: 0.3s;
			background: #36b1ff;
		}

		.follow-loader p {
			margin: 0;
			color: #54667a;
			font-weight: 600;
		}

		.follow-content {
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.4s ease;
			position: relative;
			min-height: 400px;
		}

		body.follow-ready .follow-content {
			opacity: 1;
			visibility: visible;
		}

		body.follow-ready #follow-loader {
			display: none;
		}
		.follow-tab.active{
			color: #0f3c6e;
			background-color:white;	
			border-radius: 5px;
			border-bottom: 2px solid #008efb;
		}
		@keyframes bounce {
			0%, 80%, 100% {
				transform: translateY(0);
				opacity: 0.6;
			}
			40% {
				transform: translateY(-12px);
				opacity: 1;
			}
		}

		.follow-card {
			border: 1px solid #dbe4ed;
			border-radius: 10px;
			background: #fff;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}

		.follow-tab-bar {
			display: flex;
			flex-wrap: wrap;
			    padding: 14px 18px 10px;
    border-bottom: 3px solid #008efb;
			background: #f6f8fb;
			border-radius: 10px 10px 0 0;
		}

		.follow-tab {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			padding: 8px 6px;
			font-weight: 600;
			color: #5f6f7f;
			cursor: pointer;
			position: relative;
			border-bottom: 3px solid transparent;
		}

		.follow-tab.active {
			color: #0f3c6e;
			background-color:white;	
			border-radius: 5px;
			background-color:white;
		}

		.follow-tab .badge {
			border-radius: 999px;
			font-size: 11px;
			line-height: 1;
		}

		.follow-body {
			padding: 16px;
		}

		.follow-controls {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			padding: 12px;
		}

		.follow-search {
			position: relative;
			width: 240px;
		}

		.follow-search input {
			padding-right: 32px;
		}

		.follow-search i {
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			color: #9aa8b6;
		}

		.follow-table {
			margin-bottom: 12px;
			border: 1px solid #dbe4ed;
			text-align: center;

		}

		.follow-table thead th {
			background: #0099f8;
			color: #fff;
			font-weight: 700;
			border-color: #0086d8;
			vertical-align: middle;
		}

		.follow-table tbody td {
			vertical-align: middle;
			color: #334155;
			background: #fdfefe;
			border-color: #e6ecf2;
		}

		.follow-table tbody tr:nth-child(even) td {
			background: #f8fbff;
		}

		.follow-table tbody tr:hover td {
			background: #eef5ff;
		}

		.lead-link {
			color: #0099f8;
			font-weight: 700;
			text-decoration: none !important;
		}

		.lead-link:hover {
			color: #0086d8;
			text-decoration: none !important;
		}

		.follow-footer, .dataTables_wrapper .follow-footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-size: 13px;
			color: #343434;
			padding: 4px 4px 0;
		}

		.follow-table .action-cell {
			min-width: 110px;
			white-space: nowrap;
		}

		.table a {
    border-bottom: none !important;
  
}



		.table-responsive {
    overflow: visible !important;  
}
		.follow-card, .follow-body {
    overflow: visible !important;
}
	
		.follow-action-dropdown {
    position: relative;

}

.follow-action-dropdown .dropdown-menu {
	font-size:12px !important;
	min-width: 180px;
	position: absolute !important;
	top: 0 !important;
	left: auto !important;
	right: calc(100% + 2px) !important;
	margin: 0 !important;
	transform: none !important;
    z-index: 9999;       
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

    border: none !important;
    font-size: 16px !important;
    letter-spacing: 0.7px;
    padding: 2px 50px 2px 10px !important;
    font-weight: 500;
}
/* .btn.btn-primary:active, .btn.btn-primary:hover, button#lead-action-7, button#action-career-1, button#action-ahmed-khan-2, button#action-sara-iqbal-3, button#action-bilal-awan-4, button#action-areeba-fatima-6, button#action-zain-ali-5 {
  
  background-color: #00a8ff !important;
  border-color:#00a8ff !important;
} */
button[id^="action-"].btn,
button[id^="action-"].btn:hover,
button[id^="action-"].btn:focus,
button[id^="action-"].btn:active,
button[id^="action-"].btn.show,
button[id^="action-"].btn[aria-expanded="true"] {
    background-color: #00a8ff !important;
    border-color: #00a8ff !important;
    color: #fff !important;
}
.dropdown .btn-primary.show,
.dropdown .btn-primary:focus,
.dropdown .btn-primary:active {
    background-color:#00a8ff !important;
    border-color:#00a8ff !important;
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
    padding: 1px;
}

.box-typical.box-typical-dashboard {
margin: 1% 5px !important;
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
@media (max-width: 768px) {
.dataTables_wrapper .follow-controls, .dataTables_wrapper .follow-footer{
	flex-direction:column;
	align-items: flex-start;
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
