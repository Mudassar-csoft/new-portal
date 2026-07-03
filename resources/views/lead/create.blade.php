@extends('layouts.theme')

@section('title', $formTitle ?? 'New Lead')

@section('content')
	@php
		$leadPrefill = $leadPrefill ?? [];
		$prefillCountry = old('details.country', data_get($leadPrefill, 'details.country', 'Pakistan'));
		$prefillCity = old('city', $leadPrefill['city'] ?? 'Faisalabad');
		$formAction = $formAction ?? route('leads.store');
		$formMethod = strtoupper((string) ($formMethod ?? 'POST'));
		$formTitle = $formTitle ?? 'Create New Lead';
		$formSubmitLabel = $formSubmitLabel ?? 'Create Lead';
		$cancelUrl = $cancelUrl ?? url()->previous();
		$leadTypeSelectEnabled = (bool) ($leadTypeSelectEnabled ?? true);
		$selectedWebLeadId = request('web_lead') ?: ($webLead->id ?? null);
		$leadTypeOptions = [
			'training' => 'Training',
			'coworking' => 'Coworking Space',
			'study_abroad' => 'Study Abroad',
			'certification' => 'Certification Exam',
		];
		$selectedLeadType = old('type', request('type', data_get($leadPrefill, 'type', 'training')));
		if (! array_key_exists($selectedLeadType, $leadTypeOptions)) {
			$selectedLeadType = 'training';
		}
	@endphp

	<div class="lead-shell">
		<div class="lead-content">
			<div class="lead-card box-typical box-typical-dashboard panel panel-default">
				<header class="box-typical-header panel-heading lead-header">
					<div class="tbl w-100">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title p-0 m-0">
								<h2 class="panel-title lead-title">
									{{ $formTitle }}
									<span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
								</h2>
							</div>
							<div class="tbl-cell lead-header-tools">
								<select
									id="leadTypeSelect"
									class="form-control lead-type-select"
									@if($leadTypeSelectEnabled)
										onchange="var selectedOption=this.options[this.selectedIndex]; if(selectedOption && selectedOption.dataset.url){ window.location.href=selectedOption.dataset.url; }"
									@else
										disabled
									@endif
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
						</div>
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

					<form method="POST" action="{{ $formAction }}" id="lead-create-form" class="lead-form-shell">
						@csrf
						@if($formMethod !== 'POST')
							@method($formMethod)
						@endif
						<input type="hidden" name="web_lead_id" value="{{ old('web_lead_id', $webLead->id ?? null) }}">
						<input type="hidden" name="type" id="lead-type-field" value="{{ $selectedLeadType }}">

						@include('lead.' . $selectedLeadType)

						<div class="form-actions lead-actions mb-2 mt-3 text-right">
							<button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04">{{ $formSubmitLabel }}</button>
							<a href="{{ $cancelUrl }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04">Cancel</a>
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
        :root {
            --typo-lead-create-font-weight-1: 600;
        }

		.lead-shell {
			font-family: 'Proxima Nova', sans-serif;
			position: relative;
			min-height: 100vh;
			width: 100%;
			overflow: hidden;
			padding: 0;
			margin: 0;
		}

		.lead-content {
			position: relative;
			min-height: 400px;
		}

		.lead-card {
			overflow: visible !important;
			max-height: none !important;
			border: 1px solid #e3edf7;
			box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
			background: #fff;
		}

		.lead-card .panel-heading {
			padding: 10px 20px;
		}

		.lead-card .panel-body {
			max-height: none !important;
			overflow: visible !important;
		}

		.lead-header {
			border-bottom: 1px solid #e6eef3;
			background: #fff;
		}

		.lead-header .tbl-row {
			display: flex;
			align-items: center;
			gap: 16px;
		}

		.lead-header .tbl-cell-title {
			flex: 1 1 auto;
			min-width: 0;
		}

		.lead-title {
			font-size: 18px;
			font-weight: 500;
			color: #25364a;
			margin: 0;
		}

		.lead-title > span {
			font-size: 14px;
			font-weight: 400;
			color: #5f7289;
		}

		.lead-header-tools {
			width: 220px;
			min-width: 220px;
			margin-left: auto;
			text-align: left !important;
		}

		.lead-body {
			padding: 10px 10px 5px;
			overflow: visible !important;
		}

		.lead-type-select,
		.lead-form-shell .form-control,
		.lead-form-shell .form-select,
		.lead-form-shell .select2-container--default .select2-selection--single,
		.lead-form-shell .select2-container--default .select2-selection--multiple,
		.lead-form-shell .select2-container--white .select2-selection--single,
		.lead-form-shell .select2-container--white .select2-selection--multiple {
			border-radius: 12px;
			border: 1px solid #d6e2f0;
			background: #fff;
			box-shadow: none;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.lead-type-select,
		.lead-form-shell .form-control,
		.lead-form-shell .form-select {
			min-height: 46px;
			padding: 10px 14px;
		}

		.lead-type-select,
		.lead-form-shell select.form-control,
		.lead-form-shell select.form-select {
			color: #495057;
		}

		.lead-type-select {
			text-align: left;
			text-align-last: left;
		}

		.lead-type-select option {
			text-align: left;
		}

		.lead-form-shell .form-control:focus,
		.lead-form-shell .form-select:focus,
		.lead-type-select:focus {
			border-color: #14a2f6;
			box-shadow: 0 0 0 3px rgba(20, 162, 246, 0.12);
		}

		.lead-form-shell .form-control[disabled],
		.lead-form-shell .form-control[readonly] {
			background: #f4f8fc;
			color: #5f7289;
		}

		.lead-form-shell textarea.form-control {
			min-height: 92px;
			resize: vertical;
		}

		.lead-form-shell .lead-form {
			display: block;
			max-height: none !important;
			overflow: visible !important;
		}

		.lead-form-shell .container-fluid {
			padding: 0;
		}

		.lead-form-shell .row,
		.lead-form-shell .form-row {
			margin-left: -10px;
			margin-right: -10px;
		}

		.lead-form-shell .row > [class*="col-"],
		.lead-form-shell .form-row > [class*="col-"] {
			padding-left: 10px;
			padding-right: 10px;
		}

		.lead-form-shell .form-group {
			margin-bottom: 18px;
		}

		.lead-form-shell label,
		.lead-form-shell .form-label {
			display: block;
			min-height: 22px;
			margin-bottom: 8px;
			font-weight: var(--typo-lead-create-font-weight-1);
			color: #223a57 !important;
		}

		.lead-form-shell .choice-group {
			align-items: center;
			padding-top: 4px;
			padding-bottom: 2px;
		}

		.lead-form-shell .choice-group .form-check {
			gap: 3px;
		}

		.lead-form-shell .choice-group.is-invalid {
			border: 1px solid #e53935;
			border-radius: 6px;
			margin-left: 0;
			margin-right: 0;
			padding: 4px 0;
		}

		.lead-form-shell .field-error {
			margin-top: 6px;
			font-size: 12px;
			color: #dc3545;
		}

		.lead-form-shell .form-control.is-invalid,
		.lead-form-shell .form-select.is-invalid,
		.lead-form-shell .form-control-range.is-invalid {
			border-color: #e53935;
			box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
		}

		.lead-form-shell .select2-container--default .select2-selection--single,
		.lead-form-shell .select2-container--white .select2-selection--single {
			min-height: 46px;
			height: 46px;
			padding: 8px 14px;
			display: flex;
			align-items: center;
			position: relative;
			overflow: hidden;
		}

		.lead-form-shell .select2-container--default .select2-selection--single .select2-selection__rendered,
		.lead-form-shell .select2-container--white .select2-selection--single .select2-selection__rendered {
			border: 0 !important;
			background: transparent !important;
			padding-left: 0 !important;
			padding-right: 2rem !important;
			height: auto !important;
			line-height: 1.3;
			color: #495057;
		}

		.lead-form-shell .select2-container--default .select2-selection--single .select2-selection__arrow,
		.lead-form-shell .select2-container--white .select2-selection--single .select2-selection__arrow {
			height: 100% !important;
			width: 38px;
			right: 0;
			top: 0;
			border-left: 1px solid #d6e2f0;
			border-radius: 0 !important;
			background: #eef4f8;
		}

		.lead-form-shell .select2-container--default .select2-selection--single.is-invalid,
		.lead-form-shell .training-course-select.is-invalid + .select2-container .select2-selection--single {
			border-color: #e53935 !important;
			box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
		}

		.lead-form-shell .select2-dropdown {
			border: 1px solid #d6e2f0;
			border-radius: 12px;
			box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
			overflow: hidden;
		}

		.lead-form-shell .select2-search--dropdown {
			padding: 10px;
			border-bottom: 1px solid #e8eef5;
			background: #fff;
		}

		.lead-form-shell .select2-search--dropdown .select2-search__field {
			min-height: 38px;
			border-radius: 10px;
			border: 1px solid #d6e2f0;
			padding: 8px 10px;
			outline: none;
		}

		.lead-form-shell .select2-results__options {
			padding: 6px;
			background: #fff;
		}

		.lead-form-shell .select2-results__option {
			border-radius: 8px;
			padding: 8px 10px;
			color: #223a57;
		}

		.lead-form-shell .select2-results__option--highlighted[aria-selected],
		.lead-form-shell .select2-results__option--highlighted[data-selected] {
			background: #f2f8ff !important;
			color: #223a57 !important;
		}

		.training-course-select {
			width: 100%;
			min-width: 0;
			max-width: 100%;
			display: block;
		}

		.training-course-select + .select2-container {
			width: 100% !important;
		}

		.training-course-option {
			display: flex;
			flex-direction: column;
			gap: 0;
			line-height: 1.25;
		}

		.training-course-option-line {
			display: block;
			white-space: normal;
			margin-bottom: 0;
		}

		.training-course-option-label {
			font-weight: var(--typo-lead-create-font-weight-1);
			color: #183b68;
		}

		.training-course-option-value {
			color: #5f6b7a;
		}

		.lead-form-shell .probability-display {
			margin-top: 6px;
			font-weight: var(--typo-lead-create-font-weight-1);
			color: #54667a;
		}

		.lead-actions {
			padding: 0 10px 4px;
		}

		.web-lead-alert {
			margin: 0 10px 14px;
			border-radius: 12px;
			border-color: #cfe4f7;
			background: #f2f8ff;
		}

		.lead-form-shell .coworking-voucher-head,
		.lead-form-shell .voucher-section-title,
		.lead-form-shell .coworking-voucher-kicker,
		.lead-form-shell .coworking-voucher-copy,
		.lead-form-shell .coworking-voucher-meta {
			display: none !important;
		}

		.lead-form-shell .coworking-voucher-lead,
		.lead-form-shell .voucher-section {
			border: 0 !important;
			background: transparent !important;
			padding: 0 !important;
			margin: 0 !important;
		}

		.form-check-input[type="radio"] {
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			width: 14px;
			height: 14px !important;
			border: 2px solid grey;
			border-radius: 50%;
			outline: none;
			cursor: pointer;
			position: relative;
			background-color: #fff;
			transition: background 0.2s, box-shadow 0.2s;
		}

		.form-check-input[type="radio"]:checked {
			border-color: #00a8ff;
		}

		.form-check-input[type="radio"]:checked::before {
			content: '';
			position: absolute;
			top: 2px;
			left: 2px;
			width: 6px;
			height: 6px;
			border-radius: 50%;
			background-color: #00a8ff;
		}

		@media (max-width: 767px) {
			.lead-card .panel-heading {
				padding: 10px 14px;
			}

			.lead-body {
				padding: 10px 8px 4px;
			}

			.tbl-row {
				display: block;
			}

			.lead-header-tools {
				width: 100%;
				min-width: 0;
				margin-top: 10px;
				text-align: left !important;
			}

			.lead-type-select {
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
			}

			document.addEventListener('DOMContentLoaded', bindProbabilityDisplays);
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
