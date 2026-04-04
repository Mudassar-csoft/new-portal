<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', 'Career Institute')</title>

    <base href="{{ asset('theme') }}/">

    <link href="{{ asset('theme/img/favicon.144x144.png') }}" rel="apple-touch-icon" type="image/png" sizes="144x144">
    <link href="{{ asset('theme/img/favicon.114x114.png') }}" rel="apple-touch-icon" type="image/png" sizes="114x114">
    <link href="{{ asset('theme/img/favicon.72x72.png') }}" rel="apple-touch-icon" type="image/png" sizes="72x72">
    <link href="{{ asset('theme/img/favicon.57x57.png') }}" rel="apple-touch-icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.ico') }}" rel="shortcut icon">

    <link rel="stylesheet" href="css/lib/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="lib/bootstrap-sweetalert/sweetalert.css">

    <style>
        html,
        body {
            min-height: 100%;
        }

        body.crm-embed-body {
            margin: 0;
            background: linear-gradient(180deg, #f3f8fd 0%, #f8fbff 100%);
            color: #24364d;
            font-family: 'Proxima Nova', sans-serif;
        }

        body.crm-embed-body * {
            box-sizing: border-box;
        }

        body.crm-embed-body .box-typical,
        body.crm-embed-body .panel {
            margin-bottom: 0;
        }
    </style>

    @stack('styles')
</head>
<body class="crm-embed-body">
    @yield('content')

    <script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
    <script src="js/lib/popper/popper.min.js"></script>
    <script src="js/lib/tether/tether.min.js"></script>
    <script src="js/lib/bootstrap/bootstrap.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/lib/bootstrap-sweetalert/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="js/app.js"></script>

    @stack('scripts')
</body>
</html>
