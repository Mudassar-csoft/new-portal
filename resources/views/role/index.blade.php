@extends('layouts.theme')

@section('title', 'Roles')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default role-directory">
					<header class="box-typical-header panel-heading d-flex justify-content-between">
						<div>
							<h3 class="panel-title mb-0 form-label">Roles</h3>
							<!-- <small class="text-muted">Manage role definitions and attached permissions.</small> -->
						</div>
						<div class="d-flex gap-2">
							<a href="{{ route('roles.create') }}" class="btn btn-primary">New Role</a>
						</div>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover table-striped text-center" id="roles-table">
								<div class="table-layout">
								<thead>
									<tr>
										<th>Sr#</th>
										<th>Name</th>
										<th>Slug</th>
										<th>Permissions</th>
										<th>Date</th>
										<th class="text-center">Actions</th>
									</tr>
								</thead>
								</div>
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
			padding: 4px 10px;
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
			/* border-top: 1px solid #d9e2ef;
			padding-top: 12px; */
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
		.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar),
		.dataTables_wrapper .follow-footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 12px;
		}
		.dataTables_wrapper .follow-footer {
			margin-top: 10px;
			margin-bottom: 0;
			color: #54667a;
			font-size: 13px;
		}
		.dataTables_wrapper .dataTables_length,
		.dataTables_wrapper .dataTables_filter,
		.dataTables_wrapper .dataTables_info,
		.dataTables_wrapper .dataTables_paginate {
			margin: 0;
			padding: 0;
			float: none !important;
			text-align: inherit !important;
		}
		.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label {
			position: relative;
			margin: 0;
			font-size: 0;
		}
		.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter label::after {
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
		.dataTables_wrapper .follow-controls:not(.follow-controls--toolbar) .dataTables_filter input {
			margin-left: 0 !important;
			border: 1px solid #d9e2ef;
			border-radius: .25rem;
			padding: .375rem 32px .375rem .75rem;
			height: 32px;
			width: 240px;
			box-shadow: none;
		}
		.dataTables_wrapper .follow-controls--toolbar .dataTables_filter label::after {
			display: none !important;
			content: none !important;
		}
		.dataTables_wrapper .follow-controls--toolbar .dataTables_filter input {
			height: 36px !important;
			width: 380px !important;
			padding: 6px 18px !important;
			border-radius: 999px !important;
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
				autoWidth: false,
				dom: '<"follow-controls"l f>rt<"follow-footer"i p>',
				ajax: "{{ route('roles.index') }}",
				order: [[1, 'asc']],
				columns: [
					{ data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
					{ data: 'name', name: 'name' },
					{ data: 'slug', name: 'slug' },
					{ data: 'permissions', name: 'permissions', orderable: false, searchable: false },
					{ data: 'date', name: 'date', orderable: false, searchable: false },
					{ data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center actions-cell' },
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
