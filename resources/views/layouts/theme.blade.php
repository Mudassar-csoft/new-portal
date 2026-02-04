<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>@yield('title', 'StartUI - Premium Bootstrap 4 Admin Dashboard Template')</title>

	<base href="{{ asset('theme') }}/">

	<link href="img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
	<link href="img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
	<link href="img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
	<link href="img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
	<link href="img/favicon.png" rel="icon" type="image/png">
	<link href="img/favicon.ico" rel="shortcut icon">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" href="css/lib/font-awesome/font-awesome.min.css">
	<link rel="stylesheet" href="css/lib/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">
	<style>
	/* Keep stage labels and count pills on one line in the side menu */
	.side-menu .stage-link {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
	}

	.side-menu .stage-count {
		min-width: 32px;
		text-align: center;
		color: #fff;
		background-color: #6c757d;
		border-color: #6c757d;
	}

	/* Module-specific pill tones */
	.side-menu .brown .stage-count { background-color: #c77d16; border-color: #c77d16; }
	.side-menu .purple .stage-count { background-color: #6f42c1; border-color: #6f42c1; }
	.side-menu .orange .stage-count { background-color: #ff9800; border-color: #ff9800; }
	.side-menu .magenta .stage-count { background-color: #e83e8c; border-color: #e83e8c; }
	.side-menu .blue .stage-count { background-color: #0088cc; border-color: #0088cc; }
	.side-menu .green .stage-count { background-color: #28a745; border-color: #28a745; }
	.side-menu .orange-red .stage-count { background-color: #ff5722; border-color: #ff5722; }
	.side-menu .teal .stage-count { background-color: #00897b; border-color: #00897b; }
	.side-menu .gold .stage-count { background-color: #d4a017; border-color: #d4a017; }

	/* Sidebar sizing */
	.with-side-menu .side-menu { width: 280px; }
	.with-side-menu .page-content { margin-left: 280px; }

	@media (max-width: 1199px) {
		.with-side-menu .side-menu { width: 260px; }
		.with-side-menu .page-content { margin-left: 260px; }
	}

	/* Mobile sidebar */
	@media (max-width: 991px) {
		.with-side-menu .side-menu { width: 260px; }
		.with-side-menu .page-content { margin-left: 0; }

		.side-menu .lbl {
			font-size: 14px;
			line-height: 1.3;
		}

		.side-menu-list>li>span,
		.side-menu-list>li>a {
			padding-right: 12px;
		}
	}

	/* Page content spacing */
	.with-side-menu .page-content {
		padding: 100px 32px 32px;
	}

	/* 🔥 UPDATED: container spacing fix */
	.with-side-menu .page-content > .container-fluid {
		max-width: 1440px;
		margin: 0 auto;
		padding-left: 16px;
		padding-right: 16px;
	}

	/* 🔥 UPDATED: proper gap between cards */
	.row {
		margin-left: -8px;
		margin-right: -8px;
	}

	.row > [class*="col-"] {
		padding-left: 8px;
		padding-right: 8px;
	}

	/* Sidebar hidden */
	.menu-left-hidden .page-content {
		margin-left: 0 !important;
		padding-left: 32px;
		padding-right: 32px;
	}

	.menu-left-hidden .page-content > .container-fluid {
		max-width: 100%;
		padding-left: 16px;
		padding-right: 16px;
	}

	body.sidebar-hidden .side-menu {
		left: -280px;
	}

	body.sidebar-hidden .page-content {
		margin-left: 0 !important;
		padding-left: 32px;
		padding-right: 32px;
	}

	body.sidebar-hidden .page-content > .container-fluid {
		max-width: 100%;
		padding-left: 16px;
		padding-right: 16px;
	}

	.site-header .user-greeting {
		margin-right: 10px;
		color: #6c7a89;
		font-size: 14px;
		display: inline-flex;
		align-items: center;
	}

	.welcome-swal .sweet-alert p {
		color: #28a745;
		font-size: 13px;
		font-weight: 600;
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

	.radio-group.is-invalid,
	.gender-options.is-invalid {
		border: 1px solid #e53935;
		border-radius: 6px;
		padding: 6px 10px;
	}

	@media (max-width: 991px) {
		.with-side-menu .page-content {
			padding: 110px 24px 24px;
		}

		.with-side-menu .page-content > .container-fluid {
			padding-left: 12px;
			padding-right: 12px;
		}
	}

	.dataTables_wrapper .table-responsive {
		overflow-x: hidden;
	}

	.dataTables_wrapper table {
		width: 100%;
	}
</style>

	@stack('styles')
</head>

<body class="with-side-menu control-panel control-panel-compact">

	@include('layouts.header')
	@include('layouts.nav')

	<div class="page-content">
		<div class="container-fluid">
			@yield('content')
		</div><!--.container-fluid-->
	</div><!--.page-content-->

	@include('layouts.taskbar')

	<script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
	<script src="js/lib/popper/popper.min.js"></script>
	<script src="js/lib/tether/tether.min.js"></script>
	<script src="js/lib/bootstrap/bootstrap.min.js"></script>
	<script src="js/plugins.js"></script>
	<script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<script src="js/app.js"></script>
	@if(session('welcome'))
		<script>
			(function () {
				if (!window.swal) return;
				var name = @json(session('welcome'));
				swal({
					title: 'Welcome back',
					text: name,
					type: 'success',
					customClass: 'welcome-swal',
					timer: 2000,
					showConfirmButton: false
				});
			})();
		</script>
	@endif
	@if(session('error'))
		<script>
			(function () {
				if (!window.swal) return;
				swal({
					title: 'Error',
					text: @json(session('error')),
					type: 'error'
				});
			})();
		</script>
	@endif
	@stack('scripts')
</body>

</html>
