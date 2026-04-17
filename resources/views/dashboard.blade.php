@extends('layouts.theme')

@section('title', 'Dashboard')

@section('content')
	@php
		$stats = $dashboard['stats'] ?? [];
		$incomeSummary = $dashboard['incomeSummary'] ?? [];
		$dailyActivity = $dashboard['dailyActivity'] ?? [];
		$admissionsActivity = $dashboard['admissionsActivity'] ?? [];
		$dailyRows = $dailyActivity['rows'] ?? [];
		$admissionRows = $admissionsActivity['rows'] ?? [];
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
		<div class="row pl-3 pr-3">

			<div class="col-xl-6 pl-0 ml-3 mr-2 m-md-0 m-lg-0 ">
				<div class="chart-statistic-box">
					
					<div class="chart-container row ">
						<div class="chart-txt col-md-5 p-0 m-0 ">
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
							<table class="tbl-data">
								<tr>
									<td class="collection-label pl-lg-3 pl-2" style = "font-size:14px;	">Today Collection</td>
									<td class="price color-purple collection-amount" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['today'] ?? 0), 0) }}</td>
								</tr>
								<tr>
									<td class="collection-label pl-lg-3 pl-2 " style = "font-size:14px;	">Weekly Collection</td>
									<td class="price color-yellow collection-amount" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['week'] ?? 0), 0) }}</td>
								</tr>
								<tr>
									<td class="collection-label pl-lg-3 pl-2" style = "font-size:14px;	">Monthly Collection</td>
									<td class="price color-lime collection-amount" style = "font-size:14px;	">RS. {{ number_format((float) ($incomeSummary['month'] ?? 0), 0) }}</td>
								</tr>
							</table>
						</div>
						<div class="chart-container-in col-md-7  m-0 p-0 fs-1">
							<div class="pr-md-2">
								<div class="income-chart-stage">
									<div id="chart_div" ></div>
									<div id="chart_fallback" style="display:none; height:314px; style = "font-size:11px;"">
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
								<!-- <div class="chart-caption"></div> -->
								<div class="chart-container-x"></div>
								<div class="chart-container-y"></div>
							</div>
						</div>
					</div>
					
				</div><!--.chart-statistic-box-->
			</div>
			<div class="col-xl-6 pr-4">
				<div class="row">
					<div class="col-md-6 ">
						<article class="statistic-box red"  >
							<div class="stat-inner">
								<button class="stat-eye stat-eye-inline" data-target="stat-1" aria-label="Show total leads"><i class="fa fa-eye"></i></button>
								<div class="number stat-number fs-2xl" data-value="{{ number_format((int) ($stats['totalLeads'] ?? 0)) }}" data-target="stat-1" data-mask-mode="icon"></div>
								<div class="caption mt-3">
									<div class="caption-text">Today Leads</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					<div class="col-md-6 ">
						<article class="statistic-box purple mr-1"  >
							<div class="stat-inner">
								<button class="stat-eye stat-eye-inline" data-target="stat-2" aria-label="Show current students"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="{{ number_format((int) ($stats['currentStudents'] ?? 0)) }}" data-target="stat-2" data-mask-mode="icon"></div>
								<div class="caption mt-3">
									<div class="caption-text">Current Students</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					<div class="col-md-6 ">
						<article class="statistic-box yellow">
							<div class="stat-inner">
								<button class="stat-eye stat-eye-inline" data-target="stat-3" aria-label="Show current month collection"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="RS. {{ $stats['currentMonthCollection'] ?? '0' }}" data-target="stat-3" data-mask-mode="icon"></div>
								<div class="caption mt-3">
									<div class="caption-text">{{ now()->format('F') }} Collection</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
					<div class="col-md-6 ">
						<article class="statistic-box green mr-1">
							<div class="stat-inner m">
								<button class="stat-eye stat-eye-inline" data-target="stat-4" aria-label="Show current month pending"><i class="fa fa-eye"></i></button>
								<div class="number stat-number" data-value="{{ number_format((int) ($stats['currentMonthPending'] ?? 0)) }}" data-target="stat-4" data-mask-mode="icon"></div>
								<div class="caption mt-3 ">
									<div class="caption-text">Pending Recovery</div>
								</div>
							</div>
						</article>
					</div><!--.col-->
				</div><!--.row-->
			</div><!--.col-->
		
		
	    </div>
<!--Current Month Charts-->
	<div class="row pl-4 pr-3 tables-dashbord">
		<div class="col-xl-12 pl-1 ml-1 mr-2 m-md-0 m-lg-0 current-month-chart-col">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card  bg-gray-300 ">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">Current Month Leads & Admission</span>
							</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="action-btn dashboard-panel-action" data-action="edit-title" aria-label="Edit current month leads title">
								<i class="font-icon glyphicon glyphicon-pencil"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="offset" aria-label="Move current month leads panel slightly">
								<i class="glyphicon glyphicon-move"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh current month leads chart">
								<i class="font-icon font-icon-refresh"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse current month leads chart" aria-expanded="true">
								<i class="font-icon font-icon-minus"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize current month leads chart">
								<i class="font-icon font-icon-expand"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="close" aria-label="Close current month leads chart">
								<i class="font-icon font-icon-close"></i>
							</button>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<div id="lead-chart"></div>
				</div>
			</section>
		</div>

	</div>

<!--Daily Activity-->
	<div class="row dashboard-equal-row pl-4 pr-3 mt-4  tables-dashbord ">
		<div class="col-xl-6 d-flex pl-1 ml-1 mr-2 m-md-0 m-lg-0">
			<section class="box-typical box-typical-dashboard panel panel-default daily-activity-card dashboard-equal-card bg-gray-300">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">Recent Leads</span>
							</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="action-btn dashboard-panel-action" data-action="edit-title" aria-label="Edit daily activity title">
								<i class="font-icon glyphicon glyphicon-pencil"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="offset" aria-label="Move daily activity panel slightly">
								<i class="glyphicon glyphicon-move"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh daily activity">
								<i class="font-icon font-icon-refresh"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse daily activity" aria-expanded="true">
								<i class="font-icon font-icon-minus"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize daily activity">
								<i class="font-icon font-icon-expand"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="close" aria-label="Close daily activity">
								<i class="font-icon font-icon-close"></i>
							</button>
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
						<tbody>
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
									<td colspan="4" class="daily-empty-state">No lead activity found for today.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</section>
		</div>
	<!--campus Month Charts-->
		<div class="col-xl-6 d-flex pl-2 pr-4">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card dashboard-equal-card  bg-gray-300">
				<header class="box-typical-header panel-heading month-chart-header">
					<div class="month-chart-header-content">
						<div class="month-chart-header-wrap">
							<h3 class="form-label-dashboard month-chart-header-title">
								<span class="month-chart-header-label">Recent Admissions</span>
							</h3>
						</div>
						<div class="month-chart-header-actions">
							<button type="button" class="action-btn dashboard-panel-action" data-action="edit-title" aria-label="Edit campus admissions comparison title">
								<i class="font-icon glyphicon glyphicon-pencil"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="offset" aria-label="Move campus admissions comparison panel slightly">
								<i class="glyphicon glyphicon-move"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh campus admissions comparison">
								<i class="font-icon font-icon-refresh"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse campus admissions comparison" aria-expanded="true">
								<i class="font-icon font-icon-minus"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize campus admissions comparison">
								<i class="font-icon font-icon-expand"></i>
							</button>
							<button type="button" class="action-btn dashboard-panel-action" data-action="close" aria-label="Close campus admissions comparison">
								<i class="font-icon font-icon-close"></i>
							</button>
						</div>
					</div>
				</header>
				<div class="box-typical-body panel-body">
					<table class="tbl-typical daily-activity-table admission-activity-table">
						<thead>
							<tr>
								<th><div>Status</div></th>
								<th><div>Student Name</div></th>
								<th><div>Phone Number</div></th>
								<th><div>Date</div></th>
							</tr>
						</thead>
						<tbody>
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
									<td colspan="4" class="daily-empty-state">No admissions found for today.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</div>
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

			font-size: 15px !important;	
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
			font-size: 12px !important;
		}

		#lead-chart .c3-legend-item:last-child {
			transform: translateX(12px);
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

        /* .statistic-box .stat-eye-inline {
            left: auto;
            right: 12px;
            top: 12px;
            transform: none;
            width: 32px;
            height: 32px;
        } */

		.statistic-box .stat-eye-inline {
            left: 50%;
            right: auto;
            top: 22px;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
        }

        .statistic-box .stat-eye-inline.is-revealed {
            left: auto;
            right: 12px;
            top: 12px;
            transform: none;
            width: 32px;
            height: 32px;
        }

        .stat-number[data-mask-mode="icon"] {
            min-height: 44px;
        }

        .month-chart-card .panel-heading {
            padding: 6px 12px;
        }
        .month-chart-header {
            padding: 14px 18px !important;
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
            font-size: 17px !important;
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
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
            margin-top: 5px;
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

        .month-chart-card .box-typical-body,
        .month-chart-card .panel-body {
            max-height: none !important;
            height: auto;
            overflow: hidden !important;
            padding: 10px 14px;
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

        .daily-activity-card .panel-heading {
            border-top: 0;
        }
        .current-month-chart-col {
            flex: 0 0 calc(100% - 8px);
            max-width: calc(100% - 8px);
        }
        .daily-activity-card .panel-body {
            padding: 10px 14px;
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
        .daily-activity-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .daily-status-badge {
            display: inline-block;
            min-width: 88px;
            padding: 6px 14px;
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
            font-size: 15px;
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
            font-size: 15px !important;
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
					button.addClass('is-spinning');
					panel.addClass('panel-loading');
					setTimeout(function () {
						panel.removeClass('panel-loading');
						button.removeClass('is-spinning');
						$(window).trigger('resize');
					}, 450);
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
        bindto: '#lead-chart',
        data: {
            columns: [
                ['Lead', 50, 200, 100, 400, 150, 250,100, 400, 150, 250],
                ['Admission', 130, 100, 140, 200, 150, 50, 140, 200, 150, 50]
            ],
            type: 'bar'
        },
        bar: {
            width: {
                ratio: 0.5
            }
        }
    });

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

			if (document.getElementById('campus-admissions-chart')) {
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
			}

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

			$(window).resize(function () {
				drawChart();
				setTimeout(function () { }, 1000);
			});
		});
	</script>
@endpush
