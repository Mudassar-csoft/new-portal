@extends('layouts.theme')

@section('title', 'New Lead')

@section('content')
	<div class="lead-shell">
		<div id="lead-loader" class="lead-loader">
			<div class="lead-spinner">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p>Preparing lead form...</p>
		</div>
		<div id="lead-content" class="lead-content">
			<div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
				<header class="box-typical-header panel-heading lead-header">
					<div class="tbl w-100">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title">
								<h3 class="panel-title">Create New Lead <small class="text-muted ml-2">(All fields marked with * are required)</small></h3>
							</div>
							<div class="tbl-cell text-right">
								<select id="leadTypeSelect" class="form-control lead-type-select">
									<option value="training" selected>Trainings</option>
									<option value="certification">Certification Exam</option>
									<option value="coworking">Coworking Space</option>
									<option value="study_abroad">Study Abroad</option>
								</select>
							</div>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body lead-body">
					<form method="POST" action="{{ route('leads.store') }}">
						@csrf
						<input type="hidden" name="type" id="lead-type-field" value="training">
						@include('lead.training')
						@include('lead.certification')
						@include('lead.coworking')
						@include('lead.study_abroad')
						<div class="form-actions text-right mt-4">
							<a href="{{ url()->previous() }}" class="btn btn-secondary mr-2">Cancel</a>
							<button type="submit" class="btn btn-primary">Create Lead</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.lead-shell {
			position: relative;
			min-height: 100vh;
			width: 100%;
			overflow: hidden;
		}

		.lead-loader {
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

		.lead-spinner {
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.lead-spinner .dot {
			width: 12px;
			height: 12px;
			border-radius: 50%;
			background: #12a0ff;
			animation: bounce 0.9s ease-in-out infinite;
		}

		.lead-spinner .dot:nth-child(2) {
			animation-delay: 0.15s;
			background: #1f8ef1;
		}

		.lead-spinner .dot:nth-child(3) {
			animation-delay: 0.3s;
			background: #36b1ff;
		}

		.lead-loader p {
			margin: 0;
			color: #54667a;
			font-weight: 600;
		}

		.lead-content {
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.4s ease;
			position: relative;
			min-height: 400px;
		}

		body.lead-ready .lead-content {
			opacity: 1;
			visibility: visible;
		}

		body.lead-ready #lead-loader {
			display: none;
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

		.lead-body {
			padding: 20px 20px 10px;
			overflow: visible !important;
		}

		.lead-create-card {
			overflow: visible !important;
			max-height: none !important;
		}

		.lead-create-card .panel-heading {
			padding: 15px 20px;
		}

		.lead-type-select {
			display: inline-block;
			width: 260px;
		}

		.lead-form {
			display: none;
			max-height: none !important;
			overflow: visible !important;
		}

		.lead-form.active {
			display: block;
		}

		.lead-form-title {
			margin: 0 0 12px;
			font-size: 16px;
			font-weight: 700;
			color: #54667a;
		}

		.required::after {
			content: '*';
			color: #e53935;
			margin-left: 4px;
		}

		.radio-group label {
			margin-right: 14px;
			font-weight: 600;
			color: #54667a;
		}

		.probability-display {
			margin-top: 6px;
			font-weight: 600;
			color: #54667a;
		}

		.lead-create-card .panel-body {
			max-height: none !important;
			overflow: visible !important;
		}

		.form-row {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			margin-bottom: 12px;
		}

		.form-row .form-group {
			margin-bottom: 0;
			flex: 1 1 32%;
			min-width: 260px;
		}

		.form-row .form-group.col-md-6 {
			flex-basis: 48%;
		}

		.form-row .form-group.col-md-4 {
			flex-basis: 32%;
		}

		.form-row .form-group.col-md-3 {
			flex-basis: 24%;
		}

		input[type="range"] {
			width: 100%;
		}

		.lead-create-card .panel-body {
			max-height: none !important;
			overflow: visible !important;
		}

		.form-row {
			margin-bottom: 12px;
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function switchLeadForm(type) {
				document.querySelectorAll('.lead-form').forEach(function (form) {
					form.classList.toggle('active', form.getAttribute('data-type') === type);
				});
			}

			function bindProbabilityDisplays() {
				document.querySelectorAll('.probability-range').forEach(function (range) {
					var display = range.parentElement.querySelector('.probability-display span');
					var update = function () {
						if (display) display.textContent = range.value + '%';
					};
					range.addEventListener('input', update);
					update();
				});
			}

			function revealLeadForm() {
				// Allow the DOM to settle so the loader feels intentional
				setTimeout(function () {
					document.body.classList.add('lead-ready');
				}, 200);
			}

			document.addEventListener('DOMContentLoaded', function () {
				var select = document.getElementById('leadTypeSelect');
				var typeField = document.getElementById('lead-type-field');
				if (select) {
					switchLeadForm(select.value);
				}
				bindProbabilityDisplays();
				if (select) {
					select.addEventListener('change', function () {
						switchLeadForm(this.value);
						if (typeField) typeField.value = this.value;
					});
					if (typeField) typeField.value = select.value;
				}
				revealLeadForm();
			});
		})();
	</script>
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			CountryCityLoader.init('lead-country-select', 'lead-city-select', { country: 'Pakistan', city: 'Faisalabad' });
		});
	</script>
@endpush
