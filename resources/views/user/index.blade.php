@extends('layouts.theme')

@section('title', 'Users')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default user-directory">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">User Directory</h3>
							<small class="text-muted">Manage users, campuses, roles, and access.</small>
						</div>
						<div class="d-flex gap-2">
							<a href="{{ route('users.create') }}" class="btn btn-primary">New User</a>
						</div>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover table-striped" id="users-table">
								<thead>
									<tr>
										<th>Sr#</th>
										<th>Name</th>
										<th>Email</th>
										<th>Role</th>
										<th>Status</th>
										<th>Campus Code</th>
										<th>Date</th>
										<th class="text-right">Actions</th>
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
	<link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<style>
		.page-content > .container-fluid {
			max-width: 100%;
			padding-left: 16px;
			padding-right: 16px;
		}
		.fw-600 { font-weight: 600; }
		.gap-2 { gap: 8px; }
		.btn-danger-outline {
			border: 1px solid #e74c3c;
			color: #e74c3c;
			background: transparent;
		}
		#users-table thead th {
			background: #1fb2ff;
			color: #fff;
			border-color: #1aa4ea;
			font-weight: 600;
			vertical-align: middle;
		}
		#users-table thead th.sorting:after,
		#users-table thead th.sorting_asc:after,
		#users-table thead th.sorting_desc:after {
			color: #dff3ff;
		}
		#users-table {
			border: 1px solid #d9e2ef;
			border-radius: 6px;
			overflow: visible;
			background: #fff;
		}
		#users-table th,
		#users-table td {
			border-color: #d9e2ef;
			padding: 6px 10px;
			vertical-align: middle;
			border-right: 1px solid #d9e2ef;
			border-bottom: 1px solid #d9e2ef;
		}
		#users-table th:first-child,
		#users-table td:first-child {
			border-left: 1px solid #d9e2ef;
		}
		#users-table tbody tr:nth-of-type(odd) {
			background-color: #f5f6ff;
		}
		.user-directory .box-typical-body {
			padding: 16px;
			overflow: visible;
		}
		.user-directory {
			max-width: 1400px;
			margin: 0 auto;
		}
		.user-directory,
		.user-directory.panel,
		.user-directory .panel {
			overflow: visible;
		}
		.user-directory .dataTables_wrapper {
			border-top: 1px solid #d9e2ef;
			padding-top: 12px;
		}
		.user-directory .dataTables_wrapper .dataTables_length,
		.user-directory .dataTables_wrapper .dataTables_filter {
			padding: 0 4px;
		}
		.user-directory .table-responsive {
			overflow-x: visible;
			overflow-y: visible;
		}
		#users-table td.actions-cell {
			text-align: right;
			white-space: nowrap;
		}
		#users-table td.actions-cell .dropdown {
			display: inline-block;
		}
		.user-action-dropdown .btn {
			margin: 0;
		}
		#users-table .dropdown-menu {
			z-index: 1050;
		}
		#users-table {
			margin-top: 8px;
		}
		.dataTables_wrapper .dataTables_length {
			float: left;
			margin-bottom: 12px;
		}
		.dataTables_wrapper .dataTables_filter {
			float: right;
			margin-bottom: 12px;
		}
		.dataTables_wrapper .dataTables_filter label {
			margin: 0;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}
		.dataTables_wrapper .dataTables_filter input {
			border: 1px solid #d9e2ef;
			border-radius: 22px;
			padding: 7px 14px;
			height: 40px;
			width: 240px;
			box-shadow: none;
		}
		.dataTables_wrapper .dataTables_info {
			padding-top: 8px;
		}
		.dataTables_wrapper .dataTables_paginate {
			padding-top: 8px;
		}
		.dataTables_wrapper:after {
			content: "";
			display: block;
			clear: both;
		}
		.dataTables_wrapper .dataTables_processing {
			top: 50%;
			left: 50%;
			width: auto;
			height: auto;
			padding: 12px 16px;
			border-radius: 8px;
			transform: translate(-50%, -50%);
			border: 1px solid #dbe5f1;
			background: #fff;
			box-shadow: 0 8px 20px rgba(25, 45, 85, 0.12);
		}
	</style>
@endpush

@push('scripts')
	<script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
	<script>
		$(function () {
			$('#users-table').DataTable({
				processing: true,
				serverSide: true,
				ajax: "{{ route('users.index') }}",
				order: [[1, 'asc']],
				columns: [
					{ data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
					{ data: 'name', name: 'name' },
					{ data: 'email', name: 'email' },
					{ data: 'role', name: 'role', orderable: false, searchable: false },
					{ data: 'status', name: 'status', orderable: false, searchable: false },
					{ data: 'campus_code', name: 'campus_code', orderable: false, searchable: false },
					{ data: 'date', name: 'date', orderable: false, searchable: false },
					{ data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right actions-cell' },
				]
			});

			var statusMessage = @json(session('status'));
			if (statusMessage) {
				swal({
					title: 'Success',
					text: statusMessage,
					type: 'success',
					timer: 1800,
					showConfirmButton: false
				});
			}
		});
	</script>
@endpush
