@extends('layouts.theme')

@section('title', 'Lead Detail')

@section('content')
	@php
		$lead = [
			'name' => 'Kashif',
			'course' => 'Website Development with PHP & MySQL',
			'campus' => 'CIVTL01',
			'created_at' => '2025-09-20 09:57:47',
		];

		$stages = [
			'New',
			'Contacted',
			'Need Analysis',
			'Branch Visited',
			'Proposal & Negotiation',
			'Registered / Not Interested',
		];

		$currentStage = 'Need Analysis'; // adjust as needed

		$followups = [
			['follower' => 'Saher Maqbool', 'method' => 'call', 'probability' => '100%', 'status' => 'Pending', 'created_at' => '2025-09-20 09:57:47', 'next_followup' => '2025-09-22 10:40:00', 'campus' => 'CIVTL01', 'remarks' => 'He is interested in just backend development. He has been a front-end dev...'],
			['follower' => 'Saher Maqbool', 'method' => 'call', 'probability' => '100%', 'status' => 'Pending', 'created_at' => '2025-09-20 10:07:27', 'next_followup' => '2025-09-22 10:40:00', 'campus' => 'CIVTL01', 'remarks' => 'He is from Abbottabad'],
			['follower' => 'Saher Maqbool', 'method' => 'call', 'probability' => '50%', 'status' => 'Pending', 'created_at' => '2025-09-22 15:37:18', 'next_followup' => '2025-09-24 10:42:00', 'campus' => 'CIVTL01', 'remarks' => 'Miss Ramsha is getting in touch with him..'],
			['follower' => 'Saher Maqbool', 'method' => 'call', 'probability' => '10%', 'status' => 'Pending', 'created_at' => '2025-09-24 10:16:39', 'next_followup' => '2025-12-27 10:16:00', 'campus' => 'CIVTL01', 'remarks' => "We can't make a customized course for 1 student only..", 'highlight' => true],
		];
	@endphp

	<div class="lead-show-shell">
		<div class="lead-header">
			<h2 class="lead-name">{{ $lead['name'] }}</h2>
			<div class="lead-actions">
				@include('lead.partials.action', ['actionId' => 'lead-action-main'])
			</div>
		</div>

		<div class="stage-bar">
			@foreach ($stages as $stage)
				@php
					$isActive = $stages[array_search($currentStage, $stages)] === $stage || array_search($stage, $stages) <= array_search($currentStage, $stages);
					$isCurrent = $stage === $currentStage;
				@endphp
				<div class="stage {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
					<div class="bullet"></div>
					<div class="label">{{ $stage }}</div>
				</div>
			@endforeach
			<div class="stage-progress" style="width: {{ (array_search($currentStage, $stages) / (count($stages) - 1)) * 100 }}%;"></div>
		</div>

		<div class="lead-tabs">
			<div class="tab active" data-target="#tab-followups">
				<i class="fa fa-list-ul"></i> Follow-Up History
			</div>
			<div class="tab" data-target="#tab-personal">
				<i class="fa fa-user-o"></i> Personal Information
			</div>
		</div>

		<div class="tab-content" id="tab-followups" style="display: block;">
			<button class="btn btn-primary add-followup-btn" id="toggle-followup-form">Add Follow-Up</button>

			<div class="followup-form card" id="followup-form" style="display: none;">
				<div class="card-body">
					<div class="form-row">
						<div class="form-group col-md-3">
							<label>Follow-Up Method</label>
							<select class="form-control">
								<option>- Select -</option>
								<option>Call</option>
								<option>SMS</option>
								<option>Email</option>
								<option>Whatsapp</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<label>Status</label>
							<div class="mt-1">
								<label class="mr-2"><input type="radio" name="status" checked> Pending</label>
								<label><input type="radio" name="status"> Not Interested</label>
							</div>
						</div>
						<div class="form-group col-md-3">
							<label>Teaching Method *</label>
							<div class="mt-1">
								<label class="mr-2"><input type="radio" name="teaching" checked> On-Campus</label>
								<label><input type="radio" name="teaching"> Online</label>
							</div>
						</div>
						<div class="form-group col-md-3">
							<label>Next Follow Up</label>
							<input type="text" class="form-control" placeholder="dd/mm/yyyy --:-- --">
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-4">
							<label>Preferred Campus *</label>
							<select class="form-control">
								<option>Career Institute - Satiana Road Campus</option>
								<option>Career Institute - Main Campus</option>
							</select>
						</div>
						<div class="form-group col-md-4">
							<label>Probability *</label>
							<input type="range" class="form-control-range" min="0" max="100" value="0" id="probability-range">
							<div id="probability-value" class="probability-value">Selected: 0%</div>
						</div>
					</div>
					<div class="form-group">
						<label>Remarks</label>
						<textarea class="form-control" rows="3"></textarea>
					</div>
					<button class="btn btn-primary">Save</button>
				</div>
			</div>

			<div class="table-responsive followup-table-wrapper">
				<table class="table table-bordered followup-table">
					<thead>
						<tr>
							<th style="width: 40px;">Sr</th>
							<th>Follower</th>
							<th>Method</th>
							<th>Probability</th>
							<th>Status</th>
							<th>Created At</th>
							<th>Next Follow-Up</th>
							<th>Campus Code</th>
							<th>Remarks</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($followups as $idx => $row)
							<tr class="{{ !empty($row['highlight']) ? 'row-highlight' : '' }}">
								<td class="text-center">{{ $idx + 1 }}</td>
								<td>{{ $row['follower'] }}</td>
								<td>{{ $row['method'] }}</td>
								<td>{{ $row['probability'] }}</td>
								<td>{{ $row['status'] }}</td>
								<td>{{ $row['created_at'] }}</td>
								<td>{{ $row['next_followup'] }}</td>
								<td>{{ $row['campus'] }}</td>
								<td>{{ $row['remarks'] }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		<div class="tab-content" id="tab-personal" style="display: none;">
			<div class="card">
				<div class="card-body">
					<p><strong>Course:</strong> {{ $lead['course'] }}</p>
					<p><strong>Campus:</strong> {{ $lead['campus'] }}</p>
					<p><strong>Created At:</strong> {{ $lead['created_at'] }}</p>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.lead-show-shell {
			padding: 8px 0 16px;
		}

		.lead-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 12px;
		}

		.lead-name {
			font-size: 32px;
			font-weight: 700;
			margin: 0;
			color: #2f3b52;
		}

		.stage-bar {
			position: relative;
			display: grid;
			grid-template-columns: repeat(6, 1fr);
			gap: 6px;
			padding: 16px 10px 12px;
			border: 1px solid #dbe4ed;
			border-radius: 10px;
			background: #f6f8fb;
			overflow: hidden;
			margin-bottom: 14px;
		}

		.stage-progress {
			position: absolute;
			top: 26px;
			left: 20px;
			height: 3px;
			background: linear-gradient(90deg, #00a8ff, #00d1ff);
			border-radius: 999px;
			transition: width 0.4s ease;
		}

		.stage {
			position: relative;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			z-index: 2;
		}

		.stage .bullet {
			width: 16px;
			height: 16px;
			border-radius: 50%;
			background: #d3dce6;
			box-shadow: 0 0 0 2px #f6f8fb;
			transition: background 0.3s ease, transform 0.3s ease;
		}

		.stage .label {
			font-size: 12px;
			text-align: center;
			color: #5f6f7f;
			font-weight: 700;
			min-height: 30px;
		}

		.stage.active .bullet {
			background: #00a8ff;
			transform: scale(1.05);
		}

		.stage.current .bullet {
			background: #00d1ff;
			box-shadow: 0 0 0 4px rgba(0, 209, 255, 0.2);
		}

		.lead-tabs {
			display: flex;
			align-items: center;
			border-bottom: 2px solid #e6ecf2;
			margin-bottom: 12px;
		}

		.lead-tabs .tab {
			padding: 10px 16px;
			cursor: pointer;
			font-weight: 700;
			color: #5f6f7f;
			border-bottom: 3px solid transparent;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.lead-tabs .tab.active {
			color: #0f3c6e;
			border-bottom-color: #00a8ff;
		}

		.add-followup-btn {
			margin-bottom: 10px;
		}

		.followup-form {
			border: 1px solid #dbe4ed;
			box-shadow: 0 4px 12px rgba(17, 24, 39, 0.08);
			margin-bottom: 14px;
		}

		.probability-value {
			margin-top: 6px;
			font-weight: 700;
			color: #334155;
		}

		.followup-table-wrapper {
			border: 1px solid #dbe4ed;
			border-radius: 6px;
			overflow-x: auto;
		}

		.followup-table thead th {
			background: #0099f8;
			color: #fff;
			font-weight: 700;
			border-color: #0086d8;
			vertical-align: middle;
		}

		.followup-table tbody td {
			vertical-align: middle;
			color: #334155;
			background: #fdfefe;
			border-color: #e6ecf2;
			min-width: 110px;
		}

		.followup-table tbody tr:nth-child(even) td {
			background: #f8fbff;
		}

		.followup-table tbody tr:hover td {
			background: #eef5ff;
		}

		.followup-table .row-highlight td {
			background: #ffeded;
			color: #b00020;
		}

		@media (max-width: 991px) {
			.stage-bar {
				grid-template-columns: repeat(3, 1fr);
			}
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function toggleTabs() {
				var tabs = document.querySelectorAll('.lead-tabs .tab');
				tabs.forEach(function (tab) {
					tab.addEventListener('click', function () {
						tabs.forEach(function (t) { t.classList.remove('active'); });
						document.querySelectorAll('.tab-content').forEach(function (c) { c.style.display = 'none'; });
						this.classList.add('active');
						var target = this.getAttribute('data-target');
						document.querySelector(target).style.display = 'block';
					});
				});
			}

			function toggleFollowUpForm() {
				var btn = document.getElementById('toggle-followup-form');
				var form = document.getElementById('followup-form');
				btn.addEventListener('click', function () {
					var showing = form.style.display === 'block';
					form.style.display = showing ? 'none' : 'block';
					btn.textContent = showing ? 'Add Follow-Up' : 'Hide Follow-Up';
				});
			}

			function bindProbability() {
				var range = document.getElementById('probability-range');
				var label = document.getElementById('probability-value');
				if (!range || !label) return;
				var update = function () { label.textContent = 'Selected: ' + range.value + '%'; };
				range.addEventListener('input', update);
				update();
			}

			document.addEventListener('DOMContentLoaded', function () {
				toggleTabs();
				toggleFollowUpForm();
				bindProbability();
			});
		})();
	</script>
@endpush
