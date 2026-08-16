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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
	<link rel="preload" href="fonts/Proxima_Nova_Regular.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="fonts/Proxima_Nova_Semibold.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">
			<link rel="stylesheet" href="css/custom-responsive.css">

	@stack('styles')
	<style>
        :root {
            --typo-layouts-theme-font-size-1: 14px;
            --typo-layouts-theme-font-size-2: 22px;
            --typo-layouts-theme-font-size-3: 18px;
            --typo-layouts-theme-font-size-4: 16px;
            --typo-layouts-theme-font-size-5: 13px;
            --typo-layouts-theme-font-size-6: 12px;
            --typo-layouts-theme-font-size-7: 14px;
            --typo-layouts-theme-line-height-8: 1.5;
            --typo-layouts-theme-line-height-9: 1.2;
            --typo-layouts-theme-font-family-10: 'Proxima Nova', sans-serif;
            --typo-layouts-theme-line-height-11: 1.4;
            --typo-layouts-theme-font-size-12: 0;
            --typo-layouts-theme-line-height-13: 1;
            --typo-layouts-theme-line-height-14: 1;
            --typo-layouts-theme-font-weight-15: 600;
            --typo-layouts-theme-line-height-16: calc(var(--lead-form-control-height) - 2px);
            --typo-layouts-theme-line-height-17: 35px;
            --typo-layouts-theme-font-weight-18: 500;
            --typo-layouts-theme-font-size-19: 12px;
        }

		/* =========================================================
   Base (Mobile First)
   ========================================================= */
* {
  font-family: 'Proxima Nova', sans-serif !important;
  font-size: var(--typo-layouts-theme-font-size-1); /* 12px */
  margin: 0;
  padding: 0;
}
h1 { font-size: 1.875rem !important; }
h2 { font-size: 1.625rem!important; }
h3 { font-size: var(--typo-layouts-theme-font-size-2) !important; }
h4 { font-size: var(--typo-layouts-theme-font-size-3) !important; }
h5 { font-size: var(--typo-layouts-theme-font-size-4) !important; }

p { font-size: var(--typo-layouts-theme-font-size-1); }
span { font-size: var(--typo-layouts-theme-font-size-5); }
small { font-size: var(--typo-layouts-theme-font-size-6); }

.bi{
	font-size: var(--typo-layouts-theme-font-size-3) !important;
}

button {
  font-size: var(--typo-layouts-theme-font-size-7) !important;
  line-height: var(--typo-layouts-theme-line-height-8);
}
span{
	font-size: var(--typo-layouts-theme-font-size-1);
  line-height: var(--typo-layouts-theme-line-height-8);
}
input,select,textarea{
	font-size: var(--typo-layouts-theme-font-size-4) !important;
  line-height: var(--typo-layouts-theme-line-height-8);
}
button,
.btn,
a.btn,
input[type="button"],
input[type="submit"] {
  padding: 0.375rem 0.75rem  ; /* 6px 12px */
  height: 37px ; /* keep px */
  line-height: var(--typo-layouts-theme-line-height-8);

}

.btn[class*="-outline"] {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 37px;
  padding: 0.375rem 1rem !important;
  line-height: var(--typo-layouts-theme-line-height-9) !important;
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
.fa-brands,
.far,
.fas {
font-family: "Font Awesome 6 Free" !important;
}

.fa {
font-family: FontAwesome !important;
}
.box-typical .panel-title{
	font-size: 1.625rem !important;
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
 input:not([type="radio"]):not([type="checkbox"]):not([type="range"]) {

    min-height: 28px !important;
    color: #343434;
/* font-family: var(--typo-layouts-theme-font-family-10); */
line-height: var(--typo-layouts-theme-line-height-11);
/* min-height:32px !important; */
text-rendering: optimizeLegibility;
-moz-osx-font-smoothing: grayscale;
-webkit-font-smoothing: antialiased;
}
body,
html,
button,

select {
color: #343434;
/* font-family: var(--typo-layouts-theme-font-family-10); */
line-height: var(--typo-layouts-theme-line-height-11);
min-height:32px !important;
text-rendering: optimizeLegibility;
-moz-osx-font-smoothing: grayscale;
-webkit-font-smoothing: antialiased;
}

textarea[name="remarks"],
textarea[name$="[remarks]"] {
  height: 100px !important;
  min-height: 100px !important;
}

/* =========================================================
   Tables
   ========================================================= */
.table {
  width: 100%;
  max-width: 100%;
  /* margin-left: 0.1875rem !important;  */
}

.table th {
  padding: 0.6875rem 0.3125rem 0.625rem !important;
}
.table th{
height: auto !important;

}
.table td {
  height: auto !important;
  font-size: var(--typo-layouts-theme-font-size-7) !important;
}

.table a {
border-bottom: 1px solid #e9ecef;
}
.finance-summary-row { margin: 2px 0 10px; padding:7px;}
.finance-shell { padding: 8px 0 16px; background-color:white;}
.bootstrap-table .table td,
.fixed-table-body .table td,
.table td {
  height: auto !important;
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
.campus-title{
	font-size: var(--typo-layouts-theme-font-size-2) !important;
}
.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label {
            position: relative;
            margin: 0;
            font-size: var(--typo-layouts-theme-font-size-12);
        }
		.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label{
    font-size: var(--typo-layouts-theme-font-size-12);
}

.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input{
    font-size:0.8125rem !important
}
/* .site-header .site-header-collapsed .site-header-collapsed-in {
margin-right: 132px !important;
} */

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
font-size: var(--typo-layouts-theme-font-size-1);
display: inline-flex;
align-items: center;
}

.login-logs .box-typical-body {
/* padding: 10px 16px !important; */
}
/* body.with-side-menu.control-panel .page-content {
    padding-right: 16px !important;
} */

/* =========================================================
   DataTables
   ========================================================= */
.dataTables_wrapper {
  box-sizing: border-box;
  padding:10px;
}

.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input,
.login-logs .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input {
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
			padding: 3px 15px !important;
			width:200px;
		}
div.dataTables_wrapper div.dataTables_info {
padding-top: 1em;
}
div.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) div.dataTables_filter input{
	width:200px;
}div.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) div.dataTables_filter label {
    font-weight: 0px !important;
    white-space: nowrap;
    text-align: left;}
.dataTables_length {
padding-left: 10px !important;
}
.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar), .dataTables_wrapper .follow-footer {
    display: flex;
    align-items: center;
    justify-content: space-between !important;
    /* gap: 488px !important; */
    /* margin-bottom: 8px !important;
	padding: 10px; */
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

.page-content table thead th.follow-sortable,
.page-content table.dataTable thead th.sorting,
.page-content table.dataTable thead th.sorting_asc,
.page-content table.dataTable thead th.sorting_desc {
    position: relative;
    cursor: pointer;
    padding-right: 26px !important;
    user-select: none;
}

.page-content table thead th.follow-sortable::before,
.page-content table thead th.follow-sortable::after,
.page-content table.dataTable thead th.sorting::before,
.page-content table.dataTable thead th.sorting::after,
.page-content table.dataTable thead th.sorting_asc::before,
.page-content table.dataTable thead th.sorting_asc::after,
.page-content table.dataTable thead th.sorting_desc::before,
.page-content table.dataTable thead th.sorting_desc::after {
    position: absolute;
    right: 10px;
    color: currentColor;
    opacity: 0.28;
    font-size: var(--typo-layouts-theme-font-size-6);
    line-height: var(--typo-layouts-theme-line-height-13);
}

.page-content table thead th.follow-sortable::before,
.page-content table.dataTable thead th.sorting::before,
.page-content table.dataTable thead th.sorting_asc::before,
.page-content table.dataTable thead th.sorting_desc::before {
    content: "▲";
    top: calc(50% - 11px);
}

.page-content table thead th.follow-sortable::after,
.page-content table.dataTable thead th.sorting::after,
.page-content table.dataTable thead th.sorting_asc::after,
.page-content table.dataTable thead th.sorting_desc::after {
    content: "▼";
    top: calc(50% + 1px);
}

.page-content table thead th.follow-sortable.is-sorted-asc::before,
.page-content table.dataTable thead th.sorting_asc::before {
    opacity: 0.78;
}

.page-content table thead th.follow-sortable.is-sorted-desc::after,
.page-content table.dataTable thead th.sorting_desc::after {
    opacity: 0.78;
}

.table-responsive {
text-align: left !important;
}
.followup-table-wrapper {
height: 26vh !important;
}

.follow-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0px 2px 12px 2px;
    /* margin-left: 4px; */
}

.follow-controls.follow-controls--toolbar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
    padding: 0 2px 12px 2px !important;
    margin: 0 !important;
    width: 100% !important;
}

.dataTables_wrapper .follow-controls.follow-controls--toolbar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    padding: 0 2px 12px 2px !important;
    margin: 0 !important;
}

.follow-controls.follow-controls--toolbar .follow-controls-search-group {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 14px !important;
    flex: 0 1 auto !important;
    min-width: 280px !important;
    margin-left: auto !important;
}

.dataTables_wrapper .follow-controls.follow-controls--toolbar .follow-controls-search-group {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 14px !important;
    margin-left: auto !important;
}

.follow-controls.follow-controls--toolbar .dataTables_filter,
.follow-controls.follow-controls--toolbar .follow-search {
    position: relative !important;
    margin: 0 !important;
    float: none !important;
}

.follow-controls.follow-controls--toolbar .dataTables_filter label {
    margin: 0;
    width: 100%;
    font-size: var(--typo-layouts-theme-font-size-12);
    color: transparent !important;
    padding: 0 !important;
    display: block !important;
}

.follow-controls.follow-controls--toolbar .dataTables_filter label::after {
    display: none !important;
    content: none !important;
}

.follow-controls.follow-controls--toolbar .dataTables_filter input,
.follow-controls.follow-controls--toolbar .follow-search input {
    width: 240px !important;
    max-width: 100%;
    height: 36px !important;
    border-radius: 999px !important;
    border: 1px solid #d7e5f1 !important;
    background: #fff !important;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
    padding: 0px 18px !important;
    font-size: var(--typo-layouts-theme-font-size-7) !important;
    color: #6f8ca3 !important;
    margin: 0 !important;
    vertical-align: middle !important;
}

.follow-controls.follow-controls--toolbar .dataTables_filter input::placeholder,
.follow-controls.follow-controls--toolbar .follow-search input::placeholder {
    color: #8ca3b6;
}

.follow-controls.follow-controls--toolbar .follow-controls-tools {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    margin: 0 !important;
    flex-wrap: nowrap !important;
}

.follow-toolbar-group {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: nowrap !important;
}

.follow-toolbar-btn,
.follow-toolbar-dropdown > .btn {
    width: 34px;
    min-width: 32px;
    height: 32px !important;
    min-height: 32px;
    padding: 0 !important;
    border-radius: 6px !important;
    border: 0 !important;
    background: transparent !important;
    color: #8da4b8 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    box-shadow: none !important;
    overflow: visible !important;
    text-indent: 0 !important;
    white-space: nowrap !important;
    font-size: var(--typo-layouts-theme-font-size-3) !important;
    line-height: var(--typo-layouts-theme-line-height-14) !important;
}

.follow-toolbar-btn:hover,
.follow-toolbar-btn:focus,
.follow-toolbar-dropdown > .btn:hover,
.follow-toolbar-dropdown > .btn:focus,
.follow-toolbar-dropdown.open > .btn {
    color: #587992 !important;
    border-color: transparent !important;
    background: transparent !important;
}

.follow-toolbar-btn .glyphicon,
.follow-toolbar-dropdown > .btn .glyphicon,
.follow-toolbar-btn .font-icon,
.follow-toolbar-dropdown > .btn .font-icon,
.follow-toolbar-btn .fa,
.follow-toolbar-dropdown > .btn .fa {
    display: inline-block;
    font-size: var(--typo-layouts-theme-font-size-3) !important;
    line-height: var(--typo-layouts-theme-line-height-13);
    opacity: 1 !important;
    visibility: visible !important;
    color: inherit !important;
}

.follow-toolbar-btn .fa,
.follow-toolbar-dropdown > .btn .fa,
.follow-toolbar-btn .fa::before,
.follow-toolbar-dropdown > .btn .fa::before {
    font-family: FontAwesome !important;
    font-style: normal !important;
    font-weight: normal !important;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.follow-toolbar-btn .fa::before,
.follow-toolbar-dropdown > .btn .fa::before,
.follow-toolbar-btn .font-icon::before,
.follow-toolbar-dropdown > .btn .font-icon::before,
.follow-toolbar-btn .glyphicon::before,
.follow-toolbar-dropdown > .btn .glyphicon::before {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    color: inherit !important;
    text-indent: 0 !important;
}

.follow-toolbar-btn .font-icon::before,
.follow-toolbar-dropdown > .btn .font-icon::before {
    font-family: startui !important;
    font-style: normal !important;
    font-weight: 400 !important;
    font-variant: normal !important;
    text-transform: none !important;
    speak: none;
    line-height: var(--typo-layouts-theme-line-height-14) !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    display: inline-block;
}

.follow-toolbar-btn i,
.follow-toolbar-dropdown > .btn i {
    font-size: var(--typo-layouts-theme-font-size-3) !important;
    line-height: var(--typo-layouts-theme-line-height-13);
}

.follow-toolbar-dropdown > .btn .caret {
    margin-left: 0;
    border-top-color: currentColor;
}

.follow-toolbar-dropdown > .btn.follow-toolbar-split .caret {
    margin-top: 2px;
}

.follow-toolbar-dropdown .dropdown-menu {
    min-width: 180px;
    padding: 8px 0;
    border-radius: 10px;
    border: 1px solid #d7e5f1;
    box-shadow: 0 12px 30px rgba(31, 60, 92, 0.12);
    left: -53px !important;
    /* right: 25px !important; */
    /* transform: none !important; */
    margin-top: 6px !important;
}

.follow-toolbar-dropdown.open > .dropdown-menu,
.follow-toolbar-dropdown.show > .dropdown-menu,
.follow-toolbar-dropdown > .dropdown-menu.show {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.follow-toolbar-dropdown .dropdown-menu > li > a,
.follow-toolbar-dropdown .dropdown-menu > li > button,
.follow-toolbar-dropdown .dropdown-menu .dropdown-item {
    display: block;
    width: 100%;
    padding: 8px 14px;
    color: #466277;
    background: transparent;
    border: 0;
    text-align: left;
    font-size: var(--typo-layouts-theme-font-size-5);
}

.follow-toolbar-dropdown .dropdown-menu > li > a:hover,
.follow-toolbar-dropdown .dropdown-menu > li > button:hover,
.follow-toolbar-dropdown .dropdown-menu .dropdown-item:hover {
    background: #f2f8fc;
    color: #24445b;
}

.follow-toolbar-dropdown .dropdown-menu .checkbox {
    margin: 0;
    padding: 0;
}

.follow-toolbar-dropdown .dropdown-menu .follow-column-option {
    padding: 0;
}

.follow-toolbar-dropdown .dropdown-menu .checkbox label {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 8px;
    margin: 0;
    padding: 8px 14px;
    width: 100%;
    cursor: pointer;
    color: #466277;
    font-size: var(--typo-layouts-theme-font-size-5);
}

.follow-toolbar-dropdown .dropdown-menu .checkbox label:hover {
    background: #f2f8fc;
}

.follow-toolbar-dropdown .dropdown-menu .checkbox input {
    margin: 0;
    position: static;
}

.follow-toolbar-dropdown .dropdown-menu .follow-column-option input[type="checkbox"] {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    accent-color: #00a8ff;
}

.follow-controls.follow-controls--toolbar .follow-search {
    width: auto !important;
}

.follow-controls.follow-controls--toolbar .follow-search i {
    display: none !important;
}

.follow-controls .select2-container {
    min-height: 31px !important;
}

.follow-controls.follow-controls--toolbar > .dataTables_length,
.follow-controls.follow-controls--toolbar > .follow-controls-length,
.follow-controls.follow-controls--toolbar > .d-flex:first-child {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
    padding: 0 !important;
    flex: 0 0 auto !important;
}

.follow-controls.follow-controls--toolbar .dataTables_length,
.follow-controls.follow-controls--toolbar .dataTables_filter {
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

.follow-controls.follow-controls--toolbar .dataTables_length label,
.follow-controls.follow-controls--toolbar .follow-controls-length label {
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: var(--typo-layouts-theme-font-size-7) !important;
    color: #343434 !important;
}

.follow-controls.follow-controls--toolbar .dataTables_length select {
    height: 31px !important;
    min-height: 31px !important;
    padding: -3px 24px 4px 10px !important;
    margin: 0 !important;
}

.follow-controls .select2-container .select2-selection--single {
    height: 31px !important;
    min-height: 31px !important;
    border-radius: 6px !important;
}

.follow-controls .select2-container .select2-selection--single .select2-selection__rendered {
    height: 31px !important;
    min-height: 31px !important;
    line-height: 31px !important;
    padding-left: 10px !important;
    padding-right: 28px !important;
}

.follow-controls .select2-container .select2-selection--single .select2-selection__arrow {
    height: 31px !important;
    /* right: 6px !important; */
}

.follow-table-density-compact th,
.follow-table-density-compact td {
    padding-top: 0.12rem !important;
    padding-bottom: 0.12rem !important;
}

.follow-footer {
padding: 63px 2px !important;
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
    margin-bottom: 6px;
    margin-top: 6px;
    font-size: 0.8rem !important;
    color: #343a40 !important;
    text-transform: uppercase;
    font-weight: var(--typo-layouts-theme-font-weight-15);
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
  --lead-form-control-height: 37px;
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
  line-height: var(--typo-layouts-theme-line-height-16) !important;
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

form textarea[name="remarks"].form-control,
form textarea[name="remarks"].form-control-sm,
form textarea[name$="[remarks]"].form-control,
form textarea[name$="[remarks]"].form-control-sm {
  min-height: 90px !important;
  height: 90px !important;
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
  line-height: var(--typo-layouts-theme-line-height-16) !important;
  padding-left: var(--lead-form-control-padding-x) !important;
  padding-right: 2rem !important;
}

form .select2-container--default .select2-selection--single .select2-selection__arrow,
form .select2-container--white .select2-selection--single .select2-selection__arrow {
  height: 37px !important;
}

form .select2-container--default .select2-selection--multiple .select2-selection__rendered,
form .select2-container--white .select2-selection--multiple .select2-selection__rendered {
  /* padding: 0.25rem var(--lead-form-control-padding-x) !important; */
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

form label.required::after,
.required-feild_symbol {
    content: ' *';
    color: red;
    font-size: var(--typo-layouts-theme-font-size-3) !important;
	margin-left: 0;
	line-height: var(--typo-layouts-theme-line-height-13);
	flex: 0 0 auto;
}
.select2-container--arrow, .select2-selection__rendered{
	/* border: solid 1px #d8e2e7; */
border-radius: .25rem;
font-size: var(--typo-layouts-theme-font-size-4) !important;
/* font-weight: var(--typo-layouts-theme-font-weight-15); */
line-height: var(--typo-layouts-theme-line-height-17) !important;
color: #343434;
padding: 0 25px 0 1rem !important;
height: 36px !important;
min-height: 36px !important;
background: #fff;
}

 .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
border: solid 1px #d8e2e7;
border-radius: .25rem;
font-size: var(--typo-layouts-theme-font-size-4) !important;
/* font-weight: var(--typo-layouts-theme-font-weight-15); */
line-height: var(--typo-layouts-theme-line-height-17) !important;
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
font-size: var(--typo-layouts-theme-font-size-4) !important;

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
/* margin-left: auto; */
margin-right: auto;
}

.dropdown.dropdown-action-menu,
.table .action-cell > .dropdown.dropdown-action-menu,
.table td.actions-cell > .dropdown.dropdown-action-menu,
.table td > .dropdown.dropdown-action-menu,
.table [class*="-action-dropdown"].dropdown-action-menu {
z-index: auto !important;
}

.dropdown-menu.action-key,
.follow-action-dropdown .dropdown-menu,
.registration-action-dropdown .dropdown-menu {
font-size: var(--typo-layouts-theme-font-size-4) !important;
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
top: 23px !important;
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
z-index: auto !important;
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
font-size: 0.8125rem !important;
line-height: var(--typo-layouts-theme-line-height-9) !important;
text-align: center !important;
z-index: auto !important;
background-color:#00a8ff;
}

.btn{
	font-size: var(--typo-layouts-theme-font-size-4) !important;
}
.text-dark {
    color: #343a40 !important;
}

body,
html {
    color: #343434;
    font-family: var(--typo-layouts-theme-font-family-10);
    line-height: var(--typo-layouts-theme-line-height-11);
    text-rendering: optimizeLegibility;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    -o-font-smoothing: antialiased;
}

button,
input,
select {
    color: #343434;
    height: 37px !important;
    font-family: var(--typo-layouts-theme-font-family-10);
    line-height: var(--typo-layouts-theme-line-height-11);
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
			font-weight: var(--typo-layouts-theme-font-weight-15);
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
			gap: 2px !important;
			padding: 8px 2px;
			font-weight: var(--typo-layouts-theme-font-weight-18);
			color: #5f6f7f;
			cursor: pointer;
			position: relative;
			border-bottom: 3px solid transparent;
		}
		.student-directory .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar), .student-directory .dataTables_wrapper .follow-footer{
				align-items: flex-start !important;
		}
		.follow-tab.active {
			color: #0f3c6e;
			background-color:white;
			border-radius: 5px;
			background-color:white;
		}

		.follow-tab .badge {
			padding:4px 8px;
			border-radius: 999px;
			font-size: 0.6875rem;
			line-height: var(--typo-layouts-theme-line-height-13);
		}

		.follow-body {
			padding: 16px;
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
			color: #0082c6;
			/* font-weight: var(--typo-layouts-theme-font-weight-15); */
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
			font-size: var(--typo-layouts-theme-font-size-5);
			color: #343434;
			padding: 4px 4px 0;
		}

		.follow-table .action-cell {
			min-width: 110px;
			white-space: nowrap;
		}

		.page-content .table thead th,
		.page-content .dataTables_wrapper table.dataTable thead th,
		.page-content .bootstrap-table .table thead th,
		.page-content .fixed-table-body .table thead th,
		.page-content .tbl-typical th,
		.page-content .follow-table thead th,
		.page-content .program-table thead th {
			background: #00A8FF !important;
			background-color: #00A8FF !important;
			color: #fff !important;
			border-color: #00A8FF ;
		}

		.page-content .tbl-typical th > div::before {
			background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0, rgba(255, 255, 255, 0.38) 77%, rgba(255, 255, 255, 0.38) 100%) !important;
		}

		.page-content .table,
		.page-content .dataTables_wrapper table.dataTable,
		.page-content .bootstrap-table .table,
		.page-content .fixed-table-body .table,
		.page-content .tbl-typical,
		.page-content .follow-table,
		.page-content .program-table {
			/* width: max-content !important; */
			min-width: 100% !important;
			max-width: none !important;
			table-layout: auto !important;
		}

		.page-content .table thead th,
		.page-content .table tbody td,
		.page-content .table tfoot th,
		.page-content .table tfoot td,
		.page-content .dataTables_wrapper table.dataTable thead th,
		.page-content .dataTables_wrapper table.dataTable tbody td,
		.page-content .bootstrap-table .table thead th,
		.page-content .bootstrap-table .table tbody td,
		.page-content .fixed-table-body .table thead th,
		.page-content .fixed-table-body .table tbody td,
		.page-content .tbl-typical th,
		.page-content .tbl-typical td,
		.page-content .follow-table thead th,
		.page-content .follow-table tbody td,
		.page-content .program-table thead th,
		.page-content .program-table tbody td {
			width: auto !important;
			max-width:154px !important;
			height: 30px !important;
			text-align: left !important;
			vertical-align: center !important;
			padding-top: 0.25rem !important;
			padding-right: 4px !important;
			padding-bottom: 2px !important;
			padding-left: 8px !important;
			line-height: var(--typo-layouts-theme-line-height-9) !important;
			white-space: wrap !important;
			/* word-break: break-word !important; */

		}

		/* .page-content .table tr,
		.page-content .dataTables_wrapper table.dataTable tr,
		.page-content .bootstrap-table .table tr,
		.page-content .fixed-table-body .table tr,
		.page-content .tbl-typical tr,
		.page-content .follow-table tr,
		.page-content .program-table tr {
			height: auto !important;
		}

		.page-content .table tbody td > .btn,
		.page-content .table tbody td > button,
		.page-content .table tbody td > input,
		.page-content .table tbody td > select,
		.page-content .dataTables_wrapper table.dataTable tbody td > .btn,
		.page-content .dataTables_wrapper table.dataTable tbody td > button,
		.page-content .dataTables_wrapper table.dataTable tbody td > input,
		.page-content .dataTables_wrapper table.dataTable tbody td > select {
			margin-top: 0 !important;
			margin-bottom: 0 !important;
		}

		.page-content .table thead th,
		.page-content .table tbody td {
			font-size: var(--typo-layouts-theme-font-size-19) !important;
			line-height: 1.15 !important;
		}

		.page-content .table th > *,
		.page-content .table td > *,
		.page-content .dataTables_wrapper table.dataTable th > *,
		.page-content .dataTables_wrapper table.dataTable td > *,
		.page-content .bootstrap-table .table th > *,
		.page-content .bootstrap-table .table td > *,
		.page-content .fixed-table-body .table th > *,
		.page-content .fixed-table-body .table td > * {
			align-items: flex-start !important;
			text-align: left !important;

		} */

		.table a {
    border-bottom: none !important;

}



		.table-responsive {
    overflow-x: visible !important;
    /* overflow-y: scroll !important;   */
}
		.follow-card, .follow-body {
    overflow: visible !important;
}

		.follow-action-dropdown {
    position: relative;

}

.follow-action-dropdown .dropdown-menu {
	font-size: var(--typo-layouts-theme-font-size-19) !important;
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
    font-size: var(--typo-layouts-theme-font-size-7) !important;
    text-transform: uppercase;
	font-weight: var(--typo-layouts-theme-font-weight-15);
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
font-size: var(--typo-layouts-theme-font-size-5);
font-weight: var(--typo-layouts-theme-font-weight-15);
}

.field-error {
color: #e53935;
font-size: var(--typo-layouts-theme-font-size-6);
margin-top: 6px;
}

form .form-control.is-invalid,
form .form-control:invalid,
form .form-select.is-invalid,
form .form-select:invalid,
form .custom-select.is-invalid,
form .custom-select:invalid,
form .form-control-range.is-invalid,
form .form-control-range:invalid,
form .custom-range.is-invalid,
form .custom-range:invalid,
.was-validated form .form-control:invalid,
.was-validated form .form-select:invalid,
.was-validated form .custom-select:invalid,
.was-validated form .custom-range:invalid {
    border-color: #d8e2e7 !important;
    box-shadow: none !important;
    background-image: none !important;
}

form .select2-container--default .select2-selection--single.is-invalid,
form .select2-container--default .select2-selection--multiple.is-invalid,
form .select2-container--white .select2-selection--single.is-invalid,
form .select2-container--white .select2-selection--multiple.is-invalid,
form .is-invalid + .select2-container .select2-selection--single,
form .is-invalid + .select2-container .select2-selection--multiple {
    border-color: #d8e2e7 !important;
    box-shadow: none !important;
    background-image: none !important;
}

.lead-form input[type="range"] {
    /* min-height: 0 !important;
    height: auto !important; */
}

.form-check-input[type="radio"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 14px;
    height: 13px !important;
    border: 2px solid grey;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
    position: relative;
    background-color: #fff;
    transition: background 0.2s, box-shadow 0.2s;
}

.form-check-input[type="radio"]:checked {
    border-color: #00a8ff;
}

.form-check-input[type="radio"]:checked::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 6px;
    height: 7px;
    border-radius: 50%;
    background-color: #00a8ff;
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
    font-size: var(--typo-layouts-theme-font-size-4) !important;
    letter-spacing: 0.7px;
    padding: 5px 50px 2px 10px !important;
    font-weight: var(--typo-layouts-theme-font-weight-18);
}

.page-item.active .page-link,
.page-item.active .page-link:focus,
.page-item.active .page-link:hover {
    z-index: auto !important;
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

		.control-panel:not(.dashboard-page) .page-content {
    padding-right: 67px;
}
.dataTables_wrapper{
    overflow: visible !important;
}

div.dataTables_scrollBody{
    overflow: visible !important;
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
    /* padding: 1px; */
	margin:5px;
}

.box-typical.box-typical-dashboard {
margin-bottom:  10px !important;
border: 1px solid #dbe4ed !important;
}

.box-typical.box-typical-dashboard .box-typical-header {
display: flex;
}
.Traing-head-selector{
			width: 200px;
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
	.control-flow-show-bar{

	}
	.follow-controls.follow-controls--toolbar .follow-controls-search-group{
		flex-direction:column;
		align-items: flex-start !important;
	}
.Traing-head-selector{
			width: 100%;
		}
		.dropdown.dropdown-notification.notif {
  margin-right: 4px;
}
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
	white-space: wrap;
}

.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar), .dataTables_wrapper .follow-footer{
	flex-direction:column;
	align-items: flex-start;
}
.follow-footer {
flex-direction: column;
margin-top:10px;
text
}
.font-icon-menu-addl{
	font-size: 1.5rem !important;
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
.student-directory .dataTables_wrapper .follow-controls--toolbar .dataTables_filter input
{
    width: 94px !important;

}
.student-summary-card strong {
margin-top: -2px;

}
.program-discount-header, .box-typical.box-typical-dashboard .box-typical-header{
	align-items: baseline;
	padding: 10px;
}






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
        /* grid-template-columns: repeat(1, minmax(0, 1fr)) !important; */
    }
	.kpi-value, .batch-scope-card strong , .program-scope-card,.campus-scope-card{

	margin-top: 5px !important;
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

/* =========================================================
   Tables
   ========================================================= */
.table {
width: 100%;
max-width: 100%;
/* margin-left: 8px;%    .table {
 */
}
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
		.control-panel:not(.dashboard-page) .page-content {
			padding-right: 57px !important;
		}

		.profile-no-sidebar .page-content {
			padding-left: 15px !important;
			padding-right: 15px !important;
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

			.profile-no-sidebar .page-content {
				padding-left: 15px !important;
				padding-right: 15px !important;
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
				padding: 82px 15px 10px !important;
				margin-top: 14px !important;
			}

			body.with-side-menu.control-panel .page-content {
				padding-left: 16px !important;
				padding-right: 16px !important;
			}
			.chart-statistic-box .chart-container{
				background-color:white;
				gap:30px;
			}
			    .chart-statistic-box .chart-txt{
					       border-radius: 8px 8px 8px 8px;
				}
				.chart-statistic-box .chart-container-in{
					        border-radius: 8px 8px 8px 8px;

				}
		}

		.app-global-loader {
			position: fixed;
			inset: 0;
			z-index: 3000;
			display: none;
			align-items: center;
			justify-content: center;
			background: rgba(245, 247, 251, 0.62);
			backdrop-filter: blur(2px);
			pointer-events: none;
		}
		.app-global-loader.is-visible {
			display: flex;
		}
		.app-global-loader-card {
			min-width: 190px;
			padding: 22px 26px;
			border-radius: 8px;
			background: rgba(255, 255, 255, 0.96);
			border: 1px solid #dfe5eb;
			box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			gap: 12px;
		}
		.app-global-loader .follow-spinner {
			display: flex;
			gap: 6px;
			align-items: center;
			justify-content: center;
		}
		.app-global-loader .follow-spinner .dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #0099f8;
			animation: app-global-loader-pulse 0.9s ease-in-out infinite;
		}
		.app-global-loader .follow-spinner .dot:nth-child(2) {
			animation-delay: 0.14s;
		}
		.app-global-loader .follow-spinner .dot:nth-child(3) {
			animation-delay: 0.28s;
		}
		.app-global-loader p {
			margin: 0;
			color: #54667a;
			font-weight: 600;
			font-size: 0.875rem;
		}
		.dataTables_processing,
		body.filter-request-page .follow-loader,
		body.filter-request-page .dashboard-loader {
			display: none !important;
		}
		@keyframes app-global-loader-pulse {
			0%, 80%, 100% {
				opacity: 0.35;
				transform: scale(0.82);
			}
			40% {
				opacity: 1;
				transform: scale(1);
			}
		}
	</style>
</head>

@php
	$isMainDashboardPage = request()->routeIs('dashboard') && in_array(optional(request()->route())->uri(), ['', '/'], true);
	$hideSidebarForPage = request()->routeIs('profile.*');
	$isFilterRequestPage = request()->hasAny([
		'search',
		'q',
		'campus_id',
		'program_id',
		'batch_id',
		'category',
		'status',
		'period',
		'per_page',
		'created_from',
		'created_to',
		'from',
		'to',
		'day_of_week',
		'attendance_date',
	]);
@endphp
<body class="{{ $hideSidebarForPage ? 'profile-no-sidebar' : 'with-side-menu' }} control-panel control-panel-compact {{ $isMainDashboardPage ? 'dashboard-page' : '' }} {{ $isFilterRequestPage ? 'filter-request-page' : '' }} {{ trim($__env->yieldContent('body_class')) }}">
	<div id="app-global-loader" class="app-global-loader" role="status" aria-live="polite" aria-hidden="true">
		<div class="app-global-loader-card">
			<div class="follow-spinner" aria-hidden="true">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p id="app-global-loader-message">Loading...</p>
		</div>
	</div>

	@include('layouts.header')
	@if(!$hideSidebarForPage)
		@include('layouts.nav')
	@endif

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
			var globalLoaderTimer = null;
			var activeDataTableLoads = 0;

			function showGlobalLoader(message) {
				var loader = document.getElementById('app-global-loader');
				var label = document.getElementById('app-global-loader-message');

				if (!loader) {
					return;
				}

				if (globalLoaderTimer) {
					window.clearTimeout(globalLoaderTimer);
					globalLoaderTimer = null;
				}

				if (label) {
					label.textContent = message || 'Loading...';
				}

				$('.follow-loader, .dashboard-loader, .dataTables_processing').hide();
				loader.classList.add('is-visible');
				loader.setAttribute('aria-hidden', 'false');
			}

			function hideGlobalLoader() {
				var loader = document.getElementById('app-global-loader');

				if (!loader) {
					return;
				}

				if (globalLoaderTimer) {
					window.clearTimeout(globalLoaderTimer);
				}

				globalLoaderTimer = window.setTimeout(function () {
					loader.classList.remove('is-visible');
					loader.setAttribute('aria-hidden', 'true');
					globalLoaderTimer = null;
				}, 120);
			}

			window.AppLoader = window.AppLoader || {
				show: showGlobalLoader,
				hide: hideGlobalLoader
			};

			function debounce(callback, wait) {
				var timer = null;

				return function () {
					var context = this;
					var args = arguments;

					window.clearTimeout(timer);
					timer = window.setTimeout(function () {
						callback.apply(context, args);
					}, wait || 400);
				};
			}

			function normalizeSearchFormUrl(form) {
				if (!form || !form.action) {
					return;
				}

				try {
					var target = new URL(form.action, window.location.origin);
					target.searchParams.delete('page');
					form.action = target.toString();
				} catch (error) {
					// Keep the browser's native form action if URL parsing is unavailable.
				}
			}

			function bindLiveSearchForms() {
				$('form[method="GET"], form[method="get"]').each(function () {
					var form = this;
					var $form = $(form);
					var $search = $form.find('input[name="search"], input[name="q"]').filter('input[type="search"], input[type="text"], input:not([type])').first();

					if (!$search.length || $search.data('liveSearchReady') || $form.is('[data-live-search="custom"]') || $search.is('[data-live-search="off"]')) {
						return;
					}

					var lastSubmittedValue = String($search.val() || '');
					var submitSearch = debounce(function () {
						var nextValue = String($search.val() || '');

						if (nextValue === lastSubmittedValue) {
							return;
						}

						lastSubmittedValue = nextValue;
						normalizeSearchFormUrl(form);
						showGlobalLoader('Loading results...');
						form.submit();
					}, 400);

					$search.on('input.liveSearch', submitSearch);
					$form.on('submit.liveSearch', function () {
						normalizeSearchFormUrl(form);
						showGlobalLoader('Loading results...');
					});
					$search.data('liveSearchReady', true);
				});
			}

			function focusActiveSearchInput() {
				var params = new URLSearchParams(window.location.search || '');
				var hasSearch = params.has('search') || params.has('q');

				if (!hasSearch) {
					return;
				}

				var $search = $('input[name="search"]:visible, input[name="q"]:visible').filter('input[type="search"], input[type="text"], input:not([type])').first();

				if (!$search.length) {
					return;
				}

				window.setTimeout(function () {
					var input = $search.get(0);
					var valueLength = String(input.value || '').length;

					input.focus({ preventScroll: true });

					if (input.setSelectionRange) {
						input.setSelectionRange(valueLength, valueLength);
					}
				}, 80);
			}

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

			function getFollowTableContext($controls) {
				var $wrapper = $controls.closest('.dataTables_wrapper');
				var $table = $();
				var api = null;

				if ($wrapper.length) {
					$table = $wrapper.find('table').first();
					if ($table.length && $.fn.dataTable && $.fn.dataTable.isDataTable($table.get(0))) {
						api = $table.DataTable();
					}
				} else {
					$table = $controls.closest('.follow-body, .panel-body, .box-typical-body, .card-body, .table-responsive, .follow-shell')
						.find('table')
						.first();
				}

				return {
					$wrapper: $wrapper,
					$table: $table,
					api: api
				};
			}

			function getSearchBlock($controls) {
				var $search = $controls.children('.dataTables_filter').first();
				if ($search.length) {
					return $search;
				}

				return $controls.children('.follow-search').first();
			}

			function getLengthControl($controls, $searchBlock) {
				var $length = $controls.children('.dataTables_length').first();
				if ($length.length) {
					return $length;
				}

				return $controls.children().not($searchBlock).has('select').first();
			}

			function getColumnLabel($th, index) {
				var text = $.trim($th.text());
				return text || ('Column ' + (index + 1));
			}

			function setManualColumnVisibility($table, index, visible) {
				var display = visible ? '' : 'none';
				$table.find('tr').each(function () {
					var $cells = $(this).children();
					if ($cells.eq(index).length) {
						$cells.eq(index).css('display', display);
					}
				});
			}

			function getManualTableRows($table) {
				return $table.find('tbody tr').filter(function () {
					return !$(this).is('[data-empty-row], .dataTables_empty') && !$(this).find('td[colspan]').length;
				});
			}

			function getFollowFooterCount($controls, context) {
				var $scope = context.$wrapper && context.$wrapper.length
					? context.$wrapper
					: $controls.closest('.follow-body, .panel-body, .box-typical-body, .card-body, .follow-shell');

				return $scope.find('.follow-footer').first().children('div').first();
			}

			function updateManualFilterCount($controls, context, visibleRows, totalRows, hasSearch) {
				var $count = getFollowFooterCount($controls, context);
				if (!$count.length) {
					return;
				}

				if (!$count.data('followOriginalText')) {
					$count.data('followOriginalText', $.trim($count.text()));
				}

				if (!hasSearch) {
					$count.text($count.data('followOriginalText'));
					return;
				}

				var label = /entries/i.test($count.data('followOriginalText')) ? 'entries' : 'Entries';
				$count.text('Showing ' + (visibleRows ? 1 : 0) + ' to ' + visibleRows + ' of ' + visibleRows + ' ' + label);
			}

			function applyManualTableSearch($controls, context, $searchInput) {
				if (context.api || !context.$table.length || !$searchInput.length) {
					return;
				}

				var query = $.trim(String($searchInput.val() || '')).toLowerCase();
				var $rows = getManualTableRows(context.$table);
				var visibleRows = 0;

				$rows.each(function () {
					var $row = $(this);
					var matches = !query || $row.text().toLowerCase().indexOf(query) !== -1;
					$row.toggle(matches);

					if (matches) {
						visibleRows++;
					}
				});

				context.$table.find('tbody tr[data-empty-row]').toggle(!query && !$rows.length);
				updateManualFilterCount($controls, context, visibleRows, $rows.length, !!query);
			}

			function bindManualFollowSearch($controls, context, $searchInput) {
				if (context.api || !$searchInput.length || $searchInput.data('followManualSearchReady')) {
					return;
				}

				$searchInput.on('input.followManualSearch keyup.followManualSearch', function () {
					applyManualTableSearch($controls, context, $searchInput);
				});

				$searchInput.data('followManualSearchReady', true);
				applyManualTableSearch($controls, context, $searchInput);
			}

			function bindDataTableLiveSearch(context, $searchInput) {
				if (!context.api || !$searchInput.length || $searchInput.data('followDataTableSearchReady')) {
					return;
				}

				$searchInput.off('.DT');

				var applySearch = debounce(function () {
					var value = String($searchInput.val() || '');

					if (context.api.search() === value) {
						return;
					}

					showGlobalLoader('Loading results...');
					context.api.search(value).draw();
				}, 400);

				$searchInput.on('input.followDataTableSearch keyup.followDataTableSearch search.followDataTableSearch', function (event) {
					if (event.type === 'keyup' && event.key && event.key !== 'Enter') {
						return;
					}

					applySearch();
				});

				$searchInput.on('keydown.followDataTableSearch', function (event) {
					if (event.key === 'Enter') {
						event.preventDefault();
						applySearch();
					}
				});

				$searchInput.data('followDataTableSearchReady', true);
			}

			function normalizeSortText(value) {
				return $.trim(String(value || '')).replace(/\s+/g, ' ');
			}

			function parseSortNumber(value) {
				var normalized = normalizeSortText(value).replace(/rs\.?/ig, '').replace(/[, ]/g, '');
				if (!normalized || !/^-?\d+(\.\d+)?$/.test(normalized)) {
					return null;
				}

				return Number(normalized);
			}

			function parseSortDate(value) {
				var text = normalizeSortText(value);
				var parsed = Date.parse(text);
				return Number.isFinite(parsed) ? parsed : null;
			}

			function compareSortValues(left, right) {
				var leftNumber = parseSortNumber(left);
				var rightNumber = parseSortNumber(right);

				if (leftNumber !== null && rightNumber !== null) {
					return leftNumber - rightNumber;
				}

				var leftDate = parseSortDate(left);
				var rightDate = parseSortDate(right);

				if (leftDate !== null && rightDate !== null) {
					return leftDate - rightDate;
				}

				return normalizeSortText(left).localeCompare(normalizeSortText(right), undefined, {
					numeric: true,
					sensitivity: 'base'
				});
			}

			function bindManualTableSorting(context) {
				if (context.api || !context.$table.length || context.$table.data('followManualSortReady')) {
					return;
				}

				var $headers = context.$table.find('thead th');
				if (!$headers.length) {
					return;
				}

				$headers.each(function (index) {
					var $header = $(this);
					if ($header.attr('colspan') || $header.hasClass('no-sort')) {
						return;
					}

					$header.addClass('follow-sortable').attr('role', 'button').attr('tabindex', '0');

					function sortColumn() {
						var direction = $header.data('followSortDirection') === 'asc' ? 'desc' : 'asc';
						var rows = getManualTableRows(context.$table).get();

						$headers.removeClass('is-sorted-asc is-sorted-desc').removeData('followSortDirection');
						$header.addClass(direction === 'asc' ? 'is-sorted-asc' : 'is-sorted-desc');
						$header.data('followSortDirection', direction);

						rows.sort(function (leftRow, rightRow) {
							var leftText = $(leftRow).children().eq(index).text();
							var rightText = $(rightRow).children().eq(index).text();
							var result = compareSortValues(leftText, rightText);

							return direction === 'asc' ? result : -result;
						});

						context.$table.children('tbody').append(rows);
					}

					$header.on('click.followManualSort', sortColumn);
					$header.on('keydown.followManualSort', function (event) {
						if (event.key === 'Enter' || event.key === ' ') {
							event.preventDefault();
							sortColumn();
						}
					});
				});

				context.$table.data('followManualSortReady', true);
			}

			function storeInitialTableState(context) {
				if (!context.$table.length || context.$table.data('followInitialState')) {
					return;
				}

				var state = {
					compact: context.$table.hasClass('follow-table-density-compact')
				};

				if (context.api) {
					var order = context.api.order ? context.api.order() : [];
					var columnVisibility = [];

					if (context.api.columns) {
						context.api.columns().every(function (index) {
							columnVisibility[index] = this.visible();
						});
					}

					state.order = $.isArray(order) ? order.slice() : [];
					state.pageLength = context.api.page && context.api.page.len ? context.api.page.len() : null;
					state.columnVisibility = columnVisibility;
				} else {
					state.columnVisibility = [];
					context.$table.find('thead th').each(function (index) {
						state.columnVisibility[index] = $(this).css('display') !== 'none';
					});
				}

				context.$table.data('followInitialState', state);
			}

			function updateColumnDropdownState(context) {
				if (!context.$table.length) {
					return;
				}

				var $dropdown = context.$table.data('followColumnsDropdown');
				if (!$dropdown || !$dropdown.length) {
					return;
				}

				$dropdown.find('input[type="checkbox"]').each(function () {
					var index = Number($(this).attr('data-index'));
					var isVisible;

					if (context.api) {
						isVisible = context.api.column(index).visible();
					} else {
						var $header = context.$table.find('thead th').eq(index);
						isVisible = $header.length ? $header.css('display') !== 'none' : true;
					}

					$(this).prop('checked', !!isVisible);
				});
			}

			function resetTableState(context, $searchInput, $controls) {
				if (!$controls || !$controls.length) {
					$controls = context.$wrapper ? context.$wrapper.find('.follow-controls').first() : $();
				}

				var initialState = context.$table.data('followInitialState') || {};

				if ($searchInput && $searchInput.length) {
					$searchInput.val('');
				}

				if (context.api) {
					if (context.api.search) {
						context.api.search('');
					}

					if (context.api.columns) {
						context.api.columns().search('');
					}

					if ($.isArray(initialState.columnVisibility)) {
						$.each(initialState.columnVisibility, function (index, visible) {
							context.api.column(index).visible(visible, false);
						});
					} else if (context.api.columns) {
						context.api.columns().visible(true, false);
					}

					if ($.isArray(initialState.order) && initialState.order.length && context.api.order) {
						context.api.order(initialState.order);
					}

					if (initialState.pageLength && context.api.page && context.api.page.len) {
						context.api.page.len(initialState.pageLength);
					}

					context.$table.toggleClass('follow-table-density-compact', !!initialState.compact);

					if ($controls && $controls.length) {
						$controls.find('[aria-label="Toggle table density"]').toggleClass('is-active', !!initialState.compact);
					}

					context.api.draw(false);

					if (context.api.page) {
						context.api.page('first').draw('page');
					}

					if (context.api.ajax && context.api.ajax.reload) {
						context.api.ajax.reload(null, false);
					}
				} else {
					context.$table.removeClass('follow-table-density-compact');
					context.$table.find('tbody tr').show();
					context.$table.find('thead th').removeClass('is-sorted-asc is-sorted-desc').removeData('followSortDirection');

					if ($.isArray(initialState.columnVisibility)) {
						$.each(initialState.columnVisibility, function (index, visible) {
							setManualColumnVisibility(context.$table, index, visible !== false);
						});
					} else {
						context.$table.find('tr').each(function () {
							$(this).children().css('display', '');
						});
					}

					if ($controls && $controls.length) {
						$controls.find('[aria-label="Toggle table density"]').removeClass('is-active');
					}

					if ($searchInput && $searchInput.length) {
						$searchInput.trigger('input').trigger('keyup').trigger('change');
					}
				}

				updateColumnDropdownState(context);
			}

			function exportTableToCsv($table, filename) {
				if (!$table.length) {
					return;
				}

				var rows = [];
				$table.find('tr:visible').each(function () {
					var cells = [];
					$(this).children(':visible').each(function () {
						var value = $.trim($(this).text()).replace(/\s+/g, ' ');
						cells.push('"' + value.replace(/"/g, '""') + '"');
					});
					if (cells.length) {
						rows.push(cells.join(','));
					}
				});

				if (!rows.length) {
					return;
				}

				var blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
				var link = document.createElement('a');
				link.href = URL.createObjectURL(blob);
				link.download = filename || 'table-export.csv';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
				URL.revokeObjectURL(link.href);
			}

			function copyTableToClipboard($table) {
				if (!$table.length) {
					return;
				}

				var lines = [];
				$table.find('tr:visible').each(function () {
					var values = [];
					$(this).children(':visible').each(function () {
						values.push($.trim($(this).text()).replace(/\s+/g, ' '));
					});
					if (values.length) {
						lines.push(values.join('\t'));
					}
				});

				var text = lines.join('\n');
				if (!text) {
					return;
				}

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text);
					return;
				}

				var textarea = document.createElement('textarea');
				textarea.value = text;
				document.body.appendChild(textarea);
				textarea.select();
				document.execCommand('copy');
				document.body.removeChild(textarea);
			}

			function buildColumnsDropdown($tools, context) {
				if (!context.$table.length || context.$table.data('follow-columns-ready')) {
					return;
				}

				var $headers = context.$table.find('thead th');
				if (!$headers.length) {
					return;
				}

				var $dropdown = $(
					'<div class="follow-toolbar-dropdown dropdown">' +
						'<button type="button" class="btn  dropdown-toggle" data-toggle="dropdown" aria-label="Columns">' +
							'<span class="font-icon font-icon-list-rotate" aria-hidden="true"></span>' +
							' <span class="caret"></span>' +
						'</button>' +
						'<ul class="dropdown-menu" role="menu"></ul>' +
					'</div>'
				);

				$headers.each(function (index) {
					var label = getColumnLabel($(this), index);
					var checkboxId = 'follow-col-' + (context.$table.attr('id') || 'table') + '-' + index;
					var isVisible = context.api ? context.api.column(index).visible() : $(this).css('display') !== 'none';
					var $item = $(
						'<li class="follow-column-option">' +
							'<span class="checkbox">' +
								'<label for="' + checkboxId + '">' +
									'<input id="' + checkboxId + '" type="checkbox" ' + (isVisible ? 'checked="checked"' : '') + ' data-index="' + index + '">' +
									'<span>' + label + '</span>' +
								'</label>' +
							'</span>' +
						'</li>'
					);
					$dropdown.find('.dropdown-menu').append($item);
				});

				$dropdown.on('click', 'label', function (event) {
					event.stopPropagation();
				});

				$dropdown.on('change', 'input[type="checkbox"]', function () {
					var index = Number($(this).attr('data-index'));
					var visible = $(this).is(':checked');

					if (context.api) {
						context.api.column(index).visible(visible);
						return;
					}

					setManualColumnVisibility(context.$table, index, visible);
				});

				context.$table.data('follow-columns-ready', true);
				context.$table.data('followColumnsDropdown', $dropdown);
				$tools.append($dropdown);
			}

			function buildExportDropdown($tools, context) {
				if (!context.$table.length) {
					return;
				}

				var filename = (context.$table.attr('id') || 'table') + '.csv';
				var exportUrl = context.$table.data('exportUrl') || (context.$wrapper && context.$wrapper.length ? context.$wrapper.data('exportUrl') : '');
				var $dropdown = $(
					'<div class="follow-toolbar-dropdown dropdown">' +
						'<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-label="Export">' +
							'<span class="font-icon font-icon-arrow-square-down" aria-hidden="true"></span>' +
							' <span class="caret"></span>' +
						'</button>' +
						'<ul class="dropdown-menu" role="menu">' +
							'<li><button type="button" class="dropdown-item" data-export="copy">Copy</button></li>' +
							'<li><button type="button" class="dropdown-item" data-export="csv">Download CSV</button></li>' +
						'</ul>' +
					'</div>'
				);

				$dropdown.on('click', '[data-export="copy"]', function () {
					copyTableToClipboard(context.$table);
				});

				$dropdown.on('click', '[data-export="csv"]', function () {
					if (exportUrl) {
						window.location.href = String(exportUrl);
						return;
					}

					exportTableToCsv(context.$table, filename);
				});

				$tools.append($dropdown);
			}

			function buildRefreshButton($tools, context, $searchInput) {
				var $button = $(
					'<button type="button" class="btn follow-toolbar-btn" aria-label="Refresh">' +
						'<span class="fa fa-refresh" aria-hidden="true"></span>' +
					'</button>'
				);

				$button.on('click', function () {
					resetTableState(context, $searchInput, $tools.closest('.follow-controls'));
				});

				$tools.append($button);
			}

			function buildDensityToggle($tools, context) {
				if (!context.$table.length) {
					return;
				}

				var $button = $(
					'<button type="button" class="btn follow-toolbar-btn" aria-label="Toggle table density">' +
						'<span class="fa fa-th-list" aria-hidden="true"></span>' +
					'</button>'
				);

				$button.on('click', function () {
					context.$table.toggleClass('follow-table-density-compact');
					$(this).toggleClass('is-active');
				});

				$tools.append($button);
			}

			function enhanceFollowControls() {
				$('.follow-controls').each(function () {
					var $controls = $(this);
					if ($controls.data('followToolbarReady')) {
						return;
					}

					var context = getFollowTableContext($controls);
					storeInitialTableState(context);
					var $searchBlock = getSearchBlock($controls);
					if (!$searchBlock.length) {
						return;
					}

					var $searchInput = $searchBlock.find('input[type="search"], input[type="text"]').first();
					var $searchGroup = $('<div class="follow-controls-search-group"></div>');
					var $tools = $('<div class="follow-controls-tools"><div class="follow-toolbar-group"></div></div>');
					var $group = $tools.find('.follow-toolbar-group');

					if ($searchBlock.hasClass('dataTables_filter')) {
						$searchBlock.find('label').contents().filter(function () {
							return this.nodeType === 3;
						}).remove();
					}

					$controls.addClass('follow-controls--toolbar');
					$searchBlock.before($searchGroup);
					var $lengthControl = getLengthControl($controls, $searchBlock);
					if ($lengthControl.length) {
						$controls.prepend($lengthControl);
					}
					$searchGroup.append($searchBlock);
					$searchGroup.append($tools);
					$controls.append($searchGroup);

					buildRefreshButton($group, context, $searchInput);
					buildDensityToggle($group, context);
					buildColumnsDropdown($group, context);
					buildExportDropdown($group, context);

					if ($searchInput.length) {
						$searchInput.attr('placeholder', 'Search');
					}

					bindManualFollowSearch($controls, context, $searchInput);
					bindDataTableLiveSearch(context, $searchInput);
					bindManualTableSorting(context);

					$controls.data('followToolbarReady', true);
					updateColumnDropdownState(context);
				});
			}

			enhanceFollowControls();
			bindLiveSearchForms();
			focusActiveSearchInput();
			$(document).on('init.dt draw.dt', function () {
				enhanceFollowControls();
			});
			$(document).on('preXhr.dt', function () {
				activeDataTableLoads++;
				showGlobalLoader('Loading results...');
			});
			$(document).on('xhr.dt error.dt draw.dt', function () {
				activeDataTableLoads = Math.max(0, activeDataTableLoads - 1);

				if (activeDataTableLoads === 0) {
					hideGlobalLoader();
				}
			});
			$(document).on('processing.dt', function (event, settings, processing) {
				if (processing) {
					showGlobalLoader('Loading results...');
					return;
				}

				if (activeDataTableLoads === 0) {
					hideGlobalLoader();
				}
			});
			$(document).on('submit', 'form[method="GET"], form[method="get"]', function () {
				if ($(this).find('input[name="search"], input[name="q"]').length) {
					showGlobalLoader('Loading results...');
				}
			});
			$(window).on('pageshow', function () {
				hideGlobalLoader();
				focusActiveSearchInput();
			});
		});
	</script>
	@if(session('welcome'))
		@php
			$pakistanHour = now('Asia/Karachi')->hour;
			$welcomeTitle = match (true) {
				$pakistanHour < 12 => 'Good Morning',
				$pakistanHour < 17 => 'Good Afternoon',
				default => 'Good Evening',
			};
		@endphp
		<script>
			(function () {
				if (!window.swal) return;
				var name = @json(session('welcome'));
				swal({
					title: @json($welcomeTitle),
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
	@include('partials.inline_form_validation')
	@stack('scripts')

</body>

</html>
