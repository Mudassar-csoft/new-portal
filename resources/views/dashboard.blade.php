@extends('layouts.theme')

@section('title', 'Dashboard')

@section('content')
	@php
		$user = auth()->user();
		$dashboardAccess = $dashboardAccess ?? [];
		$canViewLeads = (bool) ($dashboardAccess['leads'] ?? false);
		$canViewTrainingLeads = (bool) ($dashboardAccess['training_leads'] ?? false);
		$canViewAdmissions = (bool) ($dashboardAccess['admissions'] ?? false);
		$canViewIncome = (bool) ($dashboardAccess['income'] ?? false);
		$roleSlugs = $user?->roles->pluck('slug')->filter()->map(fn ($slug) => strtolower((string) $slug))->values() ?? collect();
		$roleNames = $user?->roles->pluck('name')->filter()->map(fn ($name) => strtolower((string) $name))->values() ?? collect();
		$roleLabels = $roleSlugs->merge($roleNames)->unique()->values();
		$hasAdminDashboardRole = $roleLabels->intersect(['owner', 'admin'])->isNotEmpty();
		$hasAdmissionDashboardRole = $roleLabels->contains(fn ($role) => str_contains($role, 'admission'));
		$hasRecipientDashboardRole = $roleLabels->contains(function ($role) {
			return str_contains($role, 'recipient')
				|| str_contains($role, 'receipient')
				|| str_contains($role, 'receipt')
				|| str_contains($role, 'reception');
		});
		$showIncomeChart = $canViewIncome && !$hasRecipientDashboardRole && !$hasAdmissionDashboardRole;
		$showAdmissionProgressWidget = $canViewAdmissions && $hasAdmissionDashboardRole && !$hasAdminDashboardRole;
		$showMonthCollectionCard = $canViewIncome && !$hasRecipientDashboardRole;
		$showPendingRecoveryCard = $canViewLeads && !$hasRecipientDashboardRole;
		$stats = $dashboard['stats'] ?? [];
		$incomeSummary = $dashboard['incomeSummary'] ?? [];
		$dailyActivity = $dashboard['dailyActivity'] ?? [];
		$admissionsActivity = $dashboard['admissionsActivity'] ?? [];
		$monthlyAdmissionsInsight = $dashboard['monthlyAdmissionsInsight'] ?? [];
		$dashboardGeneratedAt = $dashboard['generatedAt'] ?? now()->toIso8601String();
		$dailyRows = $dailyActivity['rows'] ?? [];
		$admissionRows = $admissionsActivity['rows'] ?? [];
		$dailyTotals = $dailyActivity['totals'] ?? [
			'leads' => 0,
			'followups' => 0,
			'admissions' => 0,
			'collection' => 0,
		];
		$currentMonthAdmissions = (int) ($stats['currentMonthAdmissions'] ?? 0);
		$previousMonthAdmissions = (int) ($stats['previousMonthAdmissions'] ?? 0);
		$admissionProgressGoal = max($previousMonthAdmissions, $currentMonthAdmissions, 10);
		$admissionProgressPercent = min(100, (int) round(($currentMonthAdmissions / max($admissionProgressGoal, 1)) * 100));
		$monthlyAdmissionLabels = $monthlyAdmissionsInsight['labels'] ?? [];
		$monthlyAdmissionCounts = collect($monthlyAdmissionsInsight['counts'] ?? [])->map(fn ($count) => (int) $count)->values();
		$monthlyAdmissionMax = max(1, (int) $monthlyAdmissionCounts->max());
		$admissionMonthDelta = $currentMonthAdmissions - $previousMonthAdmissions;
		$showComparisonChart = $canViewTrainingLeads || $canViewAdmissions;
		$comparisonChartTitle = $canViewTrainingLeads && $canViewAdmissions
			? 'Current Month Leads vs Admissions'
			: ($canViewTrainingLeads ? 'Current Month Leads' : 'Current Month Admissions');
		$hasDashboardContent = $showIncomeChart
			|| $showAdmissionProgressWidget
			|| $showComparisonChart
			|| $canViewLeads
			|| $canViewAdmissions
			|| $showMonthCollectionCard
			|| $showPendingRecoveryCard;
		$statsColumnClass = $showIncomeChart || $showAdmissionProgressWidget ? '6' : '12';
		$statCardColumnClass = $hasRecipientDashboardRole ? 'col-12' : 'col-md-6';
		$activityColumnClass = $canViewLeads && $canViewAdmissions ? 'col-xl-6' : 'col-xl-12';
	@endphp
	<div class="dashboard-shell">
		<div id="dashboard-loader" class="dashboard-loader">
			<div class="dashboard-spinner">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p>Loading dashboard...</p>
		</div>

		<div class="dashboard-content bg-white">
		<div class="row align-middle">

		<div id="dashboard-content" class="dashboard-content">
		<div id="dashboard-top-panels" class="row pl-3 pr-3 dashboard-top-panels">

			@if($showIncomeChart || $showAdmissionProgressWidget)
			<div id="dashboard-income-panel-column" class="col-xl-6 pl-0 ml-3 mr-2 m-md-0 m-lg-0 dashboard-income-panel-column">
				@if($showIncomeChart)
					<div class="chart-statistic-box">
						<div class="chart-container row ">
							<div class="chart-txt col-md-5 p-0 m-0 ">
								<div class="chart-txt-top pt-3">
									<p ><span class="unit"style="font-size:18px !important;">RS.</span><span id="income-headline-value" class="number"style="font-size:18px !important;">{{ number_format((float) ($incomeSummary['today'] ?? 0), 0) }}</span></p>
									<p class="caption"style="font-size:18px !important;">Income</p>  
								</div>
								<div class="chart-range d-flex flex-column ml-lg-3 ml-2">
									<div class="radio">
										<input type="radio" name="income-range" id="range-today" value="today" checked>
										<label for="range-today">Today</label>
									</div>
									
									<div class="radio">
										<input type="radio" name="income-range" id="range-week" value="week">
										<label for="range-week">Weekly</label>
									</div>
									
									<div class="radio">
										<input type="radio" name="income-range" id="range-month" value="month">
										<label for="range-month">Monthly</label>
									</div>
								
									<div class="radio">
										<input type="radio" name="income-range" id="range-year" value="year">
										<label for="range-year">Yearly</label>
									</div>
								</div>
								<table class="tbl-data">
									<tr>
										<td class="collection-label pl-lg-3 pl-2" style = "font-size:14px;	">Today Collection</td>
										<td class="price color-purple collection-amount" data-income-summary="today" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['today'] ?? 0), 0) }}</td>
									</tr>
									<tr>
										<td class="collection-label pl-lg-3 pl-2 " style = "font-size:14px;	">Weekly Collection</td>
										<td class="price color-yellow collection-amount" data-income-summary="week" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['week'] ?? 0), 0) }}</td>
									</tr>
									<tr>
										<td class="collection-label pl-lg-3 pl-2" style = "font-size:14px;	">Monthly Collection</td>
										<td class="price color-lime collection-amount" data-income-summary="month" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['month'] ?? 0), 0) }}</td>
									</tr>
								</table>
							</div>
							<div class="chart-container-in col-md-7  m-0 p-0 fs-1">
								<div class="pr-md-2">
									<div class="income-chart-stage">
										<div id="chart_div" ></div>
										<div id="chart_fallback" style="display:none; height:314px; font-size:11px;">
											<svg viewBox="0 0 400 314" preserveAspectRatio="none" width="100%" height="100%">
												<defs>
													<linearGradient id="incomeGradient" x1="0" y1="0" x2="0" y2="1">
														<stop offset="0%" stop-color="#12a0ff" stop-opacity="1" />
														<stop offset="100%" stop-color="#0a87e0" stop-opacity="1" />
													</linearGradient>
												</defs>
												<rect width="400" height="314" fill="url(#incomeGradient)" />
												<polyline fill="none" stroke="#fff" stroke-width="4"
													points="20,240 80,200 140,206 200,180 260,210 320,140 380,170" />
												<circle cx="20" cy="240" r="5" fill="#fff" />
												<circle cx="80" cy="200" r="5" fill="#fff" />
												<circle cx="140" cy="206" r="5" fill="#fff" />
												<circle cx="200" cy="180" r="5" fill="#fff" />
												<circle cx="260" cy="210" r="5" fill="#fff" />
												<circle cx="320" cy="140" r="5" fill="#fff" />
												<circle cx="380" cy="170" r="5" fill="#fff" />
											</svg>
										</div>
										<div id="income-axis-top" class="income-axis income-axis-top"></div>
										<div id="income-axis-right" class="income-axis income-axis-right"></div>
									</div>
									<div class="chart-container-x"></div>
									<div class="chart-container-y"></div>
								</div>
							</div>
						</div>
					</div><!--.chart-statistic-box-->
				@elseif($showAdmissionProgressWidget)
					<div class="chart-statistic-box admission-progress-box">
						<div class="admission-insight-card">
							<div class="admission-insight-top">
								<div class="admission-insight-main">
									<div class="admission-insight-title">Current Month Admissions</div>
									<div class="admission-insight-bars">
										@foreach($monthlyAdmissionCounts as $index => $count)
											@php
												$barHeight = max(10, (int) round(($count / $monthlyAdmissionMax) * 82));
												$barLabel = $monthlyAdmissionLabels[$index] ?? '';
											@endphp
											<div class="admission-insight-bar-group">
												<div class="admission-insight-bar-track">
													<div class="admission-insight-bar-fill" style="height: {{ $barHeight }}px;"></div>
												</div>
												<div class="admission-insight-bar-label">{{ $barLabel }}</div>
											</div>
										@endforeach
									</div>
								</div>
								<div class="admission-insight-total">
									<div class="admission-insight-total-value">+{{ number_format($currentMonthAdmissions) }}</div>
									<div class="admission-insight-total-note">{{ now()->format('F Y') }}</div>
								</div>
							</div>
							<div class="admission-insight-footer">
								<div class="admission-insight-footer-block">
									<div class="admission-insight-footer-label">Previous Month</div>
									<div class="admission-insight-footer-value">{{ number_format($previousMonthAdmissions) }}</div>
								</div>
								<div class="admission-insight-footer-block">
									<div class="admission-insight-footer-label">Monthly Change</div>
									<div class="admission-insight-footer-value {{ $admissionMonthDelta >= 0 ? 'is-positive' : 'is-negative' }}">
										{{ $admissionMonthDelta >= 0 ? '+' : '' }}{{ number_format($admissionMonthDelta) }}
									</div>
								</div>
							</div>
						</div>
					</div>
				@endif
			</div>
			@endif
			<div id="dashboard-stats-panel-column" class="col-xl-{{ $statsColumnClass }} pr-4 dashboard-stats-panel-column">
				<div class="row dashboard-stats-cards-row">
					@if($canViewLeads)
					<div class="{{ $statCardColumnClass }} ">
						<article class="statistic-box red"  >
							<div class="stat-inner">
								<button class="stat-eye stat-eye-inline" data-target="stat-1" aria-label="Show today leads"><i class="fa fa-eye"></i></button>
								<a href="{{ route('leads.index', ['today' => 1]) }}" class="stat-card-link" aria-label="Open today leads list">
									<div class="number stat-number fs-2xl" data-value="{{ number_format((int) ($stats['todayLeads'] ?? 0)) }}" data-target="stat-1" data-stat-key="todayLeads" data-format="number" data-mask-mode="icon"></div>
									<div class="caption">
										<div class="caption-text">Today Leads</div>
									</div>
								</a>
							</div>
						</article>
					</div><!--.col-->
					@endif
					@if($canViewAdmissions)
					<div class="{{ $statCardColumnClass }} ">
						<article class="statistic-box purple mr-1"  >
							<div class="stat-inner">
								<button class="stat-eye stat-eye-inline" data-target="stat-2" aria-label="Show current students"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="{{ number_format((int) ($stats['currentStudents'] ?? 0)) }}" data-target="stat-2" data-stat-key="currentStudents" data-format="number" data-mask-mode="icon"></div>
								<div class="caption ">
									<div class="caption-text">Current Students</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					@endif
					@if($showMonthCollectionCard)
						<div class="{{ $statCardColumnClass }} ">
							<article class="statistic-box yellow">
								<div class="stat-inner">
									<button class="stat-eye stat-eye-inline" data-target="stat-3" aria-label="Show current month collection"><i class="fa fa-eye"></i></button>
									<div class="number stat-number" data-value="RS. {{ $stats['currentMonthCollection'] ?? '0' }}" data-target="stat-3" data-stat-key="currentMonthCollection" data-format="currency" data-mask-mode="icon"></div>
									<div class="caption ">
										<div class="caption-text">{{ now()->format('F') }} Collection</div>
									</div>
								</div>
							</article>
						</div><!--.col-->
					@endif
					@if($showPendingRecoveryCard)
						<div class="{{ $statCardColumnClass }} ">
							<article class="statistic-box green mr-1">
								<div class="stat-inner m">
									<button class="stat-eye stat-eye-inline" data-target="stat-4" aria-label="Show current month pending"><i class="fa fa-eye"></i></button>
									<div class="number stat-number" data-value="{{ number_format((int) ($stats['currentMonthPending'] ?? 0)) }}" data-target="stat-4" data-stat-key="currentMonthPending" data-format="number" data-mask-mode="icon"></div>
									<div class="caption ">
										<div class="caption-text">Pending Recovery</div>
									</div>
								</div>
							</article>
						</div><!--.col-->
					@endif
				</div><!--.row-->
			</div><!--.col-->
		
		
	    </div>
	@if($showComparisonChart)
<!--Current Month Charts-->
	<div class="row pl-4 pr-3 tables-dashbord">
		<div class="col-xl-12 pl-1 ml-1 mr-2 m-md-0 m-lg-0 current-month-chart-col">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card  bg-gray-300 ">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">{{ $comparisonChartTitle }}</span>
							</h3>
						</div>
						<div class="month-chart-header-actions">
							<!-- <button type="button" class="action-btn dashboard-panel-action" data-action="edit-title" aria-label="Edit current month leads title">
								<i class="font-icon glyphicon glyphicon-pencil"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="offset" aria-label="Move current month leads panel slightly">
								<i class="glyphicon glyphicon-move"></i>
							</button> -->
							<button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh current month leads chart">
								<i class="font-icon font-icon-refresh"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse current month leads chart" aria-expanded="true">
								<i class="font-icon font-icon-minus"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize current month leads chart">
								<i class="font-icon font-icon-expand"></i>
							</button>
							<!-- <button type="button" class="action-btn dashboard-panel-action" data-action="close" aria-label="Close current month leads chart">
								<i class="font-icon font-icon-close"></i>
							</button> -->
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<div id="lead-chart"></div>
				</div>
			</section>
		</div>

	</div>
	@endif

	@if($canViewLeads || $canViewAdmissions)
<!--Daily Activity-->
	<div class="row dashboard-equal-row pl-4 pr-3 mt-4  tables-dashbord ">
		@if($canViewLeads)
		<div class="{{ $activityColumnClass }} d-flex pl-1 ml-1 mr-2 m-md-0 m-lg-0">
			<section class="box-typical box-typical-dashboard panel panel-default daily-activity-card dashboard-equal-card bg-gray-300">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">Recent Leads</span>
							</h3>
						</div>
						<div class="month-chart-header-actions">
							<!-- <button type="button" class="action-btn dashboard-panel-action" data-action="edit-title" aria-label="Edit daily activity title">
								<i class="font-icon glyphicon glyphicon-pencil"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="offset" aria-label="Move daily activity panel slightly">
								<i class="glyphicon glyphicon-move"></i>
							</button> -->
							<button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh daily activity">
								<i class="font-icon font-icon-refresh"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse daily activity" aria-expanded="true">
								<i class="font-icon font-icon-minus"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize daily activity">
								<i class="font-icon font-icon-expand"></i>
							</button>
							<!-- <button type="button" class="action-btn dashboard-panel-action" data-action="close" aria-label="Close daily activity">
								<i class="font-icon font-icon-close"></i>
							</button> -->
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<table class="tbl-typical daily-activity-table">
						<thead>
							<tr>
								<th><div>Status</div></th>
								<th><div>Student Name</div></th>
								<th><div>Phone Number</div></th>
								<th><div>Date</div></th>
							</tr>
						</thead>
						<tbody id="recent-leads-body">
							@forelse($dailyRows as $row)
								<tr>
									<td>
										<span class="daily-status-badge daily-status-badge--{{ $row['status_tone'] ?? 'primary' }}">
											{{ $row['status_label'] ?? 'New' }}
										</span>
									</td>
									<td>
										<div class="daily-student-name">{{ $row['student_name'] ?? 'N/A' }}</div>
										@if(!empty($row['show_campus']))
											<div class="daily-student-campus">{{ $row['campus'] ?? 'Campus' }}</div>
										@endif
									</td>
									<td class="daily-phone">{{ $row['phone'] ?? 'N/A' }}</td>
									<td class="daily-date">{{ $row['date_label'] ?? 'N/A' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="daily-empty-state">No lead activity available.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</section>
		</div>
		@endif
	<!--campus Month Charts-->
		@if($canViewAdmissions)
		<div class="{{ $activityColumnClass }} d-flex pl-2 pr-4">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card dashboard-equal-card  bg-gray-300">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">Recent Admissions</span>
							</h3>
						</div>
						<div class="month-chart-header-actions">
							<!-- <button type="button" class="action-btn dashboard-panel-action" data-action="edit-title" aria-label="Edit campus admissions comparison title">
								<i class="font-icon glyphicon glyphicon-pencil"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="offset" aria-label="Move campus admissions comparison panel slightly">
								<i class="glyphicon glyphicon-move"></i>
							</button> -->
							<button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh campus admissions comparison">
								<i class="font-icon font-icon-refresh"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse campus admissions comparison" aria-expanded="true">
								<i class="font-icon font-icon-minus"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize campus admissions comparison">
								<i class="font-icon font-icon-expand"></i>
							</button>
							<!-- <button type="button" class="action-btn dashboard-panel-action" data-action="close" aria-label="Close campus admissions comparison">
								<i class="font-icon font-icon-close"></i>
							</button> -->
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body-admssion">
					<table class="tbl-typical daily-activity-table admission-activity-table">
						<thead>
							<tr>
								<th><div>Status</div></th>
								<th><div>Student Name</div></th>
								<th><div>Phone Number</div></th>
								<th><div>Date</div></th>
							</tr>
						</thead>
						<tbody id="recent-admissions-body">
							@forelse($admissionRows as $row)
								<tr>
									<td>
										<span class="daily-status-badge daily-status-badge--{{ $row['status_tone'] ?? 'primary' }}">
											{{ $row['status_label'] ?? 'Enrolled' }}
										</span>
									</td>
									<td>
										<div class="daily-student-name">{{ $row['student_name'] ?? 'N/A' }}</div>
										@if(!empty($row['show_campus']))
											<div class="daily-student-campus">{{ $row['campus'] ?? 'Campus' }}</div>
										@endif
									</td>
									<td class="daily-phone">{{ $row['phone'] ?? 'N/A' }}</td>
									<td class="daily-date">{{ $row['date_label'] ?? 'N/A' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="daily-empty-state">No admissions available.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</section>
		</div>
		@endif
	</div>
	@endif
	@if(!$hasDashboardContent)
	<div class="row pl-4 pr-3 mt-4">
		<div class="col-12">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card bg-gray-300">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">Dashboard Access</span>
							</h3>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body p-4">
					<div class="comparison-empty-state">No dashboard data is available for your current permissions.</div>
				</div>
			</section>
		</div>
	</div>
	@endif
	</div>
	</div>
@endsection

@push('styles')
	<style>
		
	</style>
@endpush

@push('styles')
	<!-- <link rel="stylesheet" href="css/lib/lobipanel/lobipanel.min.css"> -->
	<link rel="stylesheet" href="css/separate/vendor/lobipanel.min.css">
	<link rel="stylesheet" href="css/lib/jqueryui/jquery-ui.min.css">
	<link rel="stylesheet" href="css/separate/pages/widgets.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css">
	<style>
		*{

			font-size: 15px;	
		}
		body.with-side-menu.control-panel .page-content {
			padding-right: 67px !important;
		}
		.box-typical.box-typical-dashboard {
			margin: 1% 1px !important;
			height: 414px;
		}
		.chart-txt-top.pt-2 {
    font-size: 17px !important;
}
		.chart-range {
			display: flex;
			flex-wrap: wrap;
			gap: 8px 12px;
			margin: 8px 0 20px;
		}
		.chart-statistic-box {
			border-radius: 8px;
			overflow: hidden;
		}
		.chart-statistic-box .chart-container {
			margin-left: 0;
			border-radius: 8px;
			overflow: hidden;
		}
		.chart-statistic-box .chart-txt {
			float: left;
			width: 200px;
			height: 314px;
			padding: 15px 20px;
			background: #304b58;
			-webkit-border-radius: 8px 0 0 8px;
			border-radius: 8px 0 0 8px;
			color: #fff;
			position: relative;
			z-index: 5;
		}
		.chart-statistic-box .chart-txt .chart-txt-top p:first-child {
			display: flex;
			align-items: baseline;
			justify-content: center;
			gap: 6px;
			line-height: 1;
		}
		.chart-statistic-box .chart-txt .chart-txt-top .unit {
			margin: 0;
			padding: 0;
			position: static;
			top: auto;
			line-height: 1;
		}
		.chart-statistic-box .chart-txt .chart-txt-top .number {
			line-height: 1;
		}
		.chart-statistic-box .chart-container-in {
			border-radius: 0 12px 12px 0;
			overflow: hidden;
		}

		.chart-statistic-box .income-chart-stage {
			position: relative;
			height: 314px;
		}

		.chart-statistic-box #chart_div,
		.chart-statistic-box #chart_fallback {
			height: 314px;
		}

		.chart-statistic-box .income-axis {
			position: absolute;
			inset: 0;
			pointer-events: none;
			z-index: 3;
			color: #fff;
			font-family: 'Proxima Nova', sans-serif;
			font-size: 10px !important;
			font-weight: 700;
			display: none;
		}

		.chart-statistic-box .income-axis-label {
			position: absolute;
			line-height: 1;
			white-space: nowrap;
			font-size: 11px !important;
		}

		.chart-statistic-box .income-axis-top .income-axis-label {
			transform: translateX(-50%);
			font-size: 10px !important;
		}

		.chart-statistic-box .income-axis-right .income-axis-label {
			transform: translateY(-50%);
			text-align: right;
			font-size: 11px !important;
		}

		.chart-statistic-box .tbl-data {
			width: auto;
		}

		.chart-statistic-box .tbl-data .collection-label {
			width: auto;
			padding-right: 10px;
		}

		.chart-statistic-box .tbl-data .collection-amount {
			text-align: left;
			white-space: nowrap;
			padding-left: 12px;
		}

		.dashboard-top-panels > .dashboard-income-panel-column,
		.dashboard-top-panels > .dashboard-stats-panel-column {
			transition: flex-basis 0.25s ease, max-width 0.25s ease, margin 0.25s ease, padding 0.25s ease;
		}

		.dashboard-top-panels.is-year-expanded > .dashboard-income-panel-column,
		.dashboard-top-panels.is-year-expanded > .dashboard-stats-panel-column {
			flex: 0 0 100% !important;
			max-width: 100% !important;
			width: 100% !important;
		}

		.dashboard-top-panels.is-year-expanded > .dashboard-income-panel-column {
			margin-left: 0 !important;
			margin-right: 0 !important;
			padding-right: 24px !important;
		}

		.dashboard-top-panels.is-year-expanded > .dashboard-stats-panel-column {
			margin-top: 14px;
			padding-right: 0 !important;
		}

		@media (min-width: 768px) {
			.dashboard-top-panels.is-year-expanded .chart-statistic-box .chart-container {
				display: flex;
				flex-wrap: nowrap;
			}

			.dashboard-top-panels.is-year-expanded .chart-statistic-box .chart-txt {
				flex: 0 0 270px !important;
				max-width: 270px !important;
				width: 270px !important;
			}

			.dashboard-top-panels.is-year-expanded .chart-statistic-box .chart-container-in {
				flex: 1 1 auto !important;
				max-width: calc(100% - 270px) !important;
				width: calc(100% - 270px) !important;
			}

			.dashboard-top-panels.is-year-expanded .chart-statistic-box .chart-container-in > div {
				/* padding-left: 12px !important; */
				padding-right: 18px !important;
			}

			.dashboard-top-panels.is-year-expanded > .dashboard-stats-panel-column {
				padding-left: 12px !important;
				padding-right: 24px !important;
			}

			.dashboard-top-panels.is-year-expanded .dashboard-stats-cards-row {
				display: flex;
				flex-wrap: nowrap;
				gap: 18px;
				margin-left: 0 !important;
				margin-right: 0 !important;
			}

			.dashboard-top-panels.is-year-expanded .dashboard-stats-cards-row > [class*="col-"] {
				flex: 1 1 0 !important;
				max-width: none !important;
				width: auto !important;
				padding-left: 0 !important;
				padding-right: 0 !important;
				margin-bottom: 0 !important;
			}

			.dashboard-top-panels.is-year-expanded .dashboard-stats-cards-row .statistic-box {
				margin: 0 !important;
			}
		}

		.admission-progress-box {
			border-radius: 12px;
			overflow: hidden;
			background: #ffffff;
			min-height: 314px;
			border: 1px solid #e5e5e5;
			box-shadow: 0 14px 30px rgba(15, 23, 42, 0.02);
		}

		.admission-insight-card {
			height: 100%;
			min-height: 314px;
			background: #ffffff;
			color: #263748;
			display: flex;
			flex-direction: column;
			overflow: hidden;
		}

		.admission-insight-top {
			display: flex;
			align-items: flex-end;
			justify-content: space-between;
			gap: 22px;
			padding: 22px 24px 20px;
		}

		.admission-insight-main {
			flex: 1 1 auto;
			min-width: 0;
		}

		.admission-insight-title {
			font-size: 20px !important;
			font-weight: 700;
			color: #243746;
			text-align: left;
			margin-bottom: 18px;
		}

		.admission-insight-bars {
			display: flex;
			align-items: flex-end;
			gap: 18px;
			min-height: 122px;
		}

		.admission-insight-bar-group {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 8px;
		}

		.admission-insight-bar-track {
			width: 24px;
			height: 96px;
			border-radius: 4px;
			background: #e8edf5;
			position: relative;
			overflow: hidden;
		}

		.admission-insight-bar-fill {
			position: absolute;
			left: 0;
			right: 0;
			bottom: 0;
			border-radius: 4px 4px 0 0;
			background: linear-gradient(180deg, #39bbff 0%, #00a8ff 100%);
		}

		.admission-insight-bar-label {
			font-size: 13px !important;
			font-weight: 600;
			color: #31465a;
		}

		.admission-insight-total {
			flex: 0 0 170px;
			text-align: right;
			padding-bottom: 6px;
		}

		.admission-insight-total-value {
			font-size: 54px !important;
			font-weight: 800;
			line-height: 1;
			color: #00a8ff;
		}

		.admission-insight-total-note {
			font-size: 16px !important;
			color: #31465a;
			margin-top: 18px;
		}

		.admission-insight-footer {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			border-top: 1px solid #d8e1ec;
			background: #fff;
		}

		.admission-insight-footer-block {
			padding: 20px 24px;
			text-align: left;
		}

		.admission-insight-footer-block + .admission-insight-footer-block {
			border-left: 1px solid #d8e1ec;
		}

		.admission-insight-footer-label {
			font-size: 15px !important;
			font-weight: 700;
			color: #2c3f50;
			margin-bottom: 10px;
		}

		.admission-insight-footer-value {
			font-size: 34px !important;
			font-weight: 800;
			line-height: 1;
			color: #00a8ff;
		}

		.admission-insight-footer-value.is-positive {
			color: #00a8ff;
		}

		.admission-insight-footer-value.is-negative {
			color: #ef4444;
		}

		@media (max-width: 1199px) {
			.admission-insight-top {
				flex-direction: column;
				align-items: flex-start;
			}

			.admission-insight-total {
				flex: 1 1 auto;
				text-align: left;
				padding-bottom: 0;
			}
		}

		.chart-range .radio {
			margin: 0;
		}

        .chart-range .radio input {
            margin-top: 2px;
        }
		
		.box-typical .form-label-dashboard{
			font-size:15px !important;
			font-weight:500 !important;
		}
        /* Hide static axes; Google Chart handles axes dynamically */
        .chart-container-x,
        .chart-container-y {
            display: none !important;
        }

		.chart-caption , .caption-text{
			font-size: 17px !important;
            text-align: center;
            font-weight: 700;
            color: #fff;
            margin-top: 8px;
            padding-bottom: 6px;
        }

		#lead-chart .c3-legend-item text {
			font-size: 12px;
			fill: #334155;
			font-weight: 600;
		}

		#lead-chart .c3-legend-item:last-child {
			transform: none;
		}

		#lead-chart {
			min-height: 320px;
			padding: 18px 24px 8px;
		}

		#lead-chart .c3 svg {
			font-family: 'Proxima Nova', sans-serif;
		}

		#lead-chart .c3-axis-x text,
		#lead-chart .c3-axis-y text {
			fill: #526273;
			font-size: 12px;
		}

		#lead-chart .c3-axis path,
		#lead-chart .c3-axis line {
			stroke: #d7e0ea;
		}

		#lead-chart .c3-grid line {
			stroke: #edf2f7;
		}

		#lead-chart .c3-chart-bars .c3-bar {
			stroke-width: 0;
		}

		#lead-chart .c3-texts text {
			fill: #1e293b;
			font-size: 11px;
			font-weight: 600;
		}

		.comparison-empty-state {
			min-height: 280px;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 1px dashed #d7e0ea;
			border-radius: 14px;
			background: linear-gradient(180deg, #f8fbff 0%, #f3f7fb 100%);
			color: #64748b;
			font-size: 15px;
			font-weight: 600;
		}
 .statistic-box {
       -webkit-border-radius: 4px;
    border-radius: 8px;
    text-align: center;
    color: #fff;
    background: no-repeat 50% 50%;
    background-size: cover;
    margin: 0 0 30px;

    
}
        .statistic-box .stat-inner {
            position: relative;
        }

        .statistic-box .stat-card-link {
            display: block;
            color: inherit;
            text-decoration: none;
            cursor: pointer;
        }

        .statistic-box .stat-card-link:hover,
        .statistic-box .stat-card-link:focus {
            color: inherit;
            text-decoration: none;
        }

.statistic-box .number, 
.statistic-box .caption{
    font-size:28px !important;

}
*{
	font: size 15px ;
}
.text{
	font-size:14px;
}


        .statistic-box .stat-eye {
            position: absolute;
            right: 12px;
            top: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }

        .statistic-box .stat-eye:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        .statistic-box .stat-eye-inline,
        .statistic-box .stat-eye-inline.is-revealed {
            left: auto !important;
            right: 12px !important;
            top: 12px !important;
            transform: none !important;
            width: 32px;
            height: 32px;
        }

        .stat-number[data-mask-mode="icon"] {
            min-height: 44px;
        }

        .month-chart-card .panel-heading {
            padding: 4px 10px;
        }
        .month-chart-header {
            padding: 10px 16px !important;
            border-bottom: 1px solid #e6eef3;
            background: #fff;
        }
        .month-chart-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }
        .month-chart-header-wrap {
            display: flex;
            align-items: center;
            min-width: 0;
            flex: 1 1 auto;
        }
        .month-chart-header-actions {
            display: flex;
            align-items: center;
            gap: 0;
            flex-shrink: 0;
            margin-left: auto;
            justify-content: flex-end;
        }
        .month-chart-header-title {
            margin: 0;
            font-size: 15px !important;
            font-weight: 600 !important;
            color: #25364a;
            min-width: 0;
            width: 100%;
        }
        .month-chart-header-label {
            margin: 0;
            display: inline-block;
            padding: 0;
            min-width: 0;
            max-width: 100%;
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.25;
            margin-top: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border-radius: 6px;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .month-chart-header-label.is-editing {
            min-width: 220px;
            max-width: none;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            padding: 4px 8px;
            background: #fff;
            border: 1px solid #cfd9e3;
            box-shadow: 0 0 0 3px rgba(18, 160, 255, 0.12);
            outline: none;
        }
        .month-chart-header-actions .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0 0 16px;
            padding: 0;
            background: transparent;
            border: 0;
            color: #b2bcc7;
            font-size: 12.8px !important;
            line-height: 1;
            min-width: 16px;
            transition: color 0.15s ease;
        }
        .month-chart-header-actions .action-btn:first-child {
            margin-left: 0;
        }
        .month-chart-header-actions .action-btn:hover,
        .month-chart-header-actions .action-btn:focus {
            color: #7d8b99;
            background: transparent;
        }
        .month-chart-header-actions .action-btn.is-active {
            color: #4f6072;
        }
        .month-chart-header-actions .action-btn .font-icon,
        .month-chart-header-actions .action-btn .glyphicon {
            line-height: 1;
            font-size: 12.8px !important;
        }
        .month-chart-header-actions .dashboard-panel-action[data-action="fullscreen"] .font-icon {
            font-size: 14px !important;
        }
        .month-chart-header-actions .action-btn .glyphicon {
            font-family: 'Glyphicons Halflings' !important;
            font-style: normal;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .dashboard-panel-action.is-spinning .font-icon {
            animation: dashboardSpin 0.75s linear infinite;
        }
		
        .dashboard-panel-offset {
            transform: translate(24px, -14px);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 18px 40px rgba(37, 54, 74, 0.16);
        }
        .month-chart-card {
            margin-bottom: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
	.month-chart-card .panel-body > .tbl-typical td {
   
			padding:5px !important;
		}

        .month-chart-card .box-typical-body,
        .month-chart-card .panel-body {
            max-height: none !important;
            height: auto;
            overflow: hidden !important;
            padding: 0px;
        }
		
        .panel-body-admssion  {
            max-height: none !important;
            height: auto;
            overflow: auto !important;
            padding: 0px;
        }
        .dashboard-equal-row > [class*="col-"] {
            display: flex;
            align-items: flex-start;
        }
        .dashboard-equal-card {
            width: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .dashboard-content .box-typical.box-typical-collapsed {
            height: auto !important;
            min-height: 0 !important;
            align-self: flex-start;
        }
        .dashboard-content .box-typical.box-typical-collapsed .box-typical-body,
        .dashboard-content .box-typical.box-typical-collapsed .panel-body {
            display: none !important;
            height: 0 !important;
            min-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            overflow: hidden !important;
            flex: 0 0 auto !important;
        }
        .dashboard-equal-card .panel-body,
        .dashboard-equal-card .box-typical-body {
            flex: 1 1 auto;
        }
        body.dashboard-panel-fullscreen-active {
            overflow: hidden;
        }

        .dashboard-content .box-typical.box-typical-full-screen {
            position: fixed !important;
            top: 10px !important;
            right: 10px !important;
            bottom: 10px !important;
            left: 10px !important;
            width: auto !important;
            height: auto !important;
            max-width: none !important;
            margin: 0 !important;
            z-index: 1200 !important;
            display: flex !important;
            flex-direction: column !important;
            transform: none !important;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.26);
        }
        .dashboard-content .box-typical.box-typical-full-screen .box-typical-header {
            flex: 0 0 auto;
        }
        .dashboard-content .box-typical.box-typical-full-screen .box-typical-body,
        .dashboard-content .box-typical.box-typical-full-screen .panel-body {
            flex: 1 1 auto;
            height: auto !important;
            max-height: none !important;
            overflow: auto !important;
        }
        .dashboard-content .box-typical.box-typical-full-screen #leads-chart,
        .dashboard-content .box-typical.box-typical-full-screen #admissions-chart,
        .dashboard-content .box-typical.box-typical-full-screen #campus-admissions-chart {
            height: calc(100vh - 120px) !important;
            min-height: 420px;
        }
        .dashboard-content .box-typical.box-typical-full-screen.daily-activity-card .panel-body {
            min-height: 0;
        }
        .daily-activity-card .panel-body {
            min-height: 320px;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        .dashboard-equal-card #campus-admissions-chart {
            height: 320px;
        }

        #leads-chart,
        #admissions-chart {
            height: 360px;
            width: 100%;
            max-width: 100%;
        }

        #campus-admissions-chart {
            height: 250px;
            width: 100%;
        }

        /* Page loader */
        .dashboard-shell {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
			
			font-size:10px;
			margin: 1%;
        }

        .dashboard-loader {
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

        .dashboard-spinner {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .dashboard-spinner .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }
		text{
			font-size:8px !important;
			
		}
        .dashboard-spinner .dot:nth-child(2) {
            animation-delay: 0.15s;
            background: #1f8ef1;
        }

        .dashboard-spinner .dot:nth-child(3) {
            animation-delay: 0.3s;
            background: #36b1ff;
        }

        .dashboard-loader p {
            margin: 0;
            color: #54667a;
            font-weight: 600;
        }

        .dashboard-content {
			padding: 10px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        .dashboard-live-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            padding: 6px 31px 14px;
            color: #5b6b79;
        }

        .dashboard-live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #1c7c54;
        }

        .dashboard-live-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.14);
            animation: dashboardPulse 1.8s ease-in-out infinite;
        }

        .dashboard-live-meta {
            font-size: 13px !important;
            font-weight: 500;
        }

        .dashboard-live-refresh {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border: 1px solid #cfe0f5;
            border-radius: 999px;
            background: #eef5ff;
            color: #0a6fd1;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s ease, transform 0.18s ease;
        }

        .dashboard-live-refresh:hover {
            background: #d6eaff;
        }

        .dashboard-live-refresh.is-spinning i {
            animation: dashboardRefreshSpin 0.8s linear infinite;
        }

        @keyframes dashboardRefreshSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        body.dashboard-ready .dashboard-content {
			opacity: 1;
            visibility: visible;
			width: 100%
        }

        body.dashboard-ready #dashboard-loader {
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
        @keyframes dashboardSpin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes dashboardPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(0.85);
                opacity: 0.8;
            }
        }

        .daily-activity-card .panel-heading {
            border-top: 0;
        }
        .current-month-chart-col {
            flex: 0 0 calc(100% - 8px);
            max-width: calc(100% - 8px);
        }
        .daily-activity-card .panel-body {
            padding: 0px;
        }
        .daily-activity-card .tbl-typical {
            width: 100%;
            margin-bottom: 0;
            table-layout: auto;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .daily-activity-card .tbl-typical td {
            font-size: 13px !important;
            line-height: 1.45;
            vertical-align: middle;
            color: #304b58;
            width: auto !important;
            padding: 5px 5px;
            border-bottom: 1px solid #e6edf3;
        }
		 .daily-activity-card .tbl-typical th
         {
            font-size: 13px !important;
            line-height: 1.45;
            vertical-align: middle;
            color: #304b58;
            width: auto !important;
            /* padding: 5px 5px; */
            border-bottom: 1px solid #e6edf3;
        }
        .daily-activity-card .tbl-typical th {
            font-weight: 700;
            text-align: left;
            white-space: normal;
            color: #5d7386;
            background: #f7fafc;
            border-bottom-color: #d9e4ee;
        }
        .tbl-typical th > div {
            padding: 6px 5px !important;
        }
        .daily-activity-card .tbl-typical td:not(:first-child),
        .daily-activity-card .tbl-typical th:not(:first-child) {
            text-align: left;
        }
        .daily-activity-table td:nth-child(2),
        .daily-activity-table th:nth-child(2) {
            text-align: left !important;
        }
        .daily-activity-table tbody tr td {
            border-bottom: 1px solid #e6edf3 !important;
        }
        .daily-activity-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .daily-status-badge {
            display: inline-block;
            min-width: 67px;
            padding: 6px 7px;
            border-radius: 999px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.1;
            text-align: center;
        }
        .daily-status-badge--primary {
            background: #ff4f87;
        }
        .daily-status-badge--info {
            background: #1296eb;
        }
        .daily-status-badge--success {
            background: #45c156;
        }
        .daily-status-badge--warning {
            background: #ffae2b;
        }
        .daily-status-badge--orange {
            background: #f97316;
        }
        .daily-status-badge--muted {
            background: #6f7d8c;
        }
        .daily-status-badge--danger {
            background: #fa424a;
        }
        .daily-student-name {
            color: #304b58;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
        }
        .daily-student-campus {
            margin-top: 4px;
            color: #8a9aaa;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }
        .daily-phone,
        .daily-date {
            font-size: 13px !important;
            font-weight: 500;
        }
        .daily-empty-state {
            padding: 26px 14px !important;
            color: #7b8b99 !important;
            font-size: 14px !important;
            font-weight: 600;
            text-align: center !important;
            border-bottom: 0 !important;
        }

        @media (max-width: 767px) {
            .dashboard-shell {
                margin: 0 !important;
            }

            .dashboard-shell .dashboard-content {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .dashboard-shell .row {
                margin-left: 3px !important;
                margin-right: 3px !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .dashboard-shell [class*="col-"] {
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .dashboard-shell .box-typical.box-typical-dashboard,
            .dashboard-shell .statistic-box {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .dashboard-shell .col-xl-6,
            .dashboard-shell .col-md-6,
            .dashboard-shell .dashboard-equal-row > [class*="col-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .dashboard-shell .chart-statistic-box,
            .dashboard-shell .statistic-box,
            .dashboard-shell .month-chart-card,
            .dashboard-shell .dashboard-equal-card {
                width: 100% !important;
            }

            .statistic-box .stat-inner {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 18px 12px !important;
            }

            .statistic-box .number,
            .statistic-box .caption,
            .statistic-box .caption .text {
                width: 100%;
                text-align: center !important;
                padding: 0 !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .statistic-box .number {
                margin-top: 0 !important;
                margin-bottom: 8px !important;
                line-height: 1.15;
            }

            .statistic-box .caption {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }

            .statistic-box .caption .text {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }

            .chart-statistic-box .chart-range,
            .chart-statistic-box .tbl-data {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .chart-statistic-box .chart-container-in > div {
                padding-right: 0 !important;
            }

            .chart-statistic-box .chart-container {
                display: flex;
                flex-wrap: wrap;
				justify-content: center;
            }

            .chart-statistic-box .chart-txt {
                float: none;
                width: 100% !important;
                max-width: 100%;
                height: auto;
                min-height: 0;
                display: flex;
                flex-wrap: wrap;
                align-items: flex-start;
                gap: 12px 16px;
                padding: 16px 14px !important;
                border-radius: 4px 4px 0 0;
                flex: 0 0 100%;
            }

            .chart-statistic-box .chart-txt .chart-txt-top {
                flex: 1 1 100%;
                padding-top: 0 !important;
            }

            .chart-statistic-box .chart-txt .chart-range {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px 14px;
                margin: 0 !important;
                width: 100%;
            }

            .chart-statistic-box .chart-txt .tbl-data {
                width: 100%;
                margin: 0 !important;
            }

            .chart-statistic-box .chart-txt .tbl-data td {
                padding-top: 4px;
                padding-bottom: 4px;
            }

            .chart-statistic-box .chart-container-in {
                flex: 0 0 100%;
                max-width: 100%;
                border-radius: 0 0 4px 4px;
            }

            .daily-activity-card .panel-body {
                overflow-x: auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            .daily-activity-card .tbl-typical {
                width: max-content !important;
                min-width: 100% !important;
            }

            .daily-activity-card .tbl-typical th,
            .daily-activity-card .tbl-typical td {
                white-space: nowrap;
            }
        }
    </style>
@endpush

@push('scripts')
	<script src="js/lib/jqueryui/jquery-ui.min.js"></script>
	<script src="js/lib/lobipanel/lobipanel.min.js"></script>
	<script src="js/lib/match-height/jquery.matchHeight.min.js"></script>
	<script src="https://www.gstatic.com/charts/loader.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.16.0/d3.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.js"></script>
	<script>
		var dashboardData = @json($dashboard ?? []);
		var dashboardAccessMap = @json($dashboardAccess ?? []);
		var dashboardRefreshUrl = @json(route('dashboard.live-data', request()->query()));
		var incomeRanges = @json($dashboard['incomeRanges'] ?? []);
		var chartSeries = @json($dashboard['charts'] ?? []);
		var defaultIncomeRanges = {
			today: { label: 'Today income (hourly)', points: [['08 AM', 0]], ticks: [0, 10] },
			week: { label: 'Week income (daily)', points: [['Mon', 0]], ticks: [0, 10] },
			month: { label: 'Month income (weekly)', points: [['Week 1', 0], ['Week 2', 0], ['Week 3', 0], ['Week 4', 0]], ticks: [0, 10] },
			year: { label: 'Year income (monthly)', points: [['Jan', 0]], ticks: [0, 10] }
		};
		incomeRanges = Object.assign({}, defaultIncomeRanges, incomeRanges || {});

		var currentIncomeRange = 'today';

		$(document).ready(function () {
			var maskedValue = '***';
			var refreshIntervalMs = 15000;
			var refreshRequest = null;
			var monthlyComparisonChart = null;
			$('.panel').each(function () {
				try {
					$(this).lobiPanel({
						sortable: true
					}).on('dragged.lobiPanel', function () {
						$('.dahsboard-column').matchHeight();
					});
				} catch (err) { }
			});

			function showChartFallback() {
				if (!document.getElementById('chart_div') || !document.getElementById('chart_fallback')) {
					return;
				}
				$('#chart_div').hide();
				$('#chart_fallback').show();
				clearIncomeAxisOverlay();
				var range = incomeRanges[currentIncomeRange] || incomeRanges.today;
				$('.chart-caption').text(range.label);
				updateIncomeHeadline();
			}

			$(document).on('click', '.panel-action', function (e) {
				e.preventDefault();
				var action = $(this).data('action');
				var panel = $(this).closest('.box-typical');

				switch (action) {
					case 'close':
						panel.remove();
						break;
					case 'fullscreen':
						panel.toggleClass('box-typical-full-screen');
						break;
					case 'refresh':
						panel.addClass('panel-loading');
						setTimeout(function () {
							panel.removeClass('panel-loading');
						}, 500);
						break;
					case 'collapse':
						var target = $(this).data('target');
						if (target) {
							$(target).collapse('toggle');
						} else {
							panel.find('.box-typical-body').collapse('toggle');
						}
						break;
					default:
						break;
				}
			});

			$(document).on('click', '.dashboard-panel-action', function (e) {
				e.preventDefault();
				var action = $(this).data('action');
				var targetSelector = $(this).data('target-panel');
				var panel = targetSelector ? $(targetSelector).first() : $(this).closest('.box-typical');
				var button = $(this);
				var body = panel.find('.box-typical-body').first();
				var titleLabel = panel.find('.month-chart-header-label').first();

				if (!panel.length) {
					return;
				}

				function syncFullscreenState() {
					$('body').toggleClass('dashboard-panel-fullscreen-active', $('.box-typical.box-typical-full-screen').length > 0);
				}

				function finishTitleEdit(revertChanges) {
					if (!titleLabel.length || !titleLabel.hasClass('is-editing')) {
						return;
					}

					var originalText = titleLabel.data('original-text') || '';
					var nextText = revertChanges ? originalText : $.trim(titleLabel.text().replace(/\s+/g, ' '));
					if (!nextText) {
						nextText = originalText || 'Panel Title';
					}

					titleLabel.text(nextText);
					titleLabel.removeAttr('contenteditable spellcheck');
					titleLabel.removeClass('is-editing');
					button.removeClass('is-active');
				}

				if (action === 'close') {
					if (panel.hasClass('box-typical-full-screen')) {
						panel.removeClass('box-typical-full-screen');
						syncFullscreenState();
					}
					panel.remove();
					return;
				}

				if (action === 'edit-title') {
					var activeLabel = $('.month-chart-header-label.is-editing').not(titleLabel).first();
					if (activeLabel.length) {
						activeLabel.trigger('blur');
					}

					if (!titleLabel.length) {
						return;
					}

					if (titleLabel.hasClass('is-editing')) {
						finishTitleEdit(false);
						return;
					}

					titleLabel.data('original-text', $.trim(titleLabel.text().replace(/\s+/g, ' ')));
					titleLabel.attr('contenteditable', 'true');
					titleLabel.attr('spellcheck', 'false');
					titleLabel.addClass('is-editing');
					button.addClass('is-active');

					setTimeout(function () {
						var node = titleLabel.get(0);
						if (!node) {
							return;
						}
						node.focus();
						if (window.getSelection && document.createRange) {
							var range = document.createRange();
							range.selectNodeContents(node);
							var selection = window.getSelection();
							selection.removeAllRanges();
							selection.addRange(range);
						}
					}, 0);
					return;
				}

				if (action === 'offset') {
					panel.toggleClass('dashboard-panel-offset');
					button.toggleClass('is-active', panel.hasClass('dashboard-panel-offset'));
					return;
				}

				if (action === 'refresh') {
					refreshDashboard(panel, button);
					return;
				}

				if (action === 'collapse') {
					if (!body.length) {
						return;
					}

					if (panel.hasClass('box-typical-collapsed')) {
						panel.removeClass('box-typical-collapsed');
						button.attr('aria-expanded', 'true');
						body.stop(true, true).show();
						$(window).trigger('resize');
					} else {
						button.attr('aria-expanded', 'false');
						body.stop(true, true).hide();
						panel.addClass('box-typical-collapsed');
						$(window).trigger('resize');
					}
					return;
				}

				if (action === 'fullscreen') {
					var wasFullScreen = panel.hasClass('box-typical-full-screen');
					$('.box-typical.box-typical-full-screen').not(panel).removeClass('box-typical-full-screen');
					$('.dashboard-panel-action[data-action="fullscreen"]').not(button).removeClass('is-active');
					panel.removeClass('dashboard-panel-offset');
					panel.find('.dashboard-panel-action[data-action="offset"]').removeClass('is-active');
					panel.toggleClass('box-typical-full-screen');
					button.toggleClass('is-active', !wasFullScreen);
					syncFullscreenState();
					setTimeout(function () {
						$(window).trigger('resize');
					}, 150);
				}
			});

			$(document).on('keydown', '.month-chart-header-label.is-editing', function (e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					$(this).trigger('blur');
					return;
				}

				if (e.key === 'Escape') {
					e.preventDefault();
					var label = $(this);
					label.text(label.data('original-text') || label.text());
					label.trigger('blur');
				}
			});

			$(document).on('blur', '.month-chart-header-label.is-editing', function () {
				var label = $(this);
				var panel = label.closest('.box-typical');
				var editButton = panel.find('.dashboard-panel-action[data-action="edit-title"]').first();
				var nextText = $.trim(label.text().replace(/\s+/g, ' '));
				var originalText = label.data('original-text') || '';

				if (!nextText) {
					nextText = originalText || 'Panel Title';
				}

				label.text(nextText);
				label.removeAttr('contenteditable spellcheck');
				label.removeClass('is-editing');
				editButton.removeClass('is-active');
			});

			var hasIncomeChart = !!document.getElementById('chart_div');
			var topPanels = $('#dashboard-top-panels');
			var incomeLayoutRedrawTimer = null;
			var scheduleChartDraw = window.requestAnimationFrame
				? window.requestAnimationFrame.bind(window)
				: function (callback) { return window.setTimeout(callback, 0); };

			function syncIncomeLayout() {
				if (!hasIncomeChart || !topPanels.length) {
					return;
				}

				topPanels.toggleClass('is-year-expanded', currentIncomeRange === 'year');
			}

			function redrawIncomeChartAfterLayout() {
				if (!hasIncomeChart) {
					return;
				}

				scheduleChartDraw(drawChart);

				if (incomeLayoutRedrawTimer) {
					window.clearTimeout(incomeLayoutRedrawTimer);
				}

				incomeLayoutRedrawTimer = window.setTimeout(function () {
					incomeLayoutRedrawTimer = null;
					drawChart();
				}, 320);
			}

			if (hasIncomeChart) {
				$('input[name="income-range"]').on('change', function () {
					currentIncomeRange = $(this).val();
					syncIncomeLayout();
					redrawIncomeChartAfterLayout();
				});

				// Reflow chart when menu toggles to avoid leftover blank space
				$('#show-hide-sidebar-toggle, .hamburger').on('click', function () {
					setTimeout(drawChart, 200);
				});

				syncIncomeLayout();

				if (window.google && google.charts) {
					google.charts.load('current', { packages: ['corechart'] });
					google.charts.setOnLoadCallback(drawChart);
				} else {
					showChartFallback();
				}
			}

			// Eye toggle for statistic boxes
			$('.stat-number').each(function () {
				var stat = $(this);
				var maskMode = stat.data('maskMode');
				stat.data('hidden', true);
				stat.text(maskMode === 'icon' ? '' : maskedValue);
			});
			$('.stat-eye').on('click', function (e) {
				e.preventDefault();
				var eye = $(this);
				var target = eye.data('target');
				var stat = $('.stat-number[data-target="' + target + '"]');
				var hidden = stat.data('hidden') !== false;
				var maskMode = stat.data('maskMode');
				stat.text(hidden ? stat.data('value') : (maskMode === 'icon' ? '' : maskedValue));
				stat.data('hidden', !hidden);
				if (maskMode === 'icon') {
					eye.toggleClass('is-revealed', hidden);
				}
				eye.find('i').toggleClass('fa-eye fa-eye-slash');
			});

			function toNumber(value) {
				var parsed = Number(value);
				return Number.isFinite(parsed) ? parsed : 0;
			}

			function formatAmount(value) {
				return toNumber(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
			}

			function formatCurrency(value) {
				return 'RS. ' + formatAmount(value);
			}

			function escapeHtml(value) {
				return $('<div />').text(value == null ? '' : String(value)).html();
			}

			function formatUpdatedAt(timestamp) {
				var parsedDate = timestamp ? new Date(timestamp) : null;
				if (!parsedDate || Number.isNaN(parsedDate.getTime())) {
					return 'just now';
				}

				return parsedDate.toLocaleString([], {
					day: '2-digit',
					month: 'short',
					hour: '2-digit',
					minute: '2-digit',
					second: '2-digit'
				});
			}

			function updateLiveStatus(text) {
				$('#dashboard-live-status').text(text);
			}

			function updateLastUpdated(timestamp) {
				$('#dashboard-last-updated')
					.attr('data-timestamp', timestamp || '')
					.text(formatUpdatedAt(timestamp));
			}

			function updateStatDisplay(stat) {
				var hidden = stat.data('hidden') !== false;
				var maskMode = stat.data('maskMode');
				stat.text(hidden ? (maskMode === 'icon' ? '' : maskedValue) : (stat.data('value') || '0'));
			}

			function setStatValue(key, value, format) {
				var stat = $('.stat-number[data-stat-key="' + key + '"]').first();
				if (!stat.length) {
					return;
				}

				var formattedValue = format === 'currency' ? formatCurrency(value) : formatAmount(value);
				stat.attr('data-value', formattedValue);
				stat.data('value', formattedValue);
				updateStatDisplay(stat);
			}

			function buildTicks(maxValue, segments) {
				var safeMax = Math.max(toNumber(maxValue), 10);
				var parts = segments || 5;
				var step = safeMax / parts;
				var ticks = [];
				for (var i = 0; i <= parts; i += 1) {
					ticks.push(Math.round(step * i));
				}
				return ticks;
			}

			function rangeTotal(range) {
				if (!range || !Array.isArray(range.points)) {
					return 0;
				}

				return range.points.reduce(function (sum, point) {
					return sum + toNumber(point[1]);
				}, 0);
			}

			function updateIncomeHeadline() {
				if (!document.querySelector('.chart-txt-top .number')) {
					return;
				}
				var range = incomeRanges[currentIncomeRange] || incomeRanges.today || { points: [] };
				$('.chart-txt-top .number').text(formatAmount(rangeTotal(range)));
			}

			function clearIncomeAxisOverlay() {
				$('#income-axis-top, #income-axis-right').empty().hide();
			}

			function renderIncomeAxisOverlay(chart, range) {
				var topAxis = $('#income-axis-top');
				var rightAxis = $('#income-axis-right');
				var points = Array.isArray(range.points) ? range.points : [];
				var ticks = (range.ticks || [0, 10]).map(toNumber).sort(function (a, b) {
					return a - b;
				});

				clearIncomeAxisOverlay();

				if (!chart || !chart.getChartLayoutInterface || !points.length) {
					return;
				}

				var layout = chart.getChartLayoutInterface();
				var chartArea = layout.getChartAreaBoundingBox();
				if (!chartArea || !chartArea.width || !chartArea.height) {
					return;
				}

				var xLabelTop = Math.max(6, chartArea.top - 16);
				var rightLabelLeft = chartArea.left + chartArea.width + 8;

				points.forEach(function (point, index) {
					var xPosition;
					if (points.length === 1) {
						xPosition = chartArea.left + (chartArea.width / 2);
					} else {
						xPosition = chartArea.left + ((chartArea.width * index) / (points.length - 1));
					}
					var labelText = String(point[0] || '').replace(/(\d)\s+([AP]M)$/i, '$1$2');

					$('<div/>', {
						'class': 'income-axis-label',
						text: labelText
					}).css({
						left: xPosition + 'px',
						top: xLabelTop + 'px'
					}).appendTo(topAxis);
				});

				ticks.forEach(function (tick) {
					var yPosition = layout.getYLocation(tick);
					if (!Number.isFinite(yPosition)) {
						return;
					}

					$('<div/>', {
						'class': 'income-axis-label',
						text: formatAmount(tick)
					}).css({
						left: rightLabelLeft + 'px',
						top: yPosition + 'px'
					}).appendTo(rightAxis);
				});

				topAxis.show();
				rightAxis.show();
			}

			function renderStats(payload) {
				var stats = (payload || {}).stats || {};
				setStatValue('todayLeads', stats.todayLeads || 0, 'number');
				setStatValue('currentStudents', stats.currentStudents || 0, 'number');
				setStatValue('currentMonthCollection', stats.currentMonthCollectionRaw || 0, 'currency');
				setStatValue('currentMonthPending', stats.currentMonthPending || 0, 'number');
			}

			function renderIncomeSummary(payload) {
				var summary = (payload || {}).incomeSummary || {};
				$('[data-income-summary="today"]').text(formatCurrency(summary.today || 0));
				$('[data-income-summary="week"]').text(formatCurrency(summary.week || 0));
				$('[data-income-summary="month"]').text(formatCurrency(summary.month || 0));
				updateIncomeHeadline();
			}

			function buildActivityRows(rows, emptyMessage, defaultStatus) {
				if (!Array.isArray(rows) || !rows.length) {
					return '<tr><td colspan="4" class="daily-empty-state">' + escapeHtml(emptyMessage) + '</td></tr>';
				}

				return rows.map(function (row) {
					var campusLine = row && row.show_campus ? '<div class="daily-student-campus">' + escapeHtml(row.campus || 'Campus') + '</div>' : '';
					return '<tr><td><span class="daily-status-badge daily-status-badge--' + escapeHtml(row.status_tone || 'primary') + '">' + escapeHtml(row.status_label || defaultStatus) + '</span></td><td><div class="daily-student-name">' + escapeHtml(row.student_name || 'N/A') + '</div>' + campusLine + '</td><td class="daily-phone">' + escapeHtml(row.phone || 'N/A') + '</td><td class="daily-date">' + escapeHtml(row.date_label || 'N/A') + '</td></tr>';
				}).join('');
			}

			function renderActivityTables(payload) {
				var dailyActivity = (payload || {}).dailyActivity || {};
				var admissionsActivity = (payload || {}).admissionsActivity || {};
				$('#recent-leads-body').html(buildActivityRows(dailyActivity.rows || [], 'No lead activity available.', 'New'));
				$('#recent-admissions-body').html(buildActivityRows(admissionsActivity.rows || [], 'No admissions available.', 'Enrolled'));
			}

			function normalizeComparisonSeries(series) {
				var categories = Array.isArray((series || {}).categories) ? series.categories : [];
				var counts = Array.isArray((series || {}).counts) ? series.counts : [];
				var normalized = categories.map(function (category, index) {
					return {
						label: $.trim(String(category || '')),
						value: toNumber(counts[index])
					};
				});

				var realItems = normalized.filter(function (item) {
					return item.label && item.label.toLowerCase() !== 'no data';
				});

				return realItems.length ? realItems : normalized;
			}

			function buildComparisonChartData() {
				var leadAllowed = !!dashboardAccessMap.training_leads;
				var admissionAllowed = !!dashboardAccessMap.admissions;
				var leadData = leadAllowed ? normalizeComparisonSeries(chartSeries.leads || {}) : [];
				var admissionData = admissionAllowed ? normalizeComparisonSeries(chartSeries.admissions || {}) : [];
				var categories = [];
				var leadMap = {};
				var admissionMap = {};
				var hasRealCategory = false;

				leadData.forEach(function (item) {
					var category = item.label || 'No Data';
					if (category.toLowerCase() !== 'no data') {
						hasRealCategory = true;
					}
					if (categories.indexOf(category) === -1) { categories.push(category); }
					leadMap[category] = item.value;
				});

				admissionData.forEach(function (item) {
					var category = item.label || 'No Data';
					if (category.toLowerCase() !== 'no data') {
						hasRealCategory = true;
					}
					if (categories.indexOf(category) === -1) { categories.push(category); }
					admissionMap[category] = item.value;
				});

				if (hasRealCategory) {
					categories = categories.filter(function (category) {
						return category.toLowerCase() !== 'no data';
					});
				}

				if (!categories.length) { categories = ['No Data']; }

				var leadValues = leadAllowed ? categories.map(function (category) { return leadMap[category] || 0; }) : [];
				var admissionValues = admissionAllowed ? categories.map(function (category) { return admissionMap[category] || 0; }) : [];
				var isEmpty = !hasRealCategory || (!leadValues.some(Boolean) && !admissionValues.some(Boolean));

				if ((!leadValues.some(Boolean) && !admissionValues.some(Boolean)) || (!leadAllowed && !admissionAllowed)) {
					categories = ['No Data'];
					leadValues = leadAllowed ? [0] : [];
					admissionValues = admissionAllowed ? [0] : [];
				}

				return {
					categories: categories,
					leadValues: leadValues,
					admissionValues: admissionValues,
					leadAllowed: leadAllowed,
					admissionAllowed: admissionAllowed,
					isEmpty: isEmpty || (!leadAllowed && !admissionAllowed)
				};
			}

			function renderMonthlyComparisonChart() {
				if (!(window.c3 && document.getElementById('lead-chart'))) { return; }

				var comparison = buildComparisonChartData();
				var chartElement = $('#lead-chart');
				var visibleValues = comparison.leadValues.concat(comparison.admissionValues).concat([0]);
				var tickValues = buildTicks(Math.max.apply(Math, visibleValues), 6);
				var chartColumns = [];
				var chartColors = {};

				if (comparison.leadAllowed) {
					chartColumns.push(['Leads'].concat(comparison.leadValues));
					chartColors.Leads = '#3b82f6';
				}

				if (comparison.admissionAllowed) {
					chartColumns.push(['Admissions'].concat(comparison.admissionValues));
					chartColors.Admissions = '#22c55e';
				}

				if (monthlyComparisonChart) { monthlyComparisonChart.destroy(); }
				chartElement.empty();

				if (comparison.isEmpty) {
					var emptyMessage = comparison.leadAllowed && comparison.admissionAllowed
						? 'No current month lead or admission data available.'
						: (comparison.leadAllowed ? 'No current month lead data available.' : 'No current month admission data available.');
					chartElement.html('<div class="comparison-empty-state">' + emptyMessage + '</div>');
					return;
				}

				monthlyComparisonChart = c3.generate({
					bindto: '#lead-chart',
					size: { height: 320 },
					data: {
						columns: chartColumns,
						type: 'bar',
						colors: chartColors,
						labels: {
							format: function (value) {
								return value > 0 ? String(value) : '';
							}
						}
					},
					legend: {
						show: true,
						position: 'right'
					},
					transition: { duration: 500 },
					tooltip: {
						grouped: true
					},
					axis: {
						x: {
							type: 'category',
							categories: comparison.categories,
							tick: {
								rotate: 0,
								multiline: false,
								outer: false
							},
							height: 52
						},
						y: {
							padding: { top: 12, bottom: 0 },
							min: 0,
							tick: {
								values: tickValues,
								format: function (value) {
									return Number(value).toLocaleString();
								}
							}
						}
					},
					bar: {
						width: {
							ratio: comparison.categories.length === 1 ? 0.28 : 0.48
						}
					},
					grid: { y: { show: true } },
					padding: { top: 6, right: 12, bottom: 0, left: 8 }
				});
			}

			function applyDashboardPayload(payload) {
				dashboardData = payload || {};
				incomeRanges = $.extend({}, defaultIncomeRanges, dashboardData.incomeRanges || {});
				chartSeries = dashboardData.charts || {};
				renderStats(dashboardData);
				renderIncomeSummary(dashboardData);
				renderActivityTables(dashboardData);
				renderMonthlyComparisonChart();
				updateLastUpdated(dashboardData.generatedAt || '');
				updateIncomeHeadline();
			}

			function refreshDashboard(panel, button) {
				if (refreshRequest) { return refreshRequest; }

				panel = panel || $();
				button = button || $();
				updateLiveStatus('Refreshing live data...');
				panel.addClass('panel-loading');
				button.addClass('is-spinning');

				refreshRequest = $.ajax({
					url: dashboardRefreshUrl,
					method: 'GET',
					dataType: 'json',
					cache: false,
					headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
				}).done(function (response) {
					applyDashboardPayload(response.dashboard || {});
					updateLiveStatus('Auto refresh every 15 seconds');
				}).fail(function () {
					updateLiveStatus('Live update failed. Retrying on next cycle.');
				}).always(function () {
					panel.removeClass('panel-loading');
					button.removeClass('is-spinning');
					refreshRequest = null;
					$(window).trigger('resize');
				});

				return refreshRequest;
			}

			applyDashboardPayload(dashboardData);
			$('body').addClass('dashboard-ready');

			$('#dashboard-live-refresh').on('click', function () {
				var btn = $(this);
				refreshDashboard($(), btn);
			});

			function drawChart() {
				if (!hasIncomeChart) {
					return;
				}

				syncIncomeLayout();

				if (!(
					window.google &&
					google.visualization &&
					typeof google.visualization.DataTable === 'function' &&
					typeof google.visualization.AreaChart === 'function'
				)) {
					showChartFallback();
					return;
				}

				var range = incomeRanges[currentIncomeRange] || incomeRanges.today;
				$('.chart-caption').text(range.label);
				updateIncomeHeadline();

				var dataTable = new google.visualization.DataTable();
				dataTable.addColumn('string', 'X');
				dataTable.addColumn('number', 'Values');
				dataTable.addColumn({ type: 'string', role: 'tooltip', p: { html: true } });
				dataTable.addRows(range.points.map(function (point) {
					var label = String(point[0] || '').replace(/ /g, '\u00A0');
					var amount = toNumber(point[1]);
					return [label, amount, point[0] + ': RS. ' + formatAmount(amount)];
				}));

				var options = {
					height: 314,
					legend: 'none',
					areaOpacity: 0.18,
					axisTitlesPosition: 'out',
					hAxis: {
						title: '',
						textStyle: {
							color: '#fff',
							fontName: 'Proxima Nova',
							fontSize: 11,
							bold: true,
							italic: false
						},
						textPosition: 'none',
						viewWindowMode: 'pretty'
					},
					vAxis: {
						minValue: 0,
						textPosition: 'none',
						textStyle: {
							color: '#fff',
							fontName: 'Proxima Nova',
							fontSize: 11,
							bold: true,
							italic: false
						},
						baselineColor: '#16b4fc',
						ticks: (range.ticks || [0, 10]).map(toNumber),
						gridlines: {
							color: '#1ba0fc',
							count: (range.ticks || []).length || 5
						}
					},
					lineWidth: 2,
					colors: ['#fff'],
					curveType: 'function',
					pointSize: 5,
					pointShapeType: 'circle',
					pointFillColor: '#f00',
					backgroundColor: {
						fill: '#008ffb',
						strokeWidth: 0
					},
					chartArea: {
						left: 20,
						right: 48,
						top: 36,
						bottom: 24,
						width: '100%',
						height: '100%'
					},
					fontSize: 11,
					fontName: 'Proxima Nova',
					tooltip: {
						trigger: 'selection',
						isHtml: true
					}
				};

				try {
					var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
					google.visualization.events.addListener(chart, 'ready', function () {
						renderIncomeAxisOverlay(chart, range);
					});
					$('#chart_fallback').hide();
					$('#chart_div').show();
					chart.draw(dataTable, options);
				} catch (err) {
					showChartFallback();
				}
			}

			window.setInterval(function () {
				if (!document.hidden) {
					refreshDashboard();
				}
			}, refreshIntervalMs);

			$(window).on('resize', function () {
				if (hasIncomeChart) {
					drawChart();
				}
				if (monthlyComparisonChart && typeof monthlyComparisonChart.flush === 'function') {
					monthlyComparisonChart.flush();
				}
			});
		});
	</script>
@endpush
