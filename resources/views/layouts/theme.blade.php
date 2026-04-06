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
  font-family: 'Proxima Nova', sans-serif !important;
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
  font-size: 14px !important; 
  line-height: 1.5;
}

button,
.btn,
a.btn,
input[type="button"],
input[type="submit"] {
  padding: 0.375rem 0.75rem  ; /* 6px 12px */
  height: 37px ; /* keep px */
  line-height: 1.5;
  
}

.btn[class*="-outline"] {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 37px;
  padding: 0.375rem 1rem !important;
  line-height: 1.2 !important;
}

.btn.btn-inline[class*="-outline"] {
  margin-right: 8px !important;
  margin-bottom: 8px !important;
}

.btn.btn-inline[class*="-outline"]:last-child {
  margin-right: 0 !important;
}

hr {
  margin: 1rem 0 !important;
}
.fa-classic,
.fa-regular,
.fa-solid,
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
      text-wrap: auto;
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
select {
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
.finance-summary-row { margin: 2px 0 10px; padding:7px;}
.finance-shell { padding: 8px 0 16px; background-color:white;}
.bootstrap-table .table td,
.fixed-table-body .table td,
.table td,
tr {
  height: 29px !important;
  padding: 0.1875rem 0.3125rem !important; /* 3px 5px */
  text-align: left !important;
}

/* Lead module tables */
.follow-shell .table,
.lead-status-shell .table,
.lead-show-shell .table,
.web-lead-detail-card .table {
  width: auto !important;
  min-width: 100% !important;
  max-width: none !important;
  table-layout: auto !important;
}

.follow-shell .table th,
.follow-shell .table td,
.lead-status-shell .table th,
.lead-status-shell .table td,
.lead-show-shell .table th,
.lead-show-shell .table td,
.web-lead-detail-card .table th,
.web-lead-detail-card .table td {
  width: auto !important;
  min-width: 0 !important;
  max-width: none !important;
  padding-left: 10px !important;
  padding-right: 10px !important;
}

.follow-shell .table .action-cell,
.lead-status-shell .table .action-cell,
.lead-show-shell .table .action-cell,
.web-lead-detail-card .table .action-cell {
  width: auto !important;
  min-width: 0 !important;
}

/* Student module tables */
.student-attendance-shell .table,
.student-directory .table {
  width: auto !important;
  min-width: 100% !important;
  max-width: none !important;
  table-layout: auto !important;
}

.student-attendance-shell .table th,
.student-attendance-shell .table td,
.student-directory .table th,
.student-directory .table td {
  width: auto !important;
  min-width: 0 !important;
  max-width: none !important;
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
font-size: 14.5px !important;
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
    font-size:13px !important
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
padding: 11px 12px 6px 46px;
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

/* .site-header .site-header-collapsed .site-header-collapsed-in {
margin-right: 132px !important;
} */
.control-panel .page-content {
    padding-right: 62px;
}
.with-side-menu .page-content {
    padding-left: 255px;
}
.page-content {
    padding: 107px 15px 10px;
    -webkit-transition: all .2s ease-in-out;
    transition: all .2s ease-in-out;
}
.site-header .dropdown .btn.dropdown-toggle {
height: 28px;
}
.site-header .dropdown-menu-notif .dropdown-menu-notif-list {
    height: 150px;
    overflow: hidden;
}
.site-header .dropdown.dropdown-notification .dropdown-menu-notif {
    left: auto !important;
    right: 0 !important;
    transform: none !important;
}
@media (max-width: 760px) {
    .site-header .dropdown.dropdown-notification .dropdown-menu-notif {
        right: 0 !important;
    }
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
.dataTables_wrapper {
  box-sizing: border-box;
  padding-left: 3px !important;
  padding-right: 12px !important;
}

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
.program-table thead th {
	background-color: #16b3fb;
}
.dataTables_wrapper table.dataTable {
width: auto !important;
min-width: 100%;
table-layout: auto !important;
}

.dataTables_wrapper table.dataTable th,
.dataTables_wrapper table.dataTable td {
width: auto !important;
text-align: left !important;

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
/* 
.row {
  align-items: flex-start !important;
  margin-left: -0.5rem;  
  margin-right: -0.5rem; 
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
} */



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
/* display: block; */
    margin-bottom: 6px;
    margin-top: 6px;
    font-size: 14px !important;
   color: #343a40 !important;
    text-transform: uppercase;
	font-weight:600;
}
/* =========================================================
   Shared Form Rhythm
   Match the spacing pattern used in the training lead form
   ========================================================= */
:root {
  --lead-form-row-padding-y: 3px;
  --lead-form-row-padding-x: 10px;
  --lead-form-row-gap: 8px;
  --lead-form-col-padding-x: 15px;
  --lead-form-group-margin: 6px;
  --lead-form-control-height: 2.25rem;
  --lead-form-control-padding-y: 0.375rem;
  --lead-form-control-padding-x: 0.625rem;
  --lead-form-textarea-min-height: 5rem;
}

form .form-row,
form .row {
  align-items: flex-start !important;
  gap: 0 !important;
  row-gap: var(--lead-form-row-gap) !important;
  padding: var(--lead-form-row-padding-y) var(--lead-form-row-padding-x) !important;
}

form .form-row > .col,
form .form-row > [class*=col-],
form .row > .col,
form .row > [class*=col-] {
  padding-left: var(--lead-form-col-padding-x) !important;
  padding-right: var(--lead-form-col-padding-x) !important;
}

form .form-group {
  margin-bottom: var(--lead-form-group-margin) !important;
}

form > .form-group {
  padding-left: calc(var(--lead-form-row-padding-x) + var(--lead-form-col-padding-x)) !important;
  padding-right: calc(var(--lead-form-row-padding-x) + var(--lead-form-col-padding-x)) !important;
}

form .form-control,
form .form-select,
form select.form-control,
form select.form-control-sm,
form input.form-control,
form input.form-control-sm,
form select.form-select-sm,
form textarea.form-control,
form textarea.form-control-sm {
  height: var(--lead-form-control-height) !important;
  min-height: var(--lead-form-control-height) !important;
  padding: var(--lead-form-control-padding-y) var(--lead-form-control-padding-x) !important;
  border-radius: 0.25rem !important;
}

form select.form-control,
form select.form-control-sm,
form .form-select,
form select.form-select-sm,
select.form-control,
select.form-control-sm {
  line-height: calc(var(--lead-form-control-height) - 2px) !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}

form textarea.form-control,
form textarea.form-control-sm {
  min-height: var(--lead-form-textarea-min-height) !important;
  height: var(--lead-form-textarea-min-height) !important;
  padding-top: 0.625rem !important;
  padding-bottom: 0.625rem !important;
  resize: vertical;
  line-height: 1.4 !important;
}

form .field-error {
  margin-top: 4px !important;
}

form .select2-container {
  width: 100% !important;
}

form .select2-container--default .select2-selection--single,
form .select2-container--white .select2-selection--single,
form .select2-container--default .select2-selection--multiple,
form .select2-container--white .select2-selection--multiple {
  height: var(--lead-form-control-height) !important;
  min-height: var(--lead-form-control-height) !important;
  border-radius: 0.25rem !important;
}

form .select2-container--default .select2-selection--single .select2-selection__rendered,
form .select2-container--white .select2-selection--single .select2-selection__rendered {
  height: var(--lead-form-control-height) !important;
  min-height: var(--lead-form-control-height) !important;
  line-height: calc(var(--lead-form-control-height) - 2px) !important;
  padding-left: var(--lead-form-control-padding-x) !important;
  padding-right: 2rem !important;
}

form .select2-container--default .select2-selection--single .select2-selection__arrow,
form .select2-container--white .select2-selection--single .select2-selection__arrow {
  height: var(--lead-form-control-height) !important;
}

form .select2-container--default .select2-selection--multiple .select2-selection__rendered,
form .select2-container--white .select2-selection--multiple .select2-selection__rendered {
  padding: 0.25rem var(--lead-form-control-padding-x) !important;
}

form .embed-actions,
form .form-actions {
  padding: 6px 10px 0 !important;
}

form > .text-right,
form > .form-actions,
form > .embed-actions {
  padding-left: calc(var(--lead-form-row-padding-x) + var(--lead-form-col-padding-x)) !important;
  padding-right: calc(var(--lead-form-row-padding-x) + var(--lead-form-col-padding-x)) !important;
}

.required::after,
.required-feild_symbol {
    color: red;
    font-size: 18px !important;
	margin-left: 1px;
}


.select2-container--arrow .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
border: solid 1px #d8e2e7;
border-radius: .25rem;
font-size: 16px !important;
/* font-weight:600; */
line-height: 35px !important;
color: #343434;
padding: 0 25px 0 1rem !important;
height: 35px !important;
min-height: 37px !important;
background: #fff;
}

.select2-container--arrow .select2-selection--single,
.select2-container--default .select2-selection--single,
.select2-container--white .select2-selection--single {
height: 37px !important;
min-height: 37px !important;
}

.select2-container--arrow .select2-selection--single .select2-selection__arrow,
.select2-container--default .select2-selection--single .select2-selection__arrow,
.select2-container--white .select2-selection--single .select2-selection__arrow {
height: 37px !important;
}
.select2-results__option{
font-size: 16px !important;

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
.student-action-dropdown,
.campus-action-dropdown,
.batch-action-dropdown,
.program-action-dropdown,
.inventory-action-dropdown,
.action-cell,
td.actions-cell,
.inventory-action-cell,
.table td > .dropdown {
position: relative;
}

.action-cell,
td.actions-cell,
.inventory-action-cell {
min-width: 110px;
white-space: nowrap;
}

.table .action-cell > .dropdown,
.table td.actions-cell > .dropdown,
.table td > .dropdown,
.table [class*="-action-dropdown"] {
display: flex;
justify-content: flex-start;
width: max-content;
max-width: 100%;
margin-left: auto;
margin-right: auto;
}

.dropdown.dropdown-action-menu,
.table .action-cell > .dropdown.dropdown-action-menu,
.table td.actions-cell > .dropdown.dropdown-action-menu,
.table td > .dropdown.dropdown-action-menu,
.table [class*="-action-dropdown"].dropdown-action-menu {
z-index: 99970 !important;
}

.dropdown-menu.action-key,
.follow-action-dropdown .dropdown-menu,
.registration-action-dropdown .dropdown-menu {
font-size: 12px !important;
min-width: 180px;
z-index: 99970 !important;
}

.dropdown.dropdown-action-menu > .dropdown-menu,
.table .action-cell > .dropdown.dropdown-action-menu > .dropdown-menu,
.table td.actions-cell > .dropdown.dropdown-action-menu > .dropdown-menu,
.table td > .dropdown.dropdown-action-menu > .dropdown-menu,
.table [class*="-action-dropdown"].dropdown-action-menu > .dropdown-menu {
position: absolute !important;
top: 0 !important;
bottom: auto !important;
left: auto !important;
right: 100% !important;
margin: 0 !important;
transform: none !important;
z-index: 1060 !important;
}

.dropdown.dropdown-action-menu > .dropdown-menu.dropdown-menu-upward,
.table .action-cell > .dropdown.dropdown-action-menu > .dropdown-menu.dropdown-menu-upward,
.table td.actions-cell > .dropdown.dropdown-action-menu > .dropdown-menu.dropdown-menu-upward,
.table td > .dropdown.dropdown-action-menu > .dropdown-menu.dropdown-menu-upward,
.table [class*="-action-dropdown"].dropdown-action-menu > .dropdown-menu.dropdown-menu-upward {
top: 0 !important;
bottom: auto !important;
left: auto !important;
right: 100% !important;
margin: 0 !important;
transform: none !important;
z-index: 1060 !important;
}

.dropdown.dropdown-action-menu.show,
.table .action-cell > .dropdown.dropdown-action-menu.show,
.table td.actions-cell > .dropdown.dropdown-action-menu.show,
.table td > .dropdown.dropdown-action-menu.show,
.table [class*="-action-dropdown"].dropdown-action-menu.show {
z-index: 1065 !important;
}

.dropdown.dropdown-action-menu.show > .dropdown-menu,
.table .action-cell > .dropdown.dropdown-action-menu.show > .dropdown-menu,
.table td.actions-cell > .dropdown.dropdown-action-menu.show > .dropdown-menu,
.table td > .dropdown.dropdown-action-menu.show > .dropdown-menu,
.table [class*="-action-dropdown"].dropdown-action-menu.show > .dropdown-menu {
z-index: 1070 !important;
}

.table .action-cell .dropdown-toggle,
.table td.actions-cell .dropdown-toggle,
.table td > .dropdown > .dropdown-toggle,
.table [class*="-action-dropdown"] .dropdown-toggle {
height: 22px !important;
padding: 1px 8px !important;
min-width: 68px !important;
font-size: 13px !important;
line-height: 1.2 !important;
text-align: center !important;
}

.btn{
	font-size:16px !important;
}
.text-dark {
    color: #343a40 !important;
}

body, button, html, input, select {
    color: #343434;
    height: 37px !important;
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
.student-directory .dataTables_wrapper .follow-controls, .student-directory .dataTables_wrapper .follow-footer{
	    align-items: flex-start !important;
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
	right: 100% !important;
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
.form-label-mute{
	 margin-bottom: 6px;
    margin-top: 6px;
    font-size: 14px !important;
    text-transform: uppercase;
	font-weight:600;
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
height: 13px !important;
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
margin:  5px !important;
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
padding-top: 10px;
}
}
@media (max-width: 768px) {
form .form-row,
form .row {
	padding-left: 6px !important;
	padding-right: 6px !important;
}

form .form-row > .col,
form .form-row > [class*=col-],
form .row > .col,
form .row > [class*=col-] {
	padding-left: 10px !important;
	padding-right: 10px !important;
}

form > .form-group,
form > .text-right,
form > .form-actions,
form > .embed-actions {
	padding-left: 16px !important;
	padding-right: 16px !important;
}

.dataTables_wrapper {
	overflow-x: auto !important;
	-webkit-overflow-scrolling: touch;
}

.table-responsive {
	overflow-x: auto !important;
	-webkit-overflow-scrolling: touch;
}

.table-responsive > .table,
.table-responsive > table {
	width: max-content !important;
	min-width: 100% !important;
}

.table-responsive > .table th,
.table-responsive > .table td,
.table-responsive > table th,
.table-responsive > table td {
	white-space: nowrap;
}

.dataTables_wrapper .table-responsive {
	overflow-x: auto !important;
	overflow-y: hidden !important;
	-webkit-overflow-scrolling: touch;
}

.dataTables_wrapper table.dataTable {
	width: max-content !important;
	min-width: 100% !important;
}

.dataTables_wrapper table.dataTable th,
.dataTables_wrapper table.dataTable td {
	white-space: nowrap;
}

.dataTables_wrapper .follow-controls, .dataTables_wrapper .follow-footer{
	flex-direction:column;
	align-items: flex-start;
}
.follow-footer {
flex-direction: column;
margin-top:10px;
text
}
.font-icon-menu-addl{
	font-size: 24px !important;
}
 .site-header .site-header-collapsed {
       
        z-index: 125;
}
.site-header .site-header-collapsed .site-header-collapsed-in {
margin-right: 0px !important;
}
.summary-tile {
	    padding: 22px 14px;
}
    .site-header .site-header-collapsed .site-header-search.closed {
        width: 100% !important;
        border-color: #c5d6de;
    }
.student-summary-grid {
    display: grid;
    gap: 14px;
    padding: 15px;
}

@media (min-width: 768px) and (max-width: 991.98px) {
    [class*="summary-grid"],
    [class*="scope-grid"],
    .inventory-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

@media (max-width: 767.98px) {
    [class*="summary-grid"],
    [class*="scope-grid"],
    .inventory-summary {
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
}
.program-discount-header,
.box-typical.box-typical-dashboard .box-typical-header{
	flex-direction: column;
	padding:5px;
	align-items: left !important;
}
.tables-dashbord{
	gap:25px;
}
.kpi-label{
	padding: 0px !important;
	margin-top: 0px !important;

}
.kpi-value, .batch-scope-card strong , .program-scope-card,.campus-scope-card{
          
	margin-top: 5px !important;
}

}
@media (max-width: 1056px) {
	.site-header .hamburger {
top: 0px;
}
.font-icon-menu-addl::before{
	top: -6px !important;
}
}

/* >= 768px */
/* @media (min-width: 768px) {
.form-row {
display: flex;

}
} */
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
	<style>
		/* =========================================================
   Global Page Spacing Overrides
   ========================================================= */
		.control-panel .page-content {
			padding-right: 67px !important;
		}

		.with-side-menu .page-content {
			padding-left: 38px !important;
		}

		.page-content {
			padding: 83px 15px 10px !important;
			-webkit-transition: all .2s ease-in-out;
			transition: all .2s ease-in-out;
		}

		body.with-side-menu .page-content > .container-fluid {
			max-width: 100% !important;
			margin: 0 !important;
			padding-right: 0 !important;
			height: auto !important;
		}

		.menu-left-hidden .page-content,
		body.sidebar-hidden .page-content {
			padding-left: 15px !important;
		}

		@media (max-width: 1056px) {
			body.with-side-menu .page-content,
			body.sidebar-hidden .page-content,
			.menu-left-hidden .page-content {
				padding-left: 15px !important;
			}

			body.with-side-menu .page-content > .container-fluid,
			body.sidebar-hidden .page-content > .container-fluid,
			.menu-left-hidden .page-content > .container-fluid {
				padding-left: 0 !important;
				padding-right: 0 !important;
			}

			body.with-side-menu .page-content > .container-fluid > .container-fluid,
			body.sidebar-hidden .page-content > .container-fluid > .container-fluid,
			.menu-left-hidden .page-content > .container-fluid > .container-fluid {
				padding-left: 0 !important;
				padding-right: 0 !important;
			}
		}

		@media (max-width: 767px) {
			body.with-side-menu .page-content,
			body.control-panel.open.with-side-menu .page-content,
			body.sidebar-hidden.with-side-menu .page-content,
			.menu-left-hidden .page-content {
				padding: 107px 15px 10px !important;
				margin-top: 14px !important;
			}
			.chart-statistic-box .chart-container{
				background-color:white;
				gap:30px;
			}
			    .chart-statistic-box .chart-txt{
					        border-radius: 4px 4px 4px 4px;
				}
				.chart-statistic-box .chart-container-in{
					        border-radius: 4px 4px 4px 4px;

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
			function clearDataTableInlineWidths(table) {
				if (!table) return;

				var $table = $(table);
				var cellSelector = 'thead th, tbody td, tfoot th, tfoot td';

				$table.css('width', '');
				$table.removeAttr('width');

				$table.find('colgroup col').each(function () {
					this.style.width = '';
					this.style.minWidth = '';
					this.style.maxWidth = '';
					this.removeAttribute('width');
				});

				$table.find(cellSelector).each(function () {
					this.style.width = '';
					this.style.minWidth = '';
					this.style.maxWidth = '';
					this.removeAttribute('width');
				});
			}

			function queueDataTableWidthCleanup(table) {
				if (window.requestAnimationFrame) {
					window.requestAnimationFrame(function () {
						clearDataTableInlineWidths(table);
					});
					return;
				}

				setTimeout(function () {
					clearDataTableInlineWidths(table);
				}, 0);
			}

			$(document).on('init.dt draw.dt column-sizing.dt', function (event, settings) {
				if (!settings || !settings.nTable) return;
				queueDataTableWidthCleanup(settings.nTable);
			});

			function isActionDropdown($dropdown, $menu) {
				var dropdownClass = $dropdown.attr('class') || '';
				var toggleText = $.trim($dropdown.children('.dropdown-toggle').first().text()).toLowerCase();

				return dropdownClass.indexOf('-action-dropdown') !== -1 ||
					$dropdown.closest('.action-cell, td.actions-cell, .inventory-action-cell').length > 0 ||
					$menu.hasClass('action-key') ||
					toggleText === 'action' ||
					toggleText === 'actions';
			}

			$(document).on('shown.bs.dropdown', '.dropdown', function () {
				var $dropdown = $(this);
				var $menu = $dropdown.find('.dropdown-menu').first();
				if (!$menu.length) return;
				var actionDropdown = isActionDropdown($dropdown, $menu);

				$menu.removeClass('dropdown-menu-upward');
				$dropdown.removeClass('dropup');
				$dropdown.toggleClass('dropdown-action-menu', actionDropdown);

				if (actionDropdown) {
					$menu.addClass('dropdown-menu-upward');
					return;
				}

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
				$(this).removeClass('dropup dropdown-action-menu');
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
