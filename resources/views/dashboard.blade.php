@extends('layouts.theme')

@section('title', 'Dashboard')

@section('content')
	<div class="dashboard-shell">
		<div id="dashboard-loader" class="dashboard-loader">
			<div class="dashboard-spinner">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p>Loading dashboard...</p>
		</div>
		<div id="dashboard-content" class="dashboard-content">
	<div class="row">
		<div class="col-xl-6">
			<div class="chart-statistic-box">
				<div class="chart-txt">
					<div class="chart-txt-top">
						<p><span class="unit">RS.</span><span class="number">1540</span></p>
						<p class="caption">Income</p>
					</div>
					<div class="chart-range">
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
							<td class="price color-purple">RS. 120</td>
							<td>Income</td>
						</tr>
						<tr>
							<td class="price color-yellow">RS. 15</td>
							<td>Expenses</td>
						</tr>
						<tr>
							<td class="price color-lime">RS. 55</td>
							<td>Others</td>
						</tr>
					</table>
				</div>
				<div class="chart-container">
					
					<div class="chart-container-in">
						<div id="chart_div"></div>
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
						<div class="chart-container-x"></div>
						<div class="chart-container-y"></div>
					</div>
				</div>
				
			</div><!--.chart-statistic-box-->
		</div>
		<div class="col-xl-6">
			<div class="row">
				<div class="col-sm-6">
					<article class="statistic-box red">
						<div class="stat-inner">
							<button class="stat-eye" data-target="stat-1"><i class="fa fa-eye"></i></button>
							<div class="number stat-number" data-value="26" data-target="stat-1">***</div>
							<div class="caption">
								<div>Total Leads</div>
							</div>
						</div>
					</article>
				</div><!--.col-->
				<div class="col-sm-6">
					<article class="statistic-box purple">
						<div class="stat-inner">
							<button class="stat-eye" data-target="stat-2"><i class="fa fa-eye"></i></button>
							<div class="number stat-number" data-value="12" data-target="stat-2">***</div>
							<div class="caption">
								<div>Current Students</div>
							</div>
						</div>
					</article>
				</div><!--.col-->
				<div class="col-sm-6">
					<article class="statistic-box yellow">
						<div class="stat-inner">
							<button class="stat-eye" data-target="stat-3"><i class="fa fa-eye"></i></button>
							<div class="number stat-number" data-value="104" data-target="stat-3">***</div>
							<div class="caption">
								<div>Current Month Collection</div>
							</div>
						</div>
					</article>
				</div><!--.col-->
				<div class="col-sm-6">
					<article class="statistic-box green">
						<div class="stat-inner">
							<button class="stat-eye" data-target="stat-4"><i class="fa fa-eye"></i></button>
							<div class="number stat-number" data-value="29" data-target="stat-4">***</div>
							<div class="caption">
								<div>Current Month Pending</div>
							</div>
						</div>
					</article>
				</div><!--.col-->
			</div><!--.row-->
		</div><!--.col-->
	
		<!--.col-->
	</div>
<!--Current Month Charts-->
	<div class="row">
		<div class="col-xl-6">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card">
				<header class="box-typical-header panel-heading">
					<div class="tbl">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title">
								<h3 class="panel-title">Current Month Leads</h3>
							</div>
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
				<header class="box-typical-header panel-heading" style="padding: 0px;">
					<div class="tbl">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title">
								<h3 class="panel-title">Current Month Admissions</h3>
							</div>
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
	<div class="row">
		<div class="col-xl-12">
			<section class="box-typical box-typical-dashboard panel panel-default daily-activity-card">
				<header class="box-typical-header panel-heading" style="padding: 0px;">
					<div class="tbl">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title">
								<h3 class="panel-title">Daily Activity <span class="color-blue-grey">|</span> Each Campus</h3>
							</div>
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
							<tr>
								<td><span class="badge badge-pill badge-warning daily-campus">CILHR02</span></td>
								<td>0</td>
								<td>0</td>
								<td>0</td>
								<td>0</td>
							</tr>
							<tr>
								<td><span class="badge badge-pill badge-warning daily-campus">CIFSD06</span></td>
								<td>1</td>
								<td>0</td>
								<td>0</td>
								<td>0</td>
							</tr>
							<tr>
								<td><span class="badge badge-pill badge-warning daily-campus">CIHOO1</span></td>
								<td>0</td>
								<td>0</td>
								<td>0</td>
								<td>0</td>
							</tr>
							<tr>
								<td><span class="badge badge-pill badge-warning daily-campus">CIJHG01</span></td>
								<td>2</td>
								<td>0</td>
								<td>0</td>
								<td>34500</td>
							</tr>
							<tr class="daily-activity-total">
								<td><strong>Total</strong></td>
								<td>4</td>
								<td>1</td>
								<td>0</td>
								<td>39500</td>
							</tr>
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</div>
	<!--campus Month Charts-->
	<div class="row">
		<div class="col-xl-12">
			<section class="box-typical box-typical-dashboard panel panel-default month-chart-card">
				<header class="box-typical-header panel-heading">
					<div class="tbl">
						<div class="tbl-row">
							<div class="tbl-cell tbl-cell-title">
								<h3 class="panel-title">Campus Admissions Comparison</h3>
							</div>
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
		.chart-range {
			display: flex;
			flex-wrap: wrap;
			gap: 8px 12px;
			margin: 8px 0 12px;
		}

		.chart-range .radio {
			margin: 0;
		}

        .chart-range .radio input {
            margin-top: 2px;
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
 
        .statistic-box .stat-inner {
            position: relative;
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

        .month-chart-card .box-typical-body,
        .month-chart-card .panel-body {
            max-height: none !important;
            height: auto;
            overflow: hidden !important;
            padding: 10px 14px;
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
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.dashboard-ready .dashboard-content {
            opacity: 1;
            visibility: visible;
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
            border-top: 4px solid #f9a825;
        }

        .daily-campus {
            background: #f9a825;
            color: #fff;
            font-weight: 700;
            padding: 6px 12px;
            font-size: 12px;
        }

        .daily-activity-total td {
            background: #f9a825;
            color: #fff;
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
		var incomeRanges = {
			today: {
				label: 'Today income (hourly)',
				points: [
					['08 AM', 80],
					['10 AM', 120],
					['12 PM', 160],
					['02 PM', 140],
					['04 PM', 200],
					['06 PM', 220],
					['08 PM', 180],
					['10 PM', 160]
				],
				ticks: [0, 50, 100, 150, 200, 250]
			},
			week: {
				label: 'Week income (daily)',
				points: [
					['Mon', 130],
					['Tue', 150],
					['Wed', 200],
					['Thu', 180],
					['Fri', 230],
					['Sat', 170],
					['Sun', 210]
				],
				ticks: [0, 50, 100, 150, 200, 250, 300]
			},
			month: {
				label: 'Month income (weekly)',
				points: [
					['Week 1', 320],
					['Week 2', 360],
					['Week 3', 340],
					['Week 4', 390]
				],
				ticks: [0, 100, 200, 300, 400, 500]
			},
			year: {
				label: 'Year income (monthly)',
				points: [
					['Jan', 240],
					['Feb', 260],
					['Mar', 280],
					['Apr', 300],
					['May', 320],
					['Jun', 310],
					['Jul', 350],
					['Aug', 340],
					['Sep', 360],
					['Oct', 380],
					['Nov', 370],
					['Dec', 400]
				],
				ticks: [0, 100, 200, 300, 400, 500]
			}
		};

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

			// Current month charts (fake data)
			var leadCodes = ['DMS', 'GRD', 'OTH', 'OMT', 'PHP', 'AVA', 'IEA', 'SPE', 'LAR', 'SHY'];
			var leadCounts = [70, 30, 8, 15, 1, 14, 12, 9, 8, 10];

			var admissionPrograms = ['OMT', 'GRD', 'PHP', 'MER', 'QUK', 'DMS', 'SPE', 'IEA', 'AVA', 'ITE', 'SHY', 'AIG', 'CYF', 'LAR', 'ADM'];
			var admissionCounts = [10, 2, 2, 1, 6, 1, 4, 3, 1, 1, 1, 1, 1, 2, 1];
			var campusCodes = ['CIFSD01', 'CIFSD02', 'CIFSD03', 'CILHR01', 'CILHR02'];
			var campusAdmissions = [12, 9, 15, 7, 11];
			var campusMax = Math.max.apply(Math, campusAdmissions);
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
							values: [0, 10, 20, 30, 40, 50, 60, 70, 80]
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
							values: [0, 2, 4, 6, 8, 10, 12]
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
						tick: { values: [0, 5, 10, 15, 20] }
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

				var dataTable = new google.visualization.DataTable();
				dataTable.addColumn('string', 'X');
				dataTable.addColumn('number', 'Values');
				dataTable.addColumn({ type: 'string', role: 'tooltip', p: { html: true } });
				dataTable.addRows(range.points.map(function (point) {
					return [point[0], point[1], point[0] + ': RS. ' + point[1]];
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
						ticks: range.ticks || [0, 50, 100, 150, 200, 250, 300],
						gridlines: {
							color: '#1ba0fc',
							count: (range.ticks || []).length || 10
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
