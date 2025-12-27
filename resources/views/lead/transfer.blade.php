@extends('layouts.theme')

@section('title', 'Lead Management | Transferred Leads')

@section('content')
	@php
		$leads = [
			['name' => 'Syed Ahmad Mouzam', 'program' => 'Digital Marketing & SEO', 'contact' => '03020680037', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => 'Miss hemayal please add his admission'],
			['name' => 'Talha', 'program' => 'Digital Marketing & SEO', 'contact' => '03448662035', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => "I didn't deal with him number error how can I contact with him"],
			['name' => 'Kashif', 'program' => 'UI/UX Designing with Adobe XD & Figma', 'contact' => '03254075115', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => 'He is interested to do at Satiana Campus. also batch available there why is this lead transferring again and again here'],
			['name' => 'Amna Jabbar', 'program' => 'Cybersecurity Fundamentals', 'contact' => '03330353400', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => "No batch here I'll contact her for IELTS but no response"],
			['name' => 'Muhammad Saad', 'program' => 'UI/UX Designing with Adobe XD & Figma', 'contact' => '03706285205', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => 'No batch in Jinnah.'],
			['name' => 'Mazhar Hussain', 'program' => 'Amazon eCommerce', 'contact' => '03457765234', 'from' => 'CIFSD02', 'to' => 'CIFSD06', 'response' => 'No batch in Jinnah.'],
			['name' => 'Abdullah Masood', 'program' => 'UI/UX Designing with Adobe XD & Figma', 'contact' => '03074115966', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => 'No batch in Jinnah.'],
			['name' => 'kami', 'program' => 'SAP (Financial Accounting)', 'contact' => '03046895554', 'from' => 'CILHR02', 'to' => 'CIFSD04', 'response' => 'We are not offering this course.'],
			['name' => 'Ubaid Ullah', 'program' => 'Shorthand Stenographer', 'contact' => '03167410907', 'from' => 'CIFSD06', 'to' => 'CIFSD04', 'response' => 'satiana'],
			['name' => 'Wajid Ali', 'program' => 'Artificial Intelligence (AI)', 'contact' => '03291400059', 'from' => 'CIFSD02', 'to' => 'CIFSD04', 'response' => 'batch is not available'],
		];
	@endphp

	<div class="transfer-shell">
		<div class="transfer-header">
			<h2 class="page-title">Lead Management <span class="muted">| Transferred Leads</span></h2>
			<div class="transfer-search">
				<input type="text" id="transfer-search" class="form-control form-control-sm" placeholder="Search...">
				<i class="fa fa-search"></i>
			</div>
		</div>

		<div class="transfer-controls">
			<div class="form-inline">
				<label class="mr-2 mb-0">Show</label>
				<select class="form-control form-control-sm">
					<option>10</option>
					<option>25</option>
					<option>50</option>
				</select>
				<label class="ml-2 mb-0">entries</label>
			</div>
		</div>

		<div class="table-responsive transfer-table-wrapper">
			<table class="table table-bordered transfer-table" id="transfer-table">
				<thead>
					<tr>
						<th style="width: 50px;">St#</th>
						<th>Name</th>
						<th>Program</th>
						<th>Primary Contact</th>
						<th>From</th>
						<th>To</th>
						<th>Response</th>
						<th class="text-center" style="width: 110px;">Actions</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($leads as $idx => $row)
						<tr>
							<td class="text-center">{{ $idx + 1 }}</td>
							<td class="text-primary font-weight-bold">{{ $row['name'] }}</td>
							<td>{{ $row['program'] }}</td>
							<td>{{ $row['contact'] }}</td>
							<td>{{ $row['from'] }}</td>
							<td>{{ $row['to'] }}</td>
							<td>{{ $row['response'] }}</td>
							<td class="text-center">
								<div class="dropdown follow-action-dropdown">
									<button class="btn btn-primary btn-sm dropdown-toggle transfer-action-btn" type="button" data-toggle="dropdown">
										Actions
									</button>
									<div class="dropdown-menu dropdown-menu-right">
										<a class="dropdown-item transfer-open" href="#" data-lead="{{ $row['name'] }}"><i class="fa fa-exchange mr-2 text-warning"></i>Transfer Lead</a>
										<a class="dropdown-item" href="#"><i class="fa fa-eye mr-2 text-muted"></i>View</a>
									</div>
								</div>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="transfer-footer">
			<div>Showing 1 to {{ count($leads) }} of 145 entries</div>
			<ul class="pagination pagination-sm mb-0">
				<li class="page-item disabled"><span class="page-link">Previous</span></li>
				<li class="page-item active"><span class="page-link">1</span></li>
				<li class="page-item"><span class="page-link">2</span></li>
				<li class="page-item"><span class="page-link">3</span></li>
				<li class="page-item"><span class="page-link">4</span></li>
				<li class="page-item"><span class="page-link">5</span></li>
				<li class="page-item"><span class="page-link">…</span></li>
				<li class="page-item"><span class="page-link">15</span></li>
				<li class="page-item"><span class="page-link">Next</span></li>
			</ul>
		</div>
	</div>

	<!-- Transfer Modal -->
	<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Transfer Leads</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label>To*</label>
								<select class="form-control">
									<option>- Select -</option>
									<option>CIFSD01</option>
									<option>CIFSD02</option>
									<option>CIFSD03</option>
									<option>CIFSD04</option>
									<option>CIFSD06</option>
								</select>
							</div>
							<div class="form-group col-md-6">
								<label>Assign To*</label>
								<select class="form-control">
									<option>- Select User -</option>
									<option>Syeda Hamayal</option>
									<option>Saher Maqbool</option>
									<option>Rimsha Shahbaz</option>
								</select>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label>Next Follow Up*</label>
								<input type="text" class="form-control" value="27/12/2025 06:42 pm">
							</div>
						</div>
						<div class="form-group">
							<label>Reason*</label>
							<input type="text" class="form-control" placeholder="Enter Response">
						</div>
						<div class="form-group">
							<label>Remarks*</label>
							<textarea class="form-control" rows="3" placeholder="Remarks"></textarea>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary">Save</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.transfer-shell {
			padding: 8px 0 16px;
		}

		.transfer-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 10px;
		}

		.page-title {
			font-size: 26px;
			font-weight: 700;
			margin: 0;
			color: #2f3b52;
		}

		.page-title .muted {
			font-size: 16px;
			font-weight: 600;
			color: #7b8794;
			margin-left: 6px;
		}

		.transfer-search {
			position: relative;
			width: 240px;
		}

		.transfer-search input {
			padding-right: 32px;
		}

		.transfer-search i {
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			color: #9aa8b6;
		}

		.transfer-controls {
			display: flex;
			align-items: center;
			justify-content: flex-start;
			margin-bottom: 8px;
			gap: 12px;
		}

		.transfer-table-wrapper {
			border: 1px solid #dbe4ed;
			border-radius: 6px;
			overflow-x: auto;
		}

		.transfer-table thead th {
			background: #0099f8;
			color: #fff;
			font-weight: 700;
			border-color: #0086d8;
			vertical-align: middle;
		}

		.transfer-table tbody td {
			vertical-align: middle;
			color: #334155;
			background: #fdfefe;
			border-color: #e6ecf2;
			min-width: 110px;
		}

		.transfer-table tbody tr:nth-child(even) td {
			background: #f8fbff;
		}

		.transfer-table tbody tr:hover td {
			background: #eef5ff;
		}

		.transfer-footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-size: 13px;
			color: #54667a;
			padding: 6px 4px 0;
		}

		.follow-action-dropdown .dropdown-menu {
			min-width: 180px;
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function bindTransferModal() {
				var links = document.querySelectorAll('.transfer-open');
				var modal = $('#transferModal');
				links.forEach(function (link) {
					link.addEventListener('click', function (e) {
						e.preventDefault();
						modal.modal('show');
					});
				});
			}

			document.addEventListener('DOMContentLoaded', function () {
				bindTransferModal();
			});
		})();
	</script>
@endpush
