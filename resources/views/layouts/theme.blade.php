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
	.side-menu-list {
		margin:-18px 0 20px;
	}
.side-menu-list .lbl{
			font-size:13px !important;
			font-weight:600 !important;
		}
	.side-menu .stage-count {
		min-width: 32px;
		width:80px;
		text-align: center;
		color: #fff;
		background-color: #6c757d;
		border-color: #6c757d;
	}
	.side-menu-list a,
.side-menu-list li>span {
    padding: 6px 5px 6px 46px;
}
 * {
    font-family: 'Proxima Nova', sans-serif;
    font-size: 12px !important;
    margin: 0;
    padding: 0;
    
}
.table {
    width: 98%;
    max-width: 198%;
   
    margin-left: 8px;
}
.box-typical.box-typical-dashboard .box-typical-body {
    overflow: hidden;
}
.box-typical.box-typical-dashboard{
    margin:0px 0px 5px !important;
    
}
.box-typical.box-typical-dashboard .box-typical-header{
    display:flex;

}
.form-row>.col, .form-row>[class*=col-] {
    padding-right: 3px;
    padding-left: 5px;
}
   .follow-action-dropdown {
    position: relative;

}

.follow-action-dropdown .dropdown-menu {
	font-size:12px !important;
	min-width: 180px;
    position: absolute;  
    top:-33px !important;          
    left: -73px !important;
    z-index: 9999;       
} 
	.action-cell {
			min-width: 110px;
			white-space: nowrap;
			position: relative;
		}

		.registration-action-dropdown .dropdown-menu {
	min-width: 180px;
    position: absolute;  
    top:-40px !important;          
    left: -73px !important;
    z-index: 9999;  
			
		}
.select2-container--arrow .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #343434;
    padding: .375rem 25px .375rem 1rem;
    min-height: 32px;
    background: #fff
}
.form-label{
    font-size: 11px;
    font-weight: 600 ;
    color: #343434;
    text-transform: uppercase;
    margin-bottom: 3px;
    
}

body, button, html, input, select, textarea {
    color: #343434;
    height: 32px;
    font-family: 'Proxima Nova', sans-serif;
    line-height: 1.4;
    text-rendering: optimizeLegibility;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    -o-font-smoothing: antialiased;
}

      @media (min-width: 781px) {

  .col-md-3 {
    flex: 0 0 200px ;
    max-width: 144px ;
    height: 62px;
    margin-bottom: 2px;
  }
      .col-md-8 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 66.666667%;
        flex: 0 0 66.666667%;
        max-width: 44.666667%;
    }

  .col-md-2 {
    flex: 0 0 14.666667% ;
    max-width: 14.996667% ;
  }

  .col-md-5 {
    flex: 0 0 250px ;
    max-width: 250px ;
    height: 62px;
    margin-bottom: 2px;
  }

  .col-md-4 {
    flex: 0 0 33.333333% ;
    max-width: 25.333333%;
    margin-top: 10px;
  }
  .form-row{
	display: flex;        /* ensure flex active ho */
    flex-wrap: wrap; 
		gap:18px;
		padding-left:15px;
		
	}

}
@media (max-width: 780px) {
	.col-md-1,
  .col-md-2,
  .col-md-3,
  .col-md-4,
  .col-md-5,
  .col-md-8 {
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }

  .form-row{
    display:block;   
    padding-left:15px;
	margin-right:2px;
  }
  .teaching-method{
	  /* margin-left:50px !important; */
	}
	.form-radio{
	  flex-direction:column;
	  align-items: flex-start !important; 

  }
  .gender{
	margin-right:0px !important;
  }
  .leave-button
  {
	margin:0 !important;
	
  } 
	..payrol-button{
		
	}
 
}
.box-typical .panel-heading {
    padding: 7px 20px;
}
.bootstrap-table .table td, .fixed-table-body .table td, .table td {
    height: 36px;
}
.mb-3, .my-3 {
    margin-bottom: 0rem !important;
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
	.with-side-menu .side-menu { width: 280; }
	.with-side-menu .page-content { margin-left: 210px; margin-top:-25px; font-family: 'Proxima Nova', sans-serif; }

	@media (max-width: 1199px) {
		.with-side-menu .side-menu { width: 260; }
		.with-side-menu .page-content { margin-left: 260px; }
	}

	/* Mobile sidebar */
	@media (max-width: 991px) {
		.with-side-menu .side-menu { width: 260; }
		.with-side-menu .page-content { margin-left: 0; }

		.side-menu .lbl {
			font-size: 12px;
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
		padding-left: 7px;
		padding-right: 28px;
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
			padding-left: 7px;
			padding-right: 12px;
		}
	}

	.dataTables_wrapper .table-responsive {
		overflow-x: hidden;
	}

	.dataTables_wrapper table {
		width: 100%;
	}
	.table-responsive{
		text-align:center;
	}
input[type="checkbox"] {
  /* Remove default style */
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 13px;       /* normal size */
  height: 13px;      /* normal size */
  border: 2px solid grey;
  border-radius: 3px;
  background-color: white;
  cursor: pointer;
  position: relative;
}

/* Checked state */
input[type="checkbox"]:checked {
  background-color: #00a8ff;
  border-color: #00a8ff;
}

/* Tick */
input[type="checkbox"]:checked::after {
  content: "";
  position: absolute;
  left: 3px;
  top: 1px;
  width: 4px;
  height: 8px;
  border: solid white ;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
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
		<script>
$(document).ready(function () {

    $('#show-hide-sidebar-toggle, .hamburger').on('click', function (e) {
        e.preventDefault();

        $('body').toggleClass('sidebar-hidden');
    });

});
</script>
		
	@endif
	@stack('scripts')
	
</body>

</html>
