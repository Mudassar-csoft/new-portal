@extends('layouts.theme')

@section('title', 'Dashboard')

@section('content')
	@php
		$stats = $dashboard['stats'] ?? [];
		$incomeSummary = $dashboard['incomeSummary'] ?? [];
		$dailyActivity = $dashboard['dailyActivity'] ?? [];
		$dailyRows = $dailyActivity['rows'] ?? [];
		$dailyTotals = $dailyActivity['totals'] ?? [
			'leads' => 0,
			'followups' => 0,
			'admissions' => 0,
			'collection' => 0,
		];
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

		<div id="dashboard-content " class="dashboard-content bg-white">
		<div class="row align-middle">

		<div id="dashboard-content" class="dashboard-content">
		<div class="row"style=" align-items: flex-start !important;">

			<div class="col-xl-6 pl-0 ">
				<div class="chart-statistic-box">
					
					<div class="chart-container row ">
						<div class="chart-txt col-5 p-0 m-0 ">
						<div class="chart-txt-top pt-3">
							<p ><span class="unit"style="font-size:18px !important;">RS.</span><span class="number"style="font-size:18px !important;">{{ number_format((float) ($incomeSummary['today'] ?? 0), 0) }}</span></p>
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
						<table class="tbl-data ml-lg-3 ml-2">
							<tr>
								<td class="price color-purple" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['today'] ?? 0), 0) }}</td>
								<td style = "font-size:14px;	">Today Collection</td>
							</tr>
							<tr>
								<td class="price color-yellow" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['week'] ?? 0), 0) }}</td>
								<td style = "font-size:14px;	">Weekly Collection</td>
							</tr>
							<tr>
								<td class="price color-lime" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['month'] ?? 0), 0) }}</td>
								<td style = "font-size:14px;	">Monthly Collection</td>
							</tr>
						</table>
					</div>
						<div class="chart-container-in col-7  m-0 p-0 fs-1">
							<div id="chart_div" style = "font-size:10px;"></div>
							<div id="chart_fallback" style="display:none; height:314px;">
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
							<!-- <div class="chart-caption"></div> -->
							<div class="chart-container-x"></div>
							<div class="chart-container-y"></div>
						</div>
					</div>
					
				</div><!--.chart-statistic-box-->
			</div>
			<div class="col-xl-6 pr-0">
				<div class="row">
					<div class="col-sm-6">
						<article class="statistic-box red">
							<div class="stat-inner">
								<button class="stat-eye" data-target="stat-1" aria-label="Show total leads"><i class="fa fa-eye"></i></button>
								<div class="number stat-number fs-2xl" data-value="{{ number_format((int) ($stats['totalLeads'] ?? 0)) }}" data-target="stat-1">***</div>
								<div class="caption">
									<div class="text">Total Leads</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					<div class="col-sm-6">
						<article class="statistic-box purple">
							<div class="stat-inner">
								<button class="stat-eye" data-target="stat-2" aria-label="Show current students"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="{{ number_format((int) ($stats['currentStudents'] ?? 0)) }}" data-target="stat-2">***</div>
								<div class="caption">
									<div class="text">Current Students</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					<div class="col-sm-6">
						<article class="statistic-box yellow">
							<div class="stat-inner">
								<button class="stat-eye" data-target="stat-3" aria-label="Show current month collection"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="RS. {{ $stats['currentMonthCollection'] ?? '0' }}" data-target="stat-3">***</div>
								<div class="caption">
									<div class="text">Current Month Collection</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					<div class="col-sm-6">
						<article class="statistic-box green">
							<div class="stat-inner">
								<button class="stat-eye" data-target="stat-4" aria-label="Show current month pending"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="{{ number_format((int) ($stats['currentMonthPending'] ?? 0)) }}" data-target="stat-4">***</div>
								<div class="caption">
									<div class="text">Current Month Pending</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
				</div><!--.row-->
			</div><!--.col-->
		
		
	    </div>
<!--Current Month Charts-->
	<div class="row ">
		<div class="col-xl-6">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">Current Month Leads</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="dashboard-panel-action" data-action="fullscreen" aria-label="Maximize current month leads chart">
								<i class="fa fa-window-maximize"></i>
							</button>
							<button type="button" class="dashboard-panel-action" data-action="close" aria-label="Close current month leads chart">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<div id="leads-chart"></div>
				</div>
			</section>
		</div>
		<div class="col-xl-6">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">Current Month Admissions</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="dashboard-panel-action" data-action="fullscreen" aria-label="Maximize current month admissions chart">
								<i class="fa fa-window-maximize"></i>
							</button>
							<button type="button" class="dashboard-panel-action" data-action="close" aria-label="Close current month admissions chart">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<div id="admissions-chart"></div>
				</div>
			</section>
		</div>
	</div>
<!--Daily Activity-->
	<div class="row dashboard-equal-row">
		<div class="col-xl-6 d-flex">
			<section class="box-typical box-typical-dashboard panel panel-default daily-activity-card dashboard-equal-card">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">Daily Activity <span class="color-blue-grey">|</span> {{ isset($selectedCampus) && $selectedCampus ? (($selectedCampus->code ?: 'Campus') . ' - ' . $selectedCampus->name) : 'Each Campus' }}</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="dashboard-panel-action" data-action="fullscreen" aria-label="Maximize daily activity">
								<i class="fa fa-window-maximize"></i>
							</button>
							<button type="button" class="dashboard-panel-action" data-action="close" aria-label="Close daily activity">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<table class="tbl-typical">
						<thead>
							<tr>
								<th style="width: 20%;"><div>Campus</div></th>
								<th><div>Leads</div></th>
								<th><div>Followups</div></th>
								<th><div>Admissions</div></th>
								<th><div>Collection</div></th>
							</tr>
						</thead>
						<tbody>
							@forelse($dailyRows as $row)
								<tr>
									<td><span class="badge badge-pill badge-warning daily-campus">{{ $row['campus'] }}</span></td>
									<td>{{ number_format((int) ($row['leads'] ?? 0)) }}</td>
									<td>{{ number_format((int) ($row['followups'] ?? 0)) }}</td>
									<td>{{ number_format((int) ($row['admissions'] ?? 0)) }}</td>
									<td>RS. {{ number_format((float) ($row['collection'] ?? 0), 0) }}</td>
								</tr>
							@empty
								<tr>
									<td><span class="badge badge-pill badge-warning daily-campus">N/A</span></td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>RS. 0</td>
								</tr>
							@endforelse
							<tr class="daily-activity-total">
								<td><strong>Total</strong></td>
								<td>{{ number_format((int) ($dailyTotals['leads'] ?? 0)) }}</td>
								<td>{{ number_format((int) ($dailyTotals['followups'] ?? 0)) }}</td>
								<td>{{ number_format((int) ($dailyTotals['admissions'] ?? 0)) }}</td>
								<td>RS. {{ number_format((float) ($dailyTotals['collection'] ?? 0), 0) }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</section>
		</div>
	<!--campus Month Charts-->
		<div class="col-xl-6 d-flex">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card dashboard-equal-card">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">{{ isset($selectedCampus) && $selectedCampus ? 'Admissions Overview' : 'Campus Admissions Comparison' }}</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="dashboard-panel-action" data-action="fullscreen" aria-label="Maximize campus admissions comparison">
								<i class="fa fa-window-maximize"></i>
							</button>
							<button type="button" class="dashboard-panel-action" data-action="close" aria-label="Close campus admissions comparison">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<div id="campus-admissions-chart"></div>
				</div>
			</section>
		</div>
	</div>
	</div>
	</div>
@endsection

@push('styles')
	<!-- <link rel="stylesheet" href="css/lib/lobipanel/lobipanel.min.css"> -->
	<link rel="stylesheet" href="css/separate/vendor/lobipanel.min.css">
	<link rel="stylesheet" href="css/lib/jqueryui/jquery-ui.min.css">
	<link rel="stylesheet" href="css/separate/pages/widgets.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css">
	<style>
		*{
			font-size: 15px !important;	
		}
		.box-typical.box-typical-dashboard {
			margin: 1% 5px !important;
			height: 318px;
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
			border-radius: 12px;
			overflow: hidden;
		}
		.chart-statistic-box .chart-container {
			margin-left: 0;
			border-radius: 12px;
			overflow: hidden;
		}
		.chart-statistic-box .chart-txt {
			background: #304b58;
			border-radius: 12px 0 0 12px;
			overflow: hidden;
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

        .chart-caption {
            text-align: center;
            font-weight: 700;
            color: #fff;
            margin-top: 8px;
            padding-bottom: 6px;
        }
 .statistic-box {
    padding: 2px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: relative;
	    text-align: center;
    color: #fff;
    background: no-repeat 50% 50%;
    background-size: cover;
	    height: 150px;
		margin: 0 0 15px;
		margin-top:0px;

    
}
        .statistic-box .stat-inner {
            position: relative;
        }
.statistic-box .number, 
.statistic-box .caption{
    font-size:32px !important;

}
*{
	font: size 15px ;
}
.text{
	font-size:14px;
}


        .statistic-box .stat-eye {
            position: absolute;
            right: 10px;
            top: 10px;
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
        }

        .statistic-box .stat-eye:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        .month-chart-card .panel-heading {
            padding: 6px 12px;
        }
        .month-chart-header {
            padding: 10px 14px !important;
            border-bottom: 1px solid #e6eef3;
            background: linear-gradient(135deg, #f8fbfd 0%, #eef6fb 100%);
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
            gap: 8px;
            flex-shrink: 0;
            margin-left: auto;
            justify-content: flex-end;
        }
        .month-chart-header-title {
            margin: 0;
            font-size: 16px !important;
            font-weight: 600 !important;
            color: #304b58;
        }
        .dashboard-panel-action {
            align-items: center;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 50%;
            background: #eef4f8;
            color: #5b6b79;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .dashboard-panel-action:hover {
            background: #00a8ff;
            color: #fff;
        }
        .month-chart-card {
            margin-bottom: 15px;
        }

        .month-chart-card .box-typical-body,
        .month-chart-card .panel-body {
            max-height: none !important;
            height: auto;
            overflow: hidden !important;
            padding: 10px 14px;
        }
        .dashboard-equal-row > [class*="col-"] {
            display: flex;
        }
        .dashboard-equal-card {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        .dashboard-equal-card .panel-body,
        .dashboard-equal-card .box-typical-body {
            flex: 1 1 auto;
        }
        .daily-activity-card .panel-body {
            min-height: 320px;
            overflow: visible !important;
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

        .daily-activity-card .panel-heading {
            border-top: 0;
        }
        .daily-activity-card .panel-body {
            padding: 10px 14px;
        }
        .daily-activity-card .tbl-typical {
            width: 100%;
            margin-bottom: 0;
            table-layout: auto;
        }
        .daily-activity-card .tbl-typical th,
        .daily-activity-card .tbl-typical td {
            font-size: 13px !important;
            line-height: 1.45;
            vertical-align: middle;
            color: #304b58;
            width: auto !important;
        }
        .daily-activity-card .tbl-typical th {
            font-weight: 700;
            text-align: left;
            white-space: normal;
        }
        .tbl-typical th > div {
            padding: 6px 10px !important;
        }
        .daily-activity-card .tbl-typical td:not(:first-child),
        .daily-activity-card .tbl-typical th:not(:first-child) {
            text-align: center;
        }
        .daily-campus {
            background: transparent;
            color: #304b58;
            font-weight: 600;
            padding: 0;
            border: 0;
            border-radius: 0;
        }

        .daily-activity-total td {
            background: #f8fbfd;
            color: #304b58;
            font-weight: 700;
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
		var incomeRanges = @json($dashboard['incomeRanges'] ?? []);
		var chartSeries = @json($dashboard['charts'] ?? []);
		var defaultIncomeRanges = {
			today: { label: 'Today income (hourly)', points: [['08 AM', 0]], ticks: [0, 10] },
			week: { label: 'Week income (daily)', points: [['Mon', 0]], ticks: [0, 10] },
			month: { label: 'Month income (weekly)', points: [['Week 1', 0]], ticks: [0, 10] },
			year: { label: 'Year income (monthly)', points: [['Jan', 0]], ticks: [0, 10] }
		};
		incomeRanges = Object.assign({}, defaultIncomeRanges, incomeRanges || {});

		var currentIncomeRange = 'today';

			$(document).ready(function () {
			var maskedValue = '***';
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
				$('#chart_div').hide();
				$('#chart_fallback').show();
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

				if (!panel.length) {
					return;
				}

				if (action === 'close') {
					panel.remove();
					return;
				}

				if (action === 'fullscreen') {
					panel.toggleClass('box-typical-full-screen');
					setTimeout(function () {
						$(window).trigger('resize');
					}, 150);
				}
			});

			$('input[name="income-range"]').on('change', function () {
				currentIncomeRange = $(this).val();
				drawChart();
			});
			// Reflow chart when menu toggles to avoid leftover blank space
			$('#show-hide-sidebar-toggle, .hamburger').on('click', function () {
				setTimeout(drawChart, 200);
			});

			if (window.google && google.charts) {
				google.charts.load('current', { packages: ['corechart'] });
				google.charts.setOnLoadCallback(drawChart);
			} else {
				showChartFallback();
			}

			// Eye toggle for statistic boxes
			$('.stat-number').text(maskedValue);
			$('.stat-eye').on('click', function (e) {
				e.preventDefault();
				var target = $(this).data('target');
				var stat = $('.stat-number[data-target="' + target + '"]');
				var hidden = stat.text() === maskedValue;
				stat.text(hidden ? stat.data('value') : maskedValue);
				$(this).find('i').toggleClass('fa-eye fa-eye-slash');
			});

			function toNumber(value) {
				var parsed = Number(value);
				return Number.isFinite(parsed) ? parsed : 0;
			}

			function formatAmount(value) {
				return toNumber(value).toLocaleString();
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
				var range = incomeRanges[currentIncomeRange] || incomeRanges.today || { points: [] };
				$('.chart-txt-top .number').text(formatAmount(rangeTotal(range)));
			}

			var leadCodes = (chartSeries.leads && chartSeries.leads.categories) ? chartSeries.leads.categories : ['No Data'];
			var leadCounts = (chartSeries.leads && chartSeries.leads.counts) ? chartSeries.leads.counts.map(toNumber) : [0];
			var admissionPrograms = (chartSeries.admissions && chartSeries.admissions.categories) ? chartSeries.admissions.categories : ['No Data'];
			var admissionCounts = (chartSeries.admissions && chartSeries.admissions.counts) ? chartSeries.admissions.counts.map(toNumber) : [0];
			var campusCodes = (chartSeries.campusAdmissions && chartSeries.campusAdmissions.categories) ? chartSeries.campusAdmissions.categories : ['No Data'];
			var campusAdmissions = (chartSeries.campusAdmissions && chartSeries.campusAdmissions.counts) ? chartSeries.campusAdmissions.counts.map(toNumber) : [0];
			var leadTicks = buildTicks(Math.max.apply(Math, leadCounts.concat([0])), 6);
			var admissionTicks = buildTicks(Math.max.apply(Math, admissionCounts.concat([0])), 6);
			var campusTicks = buildTicks(Math.max.apply(Math, campusAdmissions.concat([0])), 5);
			var campusMax = Math.max.apply(Math, campusAdmissions);

			updateIncomeHeadline();
			// Reveal content once charts/data ready
			$('body').addClass('dashboard-ready');

			c3.generate({
				bindto: '#leads-chart',
				size: { height: 250 },
				data: {
					columns: [['Leads'].concat(leadCounts)],
					type: 'area-spline',
					colors: { Leads: '#3b82f6' }
				},
				transition: {
					duration: 800
				},
				axis: {
					x: {
						type: 'category',
						categories: leadCodes,
						tick: {
							rotate: 0,
							multiline: false
						},
						label: 'Course Codes',
						height: 40
					},
					y: {
						label: 'Number of Leads',
						padding: { top: 10, bottom: 0 },
						min: 0,
						tick: {
							values: leadTicks
						}
					}
				},
				bar: {
					width: {
						ratio: 0.6
					}
				},
				legend: { show: false },
				grid: { y: { show: true } },
				padding: { right: 20 }
			});

			c3.generate({
				bindto: '#admissions-chart',
				size: { height: 250 },
				data: {
					columns: [['Admissions'].concat(admissionCounts)],
					type: 'area-spline',
					colors: { Admissions: '#22c55e' }
				},
				transition: {
					duration: 800
				},
				axis: {
					x: {
						type: 'category',
						categories: admissionPrograms,
						tick: {
							rotate: 0,
							multiline: false
						},
						label: 'Programs',
						height: 40
					},
					y: {
						label: 'Number of Admissions',
						padding: { top: 10, bottom: 0 },
						min: 0,
						tick: {
							values: admissionTicks
						}
					}
				},
				bar: {
					width: {
						ratio: 0.6
					}
				},
				legend: { show: false },
				grid: { y: { show: true } },
				padding: { right: 20 }
			});

			c3.generate({
				bindto: '#campus-admissions-chart',
				size: { height: 250 },
				data: {
					columns: [['Admissions'].concat(campusAdmissions)],
					type: 'bar',
					colors: { Admissions: '#12a0ff' },
					color: function (color, d) {
						if (d && d.index !== undefined && campusAdmissions[d.index] === campusMax) {
							return '#3b82f6';
						}
						return color;
					}
				},
				transition: { duration: 800 },
				axis: {
					x: {
						type: 'category',
						categories: campusCodes,
						label: 'Campuses',
						tick: { rotate: 0, multiline: false },
						height: 40
					},
					y: {
						label: 'Admissions this month',
						min: 0,
						padding: { top: 10, bottom: 0 },
						tick: { values: campusTicks }
					}
				},
				bar: { width: { ratio: 0.55 } },
				legend: { show: false },
				grid: { y: { show: true } },
				padding: { right: 20 }
			});

			function drawChart() {
				if (!(window.google && google.visualization)) {
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
					var amount = toNumber(point[1]);
					return [point[0], amount, point[0] + ': RS. ' + formatAmount(amount)];
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
						textPosition: 'out',
						slantedText: true,
						slantedTextAngle: 45,
						viewWindowMode: 'pretty'
					},
					vAxis: {
						minValue: 0,
						textPosition: 'out',
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
						left: 50,
						right: 20,
						top: 20,
						bottom: 50,
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
					chart.draw(dataTable, options);
				} catch (err) {
					showChartFallback();
				}
			}

			$(window).resize(function () {
				drawChart();
				setTimeout(function () { }, 1000);
			});
		});
	</script>
@endpush
