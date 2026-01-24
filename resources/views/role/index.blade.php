@extends('layouts.theme')

@section('title', 'Roles')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default role-directory">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">Roles</h3>
							<small class="text-muted">Manage role definitions and attached permissions.</small>
						</div>
						<div class="d-flex gap-2">
							<a href="{{ route('roles.create') }}" class="btn btn-primary">New Role</a>
						</div>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover table-striped" id="roles-table">
								<thead>
									<tr>
										<th>Sr#</th>
										<th>Name</th>
										<th>Slug</th>
										<th>Permissions</th>
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
		.fw-600 { font-weight: 600; }
		.gap-2 { gap: 8px; }
		.btn-danger-outline {
			border: 1px solid #e74c3c;
			color: #e74c3c;
			background: transparent;
		}
		#roles-table thead th {
			background: #1fb2ff;
			color: #fff;
			border-color: #1aa4ea;
			font-weight: 600;
			vertical-align: middle;
		}
		#roles-table {
			border: 1px solid #d9e2ef;
			border-radius: 6px;
			overflow: visible;
			background: #fff;
		}
		#roles-table th,
		#roles-table td {
			border-color: #d9e2ef;
			padding: 6px 10px;
			vertical-align: middle;
			border-right: 1px solid #d9e2ef;
			border-bottom: 1px solid #d9e2ef;
		}
		#roles-table th:first-child,
		#roles-table td:first-child {
			border-left: 1px solid #d9e2ef;
		}
		#roles-table tbody tr:nth-of-type(odd) {
			background-color: #f5f6ff;
		}
		.role-directory .box-typical-body {
			padding: 16px;
			overflow: visible;
		}
		.role-directory .dataTables_wrapper {
			border-top: 1px solid #d9e2ef;
			padding-top: 12px;
		}
		.role-directory .dataTables_wrapper .dataTables_length,
		.role-directory .dataTables_wrapper .dataTables_filter {
			padding: 0 4px;
		}
		.role-directory .table-responsive {
			overflow-x: visible;
			overflow-y: visible;
		}
		#roles-table td.actions-cell {
			text-align: right;
			white-space: nowrap;
		}
		#roles-table td.actions-cell .dropdown {
			display: inline-block;
		}
		.role-action-dropdown .btn {
			margin: 0;
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
	</style>
@endpush

@push('scripts')
	<script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
	<script>
		$(function () {
			$('#roles-table').DataTable({
				processing: true,
				serverSide: true,
				ajax: "{{ route('roles.index') }}",
				order: [[1, 'asc']],
				columns: [
					{ data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
					{ data: 'name', name: 'name' },
					{ data: 'slug', name: 'slug' },
					{ data: 'permissions', name: 'permissions', orderable: false, searchable: false },
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
