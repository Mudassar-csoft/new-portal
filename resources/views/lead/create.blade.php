@extends('layouts.theme')

@section('title', 'New Lead')

@section('content')
	@php
		$leadPrefill = $leadPrefill ?? [];
		$prefillCountry = old('details.country', data_get($leadPrefill, 'details.country', 'Pakistan'));
		$prefillCity = old('city', $leadPrefill['city'] ?? 'Faisalabad');
		$selectedWebLeadId = request('web_lead') ?: ($webLead->id ?? null);
		$leadTypeOptions = [
			'training' => 'Training',
			'coworking' => 'Coworking Space',
			'study_abroad' => 'Study Abroad',
			'certification' => 'Certification Exam',
		];
		$selectedLeadType = old('type', request('type', data_get($leadPrefill, 'type', 'training')));
		if (!array_key_exists($selectedLeadType, $leadTypeOptions)) {
			$selectedLeadType = 'training';
		}
	@endphp

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
					<div class="lead-header-copy">
						<p class="lead-kicker mb-1">Lead Management</p>
						<h2 class="panel-title lead-title mb-1">Create New Lead</h2>
						<p class="lead-header-note mb-0">
							All required fields are marked with <span class="text-danger semibold">*</span>.
						</p>
					</div>

					<div class="lead-header-tools">
						<label for="leadTypeSelect" class="lead-type-label">Lead Type</label>
						<select
							id="leadTypeSelect"
							class="form-control lead-type-select"
							onchange="var selectedOption=this.options[this.selectedIndex]; if(selectedOption && selectedOption.dataset.url){ window.location.href=selectedOption.dataset.url; }"
						>
							@foreach($leadTypeOptions as $leadTypeValue => $leadTypeLabel)
								<option
									value="{{ $leadTypeValue }}"
									data-url="{{ route('leads.create', array_filter(['type' => $leadTypeValue, 'web_lead' => $selectedWebLeadId])) }}"
									@selected($selectedLeadType === $leadTypeValue)
								>{{ $leadTypeLabel }}</option>
							@endforeach
						</select>
					</div>
				</header>

				<div class="box-typical-body panel-body lead-body">
					@if (!empty($webLead))
						<div class="alert alert-info web-lead-alert">
							<strong>Website Lead:</strong> {{ $webLead->source_label }} from {{ $webLead->source_site }}
							@if ($webLead->submitted_at)
								<span class="text-muted ml-2">{{ $webLead->submitted_at->format('d-M-Y h:i A') }}</span>
							@endif
							<a href="{{ route('web-leads.show', $webLead) }}" class="btn btn-xs btn-primary-outline ml-2">View Source Lead</a>
						</div>
					@endif

					<form method="POST" action="{{ route('leads.store') }}" class="lead-entry-form">
						@csrf
						<input type="hidden" name="web_lead_id" value="{{ old('web_lead_id', $webLead->id ?? null) }}">
						<input type="hidden" name="type" id="lead-type-field" value="{{ $selectedLeadType }}">

						<div class="lead-form-shell">
							@include('lead.' . $selectedLeadType)
						</div>

						<div class="lead-actions">
							<button type="submit" class="btn btn-inline btn-primary-outline lead-action-primary">Create Lead</button>
							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline lead-action-secondary">Cancel</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	@include('lead.partials.probability_slider_assets')
	<style>
		.lead-shell {
			min-height: 100vh;
			padding: 24px;
			background:
				radial-gradient(circle at top left, rgba(45, 120, 255, 0.09), transparent 28%),
				linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
			font-family: 'Proxima Nova', sans-serif;
			position: relative;
		}

		.lead-loader {
			position: fixed;
			inset: 0;
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
			min-height: 420px;
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

		.lead-create-card {
			max-width: 1120px;
			margin: 0 auto;
			border: 1px solid #d9e4f0;
			border-radius: 22px;
			overflow: visible !important;
			max-height: none !important;
			box-shadow: 0 22px 50px rgba(20, 53, 93, 0.12);
			background: #fff;
		}

		.lead-create-card .panel-heading {
			padding: 28px 34px 22px;
			border-bottom: 1px solid #e8eef6;
			background: linear-gradient(180deg, rgba(248, 251, 255, 0.96), rgba(255, 255, 255, 0.98));
		}

		.lead-header {
			display: flex;
			align-items: flex-end;
			justify-content: space-between;
			gap: 24px;
		}

		.lead-header-copy {
			min-width: 0;
		}

		.lead-kicker {
			font-size: 12px;
			letter-spacing: 0.18em;
			text-transform: uppercase;
			color: #7f93ac;
			font-weight: 700;
		}

		.lead-title {
			font-size: 32px;
			line-height: 1.05;
			font-weight: 700;
			color: #16324f;
		}

		.lead-header-note {
			font-size: 14px;
			font-weight: 500;
			color: #698198;
		}

		.lead-header-tools {
			width: 250px;
			max-width: 100%;
			flex: 0 0 auto;
		}

		.lead-type-label {
			display: block;
			margin-bottom: 8px;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0.12em;
			text-transform: uppercase;
			color: #6f879f;
		}

		.lead-type-select {
			display: block;
			width: 100% !important;
			min-height: 54px;
			border: 1px solid #cfe0f1;
			border-radius: 14px;
			background: #fff;
			box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8);
			color: #16324f;
			font-size: 15px;
			padding: 0 16px;
		}

		.lead-body {
			padding: 30px 34px 34px;
			overflow: visible !important;
		}

		.web-lead-alert {
			margin: 0 0 22px;
			padding: 12px 14px;
			border-radius: 14px;
			border: 1px solid #cfe0f5;
			background: #eef6ff;
		}

		.lead-form.active {
			display: block;
		}

		.lead-entry-form {
			display: flex;
			flex-direction: column;
			gap: 18px;
		}

		.lead-form-shell {
			display: flex;
			flex-direction: column;
			gap: 18px;
		}

		.required::after {
			content: '*';
			color: #e53935;
			margin-left: 4px;
		}

		.field-error {
			color: #e53935;
			font-size: 13px;
			font-weight: 600;
			margin-top: 8px;
		}

		.form-control.is-invalid,
		.form-select.is-invalid,
		.form-control-range.is-invalid {
			border-color: #e53935;
			box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
		}

		.select2-container--default .select2-selection--single.is-invalid,
		.training-course-select.is-invalid + .select2-container .select2-selection--single {
			border-color: #e53935 !important;
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

		.lead-form-shell .lead-form,
		.lead-form-shell .lead-form > .container-fluid,
		.lead-form-shell .lead-form > section {
			width: 100%;
		}

		.lead-form-shell .lead-form > .container-fluid {
			padding: 0;
		}

		.lead-form-shell .lead-form > .container-fluid > .row,
		.lead-form-shell .lead-form > .form-row,
		.lead-form-shell .lead-form > section > .form-row {
			display: flex;
			flex-direction: column;
			gap: 18px;
			margin: 0 0 18px;
			padding: 0;
		}

		.lead-form-shell .lead-form > .container-fluid > .row:last-child,
		.lead-form-shell .lead-form > .form-row:last-child,
		.lead-form-shell .lead-form > section > .form-row:last-child {
			margin-bottom: 0;
		}

		.lead-form-shell .lead-form > .container-fluid > .row > [class*="col-"],
		.lead-form-shell .lead-form > .form-row > [class*="col-"],
		.lead-form-shell .lead-form > section > .form-row > [class*="col-"],
		.lead-form-shell .lead-form > .form-row > .form-group,
		.lead-form-shell .lead-form > section > .form-row > .form-group {
			display: grid;
			grid-template-columns: 220px minmax(0, 1fr);
			gap: 24px;
			align-items: start;
			flex: 0 0 100%;
			width: 100%;
			max-width: 100%;
			margin: 0;
			padding: 0;
		}

		.lead-form-shell .lead-form > .container-fluid > .row > [class*="col-"] > .form-label,
		.lead-form-shell .lead-form > .form-row > [class*="col-"] > .form-label,
		.lead-form-shell .lead-form > section > .form-row > [class*="col-"] > .form-label,
		.lead-form-shell .lead-form > .form-row > .form-group > .form-label,
		.lead-form-shell .lead-form > section > .form-row > .form-group > .form-label {
			grid-column: 1;
			padding-top: 16px;
			margin: 0;
			font-size: 15px;
			font-weight: 700;
			color: #17324b !important;
			letter-spacing: 0.02em;
			text-transform: uppercase;
		}

		.lead-form-shell .lead-form > .container-fluid > .row > [class*="col-"] > :not(.form-label),
		.lead-form-shell .lead-form > .form-row > [class*="col-"] > :not(.form-label),
		.lead-form-shell .lead-form > section > .form-row > [class*="col-"] > :not(.form-label),
		.lead-form-shell .lead-form > .form-row > .form-group > :not(.form-label),
		.lead-form-shell .lead-form > section > .form-row > .form-group > :not(.form-label) {
			grid-column: 2;
			min-width: 0;
		}

		.lead-form-shell .lead-form .coworking-voucher-head,
		.lead-form-shell .lead-form .voucher-section-title,
		.lead-form-shell .lead-form .coworking-voucher-kicker,
		.lead-form-shell .lead-form .coworking-voucher-copy,
		.lead-form-shell .lead-form .coworking-voucher-meta {
			display: none !important;
		}

		.lead-form-shell .lead-form .coworking-voucher-lead,
		.lead-form-shell .lead-form .voucher-section {
			border: 0 !important;
			background: transparent !important;
			padding: 0 !important;
			margin: 0 !important;
		}

		.lead-form-shell .lead-form .form-control,
		.lead-form-shell .lead-form .form-select,
		.lead-form-shell .lead-form .select2-container--default .select2-selection--single,
		.lead-form-shell .lead-form .select2-container--white .select2-selection--single,
		.lead-form-shell .lead-form .select2-container--white .select2-selection--multiple,
		.lead-form-shell .lead-form .choice-group {
			min-height: 54px;
			border: 1px solid #cfe0f1 !important;
			border-radius: 14px !important;
			background: #fff !important;
			box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8);
			font-size: 15px;
			color: #16324f;
		}

		.lead-form-shell .lead-form input.form-control,
		.lead-form-shell .lead-form select.form-control,
		.lead-form-shell .lead-form select.form-select,
		.lead-form-shell .lead-form textarea.form-control {
			padding: 14px 16px;
			height: auto !important;
		}

		.lead-form-shell .lead-form textarea.form-control {
			min-height: 120px !important;
			resize: vertical;
		}

		.lead-form-shell .lead-form .choice-group {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 16px;
			padding: 14px 16px;
			margin: 0 !important;
		}

		.lead-form-shell .lead-form .choice-group > [class*="col-"] {
			flex: 0 0 auto;
			width: auto;
			max-width: none;
			padding: 0;
		}

		.lead-form-shell .lead-form .form-check {
			display: inline-flex !important;
			align-items: center;
			gap: 8px;
			margin: 0;
		}

		.lead-form-shell .lead-form .form-check-label {
			font-size: 14px !important;
			font-weight: 600;
			color: #566a7f;
		}

		.lead-form-shell .lead-form .probability-field {
			max-width: none;
			padding-top: 10px;
		}

		.lead-form-shell .lead-form .training-course-select {
			width: 100%;
			min-width: 0;
			max-width: 100%;
			display: block;
		}

		.lead-form-shell .lead-form .training-course-select + .select2-container,
		.lead-form-shell .lead-form .select2-container {
			width: 100% !important;
		}

		.lead-form-shell .lead-form .select2-container--default .select2-selection--single,
		.lead-form-shell .lead-form .select2-container--white .select2-selection--single {
			height: 54px;
			display: flex;
			align-items: center;
			padding: 0 44px 0 16px;
		}

		.lead-form-shell .lead-form .select2-container--default .select2-selection--single .select2-selection__rendered,
		.lead-form-shell .lead-form .select2-container--white .select2-selection--single .select2-selection__rendered {
			padding: 0;
			line-height: 1.2 !important;
			border: 0 !important;
			height: auto !important;
			color: #16324f;
			background: transparent;
		}

		.lead-form-shell .lead-form .select2-container--white .select2-selection--multiple,
		.lead-form-shell .lead-form .select2-container--default .select2-selection--multiple {
			height: auto;
			padding: 8px 12px;
		}

		.lead-form-shell .lead-form .select2-container--white .select2-selection--multiple .select2-selection__rendered,
		.lead-form-shell .lead-form .select2-container--default .select2-selection--multiple .select2-selection__rendered {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			margin: 0;
			padding: 0;
		}

		.lead-form-shell .lead-form .select2-container--white .select2-selection--multiple .select2-selection__choice,
		.lead-form-shell .lead-form .select2-container--default .select2-selection--multiple .select2-selection__choice {
			margin: 0;
			padding: 6px 12px;
			border-radius: 999px;
			border: 1px solid #cfe0ff;
			background: #edf4ff;
			color: #1b4880;
			font-size: 13px;
			font-weight: 600;
		}

		.lead-form-shell .lead-form .select2-container--white .select2-selection--multiple .select2-selection__choice__remove,
		.lead-form-shell .lead-form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
			position: static;
			margin-right: 6px;
			color: #7990ac;
			border: 0;
			background: transparent;
		}

		.lead-form-shell .lead-form .select2-dropdown {
			border: 1px solid #d6e4f3;
			border-radius: 14px;
			box-shadow: 0 18px 38px rgba(15, 42, 78, 0.12);
			overflow: hidden;
			margin-top: 8px;
		}

		.lead-form-shell .lead-form .select2-search--dropdown {
			padding: 12px;
			border-bottom: 1px solid #edf2f8;
		}

		.lead-form-shell .lead-form .select2-search--dropdown .select2-search__field {
			border: 1px solid #d6e4f3;
			border-radius: 10px;
			padding: 8px 10px;
			outline: none;
			width: 100%;
			box-sizing: border-box;
		}

		.training-course-option {
			display: flex;
			flex-direction: column;
			gap: 2px;
			line-height: 1.3;
		}

		.training-course-option-line {
			display: block;
			white-space: normal;
		}

		.training-course-option-label {
			font-weight: 700 !important;
			color: #54667a;
		}

		.training-course-option-value {
			color: #343434;
		}

		.select2-results__option--highlighted .training-course-option-label,
		.select2-results__option--highlighted .training-course-option-value {
			color: inherit;
		}

		.lead-actions {
			display: flex;
			justify-content: flex-end;
			gap: 14px;
			margin-top: 6px;
			padding-top: 10px;
		}

		.lead-action-primary,
		.lead-action-secondary {
			min-width: 140px;
			height: 48px;
			border-radius: 12px;
			font-size: 16px;
			font-weight: 700;
		}

		@media (max-width: 900px) {
			.lead-shell {
				padding: 16px;
			}

			.lead-header {
				flex-direction: column;
				align-items: stretch;
			}

			.lead-header-tools {
				width: 100%;
			}

			.lead-body {
				padding: 24px 20px 28px;
			}

			.lead-form-shell .lead-form > .container-fluid > .row > [class*="col-"],
			.lead-form-shell .lead-form > .form-row > [class*="col-"],
			.lead-form-shell .lead-form > section > .form-row > [class*="col-"],
			.lead-form-shell .lead-form > .form-row > .form-group,
			.lead-form-shell .lead-form > section > .form-row > .form-group {
				grid-template-columns: 1fr;
				gap: 10px;
			}

			.lead-form-shell .lead-form > .container-fluid > .row > [class*="col-"] > .form-label,
			.lead-form-shell .lead-form > .form-row > [class*="col-"] > .form-label,
			.lead-form-shell .lead-form > section > .form-row > [class*="col-"] > .form-label,
			.lead-form-shell .lead-form > .form-row > .form-group > .form-label,
			.lead-form-shell .lead-form > section > .form-row > .form-group > .form-label,
			.lead-form-shell .lead-form > .container-fluid > .row > [class*="col-"] > :not(.form-label),
			.lead-form-shell .lead-form > .form-row > [class*="col-"] > :not(.form-label),
			.lead-form-shell .lead-form > section > .form-row > [class*="col-"] > :not(.form-label),
			.lead-form-shell .lead-form > .form-row > .form-group > :not(.form-label),
			.lead-form-shell .lead-form > section > .form-row > .form-group > :not(.form-label) {
				grid-column: 1;
				padding-top: 0;
			}
		}

		@media (max-width: 640px) {
			.panel-title.lead-title {
				font-size: 26px;
			}

			.lead-create-card .panel-heading,
			.lead-body {
				padding-left: 16px;
				padding-right: 16px;
			}

			.lead-actions {
				flex-direction: column;
			}

			.lead-action-primary,
			.lead-action-secondary {
				width: 100%;
			}
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function bindProbabilityDisplays() {
				function syncProbabilityRange(range) {
					var field = range.closest('.probability-field');
					var displayId = range.getAttribute('data-probability-display-id');
					var display = displayId ? document.getElementById(displayId) : (field ? field.querySelector('.probability-display span') : null);
					if (!field) return;

					var min = parseFloat(range.min || 0);
					var max = parseFloat(range.max || 100);
					var value = parseFloat(range.value || 0);
					var progress = max > min ? ((value - min) / (max - min)) * 100 : 0;

					field.style.setProperty('--probability-progress', Math.max(0, Math.min(100, progress)) + '%');
					if (display) display.textContent = value + '%';
				}

				function syncAllProbabilityRanges() {
					document.querySelectorAll('.probability-range').forEach(function (range) {
						syncProbabilityRange(range);
					});
				}

				document.querySelectorAll('.probability-range').forEach(function (range) {
					var update = function () {
						syncProbabilityRange(range);
					};
					range.addEventListener('input', update);
					update();
				});

				window.addEventListener('resize', function () {
					syncAllProbabilityRanges();
				});

				return syncAllProbabilityRanges;
			}

			function revealLeadForm() {
				setTimeout(function () {
					document.body.classList.add('lead-ready');
				}, 200);
			}

			document.addEventListener('DOMContentLoaded', function () {
				bindProbabilityDisplays();
				revealLeadForm();
			});
		})();
	</script>
	@if($errors->any() && !session('error'))
		<script>
			(function () {
				if (!window.swal) return;
				swal({
					title: 'Validation Error',
					text: 'Please fix the highlighted fields below and try again.',
					type: 'error'
				});
			})();
		</script>
	@endif
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			[
				['lead-country-select', 'lead-city-select'],
				['certification-country-select', 'certification-city-select'],
				['coworking-country-select', 'coworking-city-select'],
				['study-abroad-country-select', 'study-abroad-city-select']
			].forEach(function (pair) {
				if (!document.getElementById(pair[0]) || !document.getElementById(pair[1])) {
					return;
				}

				CountryCityLoader.init(pair[0], pair[1], {
					country: @json($prefillCountry),
					city: @json($prefillCity)
				});
			});
		});
	</script>
	<script>
		(function () {
			function escapeHtml(value) {
				return String(value ?? '')
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#39;');
			}

			function formatTrainingCourseOption(state) {
				if (!state.id) {
					return state.text;
				}

				var option = state.element;
				if (!option) {
					return state.text;
				}

				var title = option.getAttribute('data-title') || state.text || '';
				var fee = option.getAttribute('data-fee') || '';
				var duration = option.getAttribute('data-duration') || '';

				return (
					'<div class="training-course-option">' +
						'<span class="training-course-option-line"><span class="training-course-option-label">' + escapeHtml(title) + '</span></span>' +
						'<span class="training-course-option-line"><span class="training-course-option-label">Fee:</span> <span class="training-course-option-value">' + escapeHtml(fee) + '</span></span>' +
						'<span class="training-course-option-line"><span class="training-course-option-label">Duration:</span> <span class="training-course-option-value">' + escapeHtml(duration) + ' months</span></span>' +
					'</div>'
				);
			}

			function formatTrainingCourseSelection(state) {
				if (!state.id) {
					return state.text;
				}

				var option = state.element;
				return option ? (option.getAttribute('data-title') || state.text || '') : state.text;
			}

			$(function () {
				if (!window.jQuery || !$.fn.select2) {
					return;
				}

				$('.training-course-select').select2({
					width: '100%',
					templateResult: function (state) {
						return $(formatTrainingCourseOption(state));
					},
					templateSelection: function (state) {
						return formatTrainingCourseSelection(state);
					},
					escapeMarkup: function (markup) {
						return markup;
					}
				});
			});
		})();
	</script>
@endpush
