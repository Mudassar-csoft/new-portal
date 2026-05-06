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
					<div class="tbl w-100">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title p-0 m-0">
								<h2 class="panel-title lead-title ">Create New Lead <span class=" ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span></h2>
							</div>
							<div class="text-right Traing-head-selector" style="width: 200px; text-align: left !important;">
								<select id="leadTypeSelect" class="form-control lead-type-select" onchange="var selectedOption=this.options[this.selectedIndex]; if(selectedOption && selectedOption.dataset.url){ window.location.href=selectedOption.dataset.url; }">
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
					<form method="POST" action="{{ route('leads.store') }}">
						@csrf
						<input type="hidden" name="web_lead_id" value="{{ old('web_lead_id', $webLead->id ?? null) }}">
						<input type="hidden" name="type" id="lead-type-field" value="{{ $selectedLeadType }}">
						@include('lead.' . $selectedLeadType)
						<div class="form-actions mb-2 mt-3 text-right  mr-0">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline " style="padding: 0.4rem; padding-left:10px; margin-left:5px">Create Lead</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; padding-left:10px; ">Cancel</a>
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
		.row{
	padding:3px 10px;
	/* gap:5px;%% */
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
			padding: 10px 10px 5px;
			overflow: visible !important;
		}

		.lead-create-card {
			overflow: visible !important;
			max-height: none !important;
		}

		.lead-create-card .panel-heading {
			padding: 10px 20px;
		}

		.web-lead-alert {
			margin: 8px 12px 12px;
			padding: 10px 12px;
		}

		.lead-type-select {
			display: inline-block;
			width: 180px !important;
			min-width: 180px;
		}

		.lead-title {
			font-size: 18px;
			font-weight: 500;
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
			font-size: 12px;
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
			font-size: 14px;
			margin-top: 4px;
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
/* 
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
		} */

		input[type="range"] {
			width: 100%;
		}

		.lead-create-card .panel-body {
			max-height: none !important;
			overflow: visible !important;
		}

		/* .form-row {
			margin-bottom: 12px;
		} */

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
        margin-top: 0px;
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
			function bindProbabilityDisplays() {
				function syncProbabilityRange(range) {
					var field = range.closest('.probability-field');
					var display = field ? field.querySelector('.probability-display span') : null;
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
				// Allow the DOM to settle so the loader feels intentional
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
						'<span class="training-course-option-line"> <span class="training-course-option-label">' + escapeHtml(title) + '</span></span>' +
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
