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
								<h2 class="panel-title lead-title">Create New Lead <small class="text-muted ml-2">(All fields marked with * are required)</small></h2>
							</div>
							<div class="text-right" style="width: 300px; text-align: left !important;">
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
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline">Create Lead</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-secondary-outline">Cancel</a>
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
			width: 180px !important;
			min-width: 180px;
		}

		.lead-title {
			font-size: 22px;
			font-weight: 700;
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

		.radio-group.is-invalid {
			border: 1px solid #e53935;
			border-radius: 6px;
			padding: 6px 10px;
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

		.follow-action-dropdown .dropdown-menu {
			min-width: 220px;
			padding: 6px 0;
			border-radius: 8px;
			box-shadow: 0 10px 24px rgba(17, 24, 39, 0.12);
		}

		.follow-action-dropdown .dropdown-item {
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 8px 14px;
			border: 0 !important;
			white-space: normal;
			background: transparent;
		}

		.follow-action-dropdown .dropdown-item i {
			width: 18px;
			text-align: center;
			flex: 0 0 18px;
		}

		.follow-action-dropdown .dropdown-item:focus,
		.follow-action-dropdown .dropdown-item:hover {
			background: #f3f8ff;
		}

		.follow-action-dropdown .dropdown-divider {
			margin: 6px 0;
		}
		.tbl-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between; /* title left, select right */
    gap: 12px;
}

/* Right cell holds the select */
.tbl-cell.text-right {
    flex: 1 1 auto;
    text-align: right;
    min-width: 150px;
}

/* Select styling */
.lead-type-select {
    width: 180px !important;
    min-width: 180px;
    max-width: 100%;
    padding: 6px 12px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

/* =====================
   RESPONSIVE FIX
===================== */
@media (max-width: 768px) {

    .tbl-row {
        flex-direction: column;
        align-items: stretch; /* full width children */
    }

    /* Equal spacing on both sides */
    .tbl-cell.tbl-cell-title,
    .tbl-cell.text-right {
        width: 100%;
        padding-left: 12px;
        padding-right: 12px;
        box-sizing: border-box;
    }

    .tbl-cell.text-right {
        text-align: left;
        margin-top: 8px;
    }

    .lead-type-select {
        width: 100%;
        max-width: 100%;
    }
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
			CountryCityLoader.init('lead-country-select', 'lead-city-select', {
				country: @json(old('details.country', 'Pakistan')),
				city: @json(old('city', 'Faisalabad'))
			});
		});
	</script>
@endpush
