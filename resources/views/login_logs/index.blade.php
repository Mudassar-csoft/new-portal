@extends('layouts.theme')

@section('title', 'Login Logs')

@section('content')
	<!-- <div class="container-fluid"> -->
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default login-logs">
					<header class="box-typical-header panel-heading d-flex justify-content-between">
						<div>
							<h3 class="panel-title mb-0 form-label">User Login Logs</h3>
							<!-- <span class="text-muted">Track user sign-ins and sign-outs.</span> -->
						</div>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover table-striped text-center" id="login-logs-table">
								<thead>
									<tr >
										<th class="text-center">Sr#</th>
										<th class="text-center">User</th>
										<th class="text-center">Email</th>
										<th class="text-center">Action</th>
										<th class="text-center">IP</th>
										<th class="text-center">Location</th>
										<th class="text-center">User Agent</th>
										<th class="text-center">Logged At</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	<!-- </div> -->
@endsection

@push('styles')
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<style>
		
		.login-logs .box-typical-body {
			padding: 16px;
			overflow-x: hidden;
		}
		.login-logs .table-responsive {
			overflow-x: visible;
		}
		.login-logs .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
		.login-logs .dataTables_wrapper .follow-footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			/* margin-bottom: 4px; */
		}
		.login-logs .dataTables_wrapper .follow-footer {
			margin-top: 10px;
			margin-bottom: 0;
			color: #54667a;
			font-size: 13px;
		}
		.login-logs .dataTables_wrapper .dataTables_length,
		.login-logs .dataTables_wrapper .dataTables_filter,
		.login-logs .dataTables_wrapper .dataTables_info,
		.login-logs .dataTables_wrapper .dataTables_paginate {
			margin: 0;
			padding: 0;
			float: none !important;
			text-align: inherit !important;
		}
		.login-logs .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label {
			position: relative;
			margin: 0;
			font-size: 0;
		}
		.login-logs .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label::after {
			content: "\f002";
			font-family: FontAwesome;
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			color: #9aa8b6;
			font-size: 12px;
			pointer-events: none;
		}
		.login-logs .dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input {
			margin-left: 0 !important;
			border: 1px solid #d9e2ef;
			border-radius: .25rem;
			padding: .375rem 32px .375rem .75rem;
			height: 32px;
			width: 240px;
			box-shadow: none;
		}
		.login-logs .dataTables_wrapper .follow-controls--toolbar .dataTables_filter label::after {
			display: none !important;
			content: none !important;
		}
		.login-logs .dataTables_wrapper .follow-controls--toolbar .dataTables_filter input {
			height: 36px !important;
			width: 240px !important;
			padding: 6px 18px !important;
			border-radius: 999px !important;
		}
		#login-logs-table thead th {
			background: #1fb2ff;
			color: #fff;
			border-color: #1aa4ea;
			font-weight: 600;
			vertical-align: middle;
		}
		#login-logs-table {
			border: 1px solid #d9e2ef;
			border-radius: 6px;
			overflow: hidden;
			background: #fff;
		}
		#login-logs-table th,
		#login-logs-table td {
			border-color: #d9e2ef;
			padding: 3px 8px;
			line-height: 1.2;
			height: 26px;
			vertical-align: middle;
			border-right: 1px solid #d9e2ef;
			border-bottom: 1px solid #d9e2ef;
		}
		#login-logs-table th:first-child,
		#login-logs-table td:first-child {
			border-left: 1px solid #d9e2ef;
		}
		#login-logs-table tbody tr:nth-of-type(odd) {
			background-color: #f5f6ff;
		}
	</style>
@endpush

@push('scripts')
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
	<script>
		$(function () {
			$('#login-logs-table').DataTable({
				processing: true,
				serverSide: true,
				autoWidth: false,
				dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
				ajax: "{{ route('login-logs.index') }}",
				order: [[7, 'desc']],
				columns: [
					{ data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
					{ data: 'user', name: 'user' },
					{ data: 'email', name: 'email' },
					{ data: 'action', name: 'action' },
					{ data: 'ip_address', name: 'ip_address' },
					{ data: 'location', name: 'location' },
					{ data: 'user_agent', name: 'user_agent' },
					{ data: 'logged_at', name: 'logged_at' },
				]
			});
		});
	</script>
@endpush
