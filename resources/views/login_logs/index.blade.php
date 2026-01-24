@extends('layouts.theme')

@section('title', 'Login Logs')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default login-logs">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">User Login Logs</h3>
							<small class="text-muted">Track user sign-ins and sign-outs.</small>
						</div>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover table-striped" id="login-logs-table">
								<thead>
									<tr>
										<th>Sr#</th>
										<th>User</th>
										<th>Email</th>
										<th>Action</th>
										<th>IP</th>
										<th>Location</th>
										<th>User Agent</th>
										<th>Logged At</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
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
		.login-logs .dataTables_wrapper:after {
			content: "";
			display: block;
			clear: both;
		}
		.login-logs .dataTables_length {
			float: left;
			margin-bottom: 12px;
		}
		.login-logs .dataTables_filter {
			float: right;
			margin-bottom: 12px;
		}
		.login-logs .dataTables_filter label {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			margin: 0;
		}
		.login-logs .dataTables_filter input {
			border: 1px solid #d9e2ef;
			border-radius: 18px;
			padding: 6px 12px;
			height: 36px;
			width: 220px;
			box-shadow: none;
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
			padding: 6px 10px;
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
