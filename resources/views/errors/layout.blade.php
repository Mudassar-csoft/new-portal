<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Career Institute - @yield('title')</title>

    <link href="{{ asset('theme/img/favicon.144x144.png') }}" rel="apple-touch-icon" type="image/png" sizes="144x144">
    <link href="{{ asset('theme/img/favicon.114x114.png') }}" rel="apple-touch-icon" type="image/png" sizes="114x114">
    <link href="{{ asset('theme/img/favicon.72x72.png') }}" rel="apple-touch-icon" type="image/png" sizes="72x72">
    <link href="{{ asset('theme/img/favicon.57x57.png') }}" rel="apple-touch-icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.ico') }}" rel="shortcut icon">

    <link rel="stylesheet" href="{{ asset('theme/lib/font-awesome/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/lib/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/main.css') }}">

    <style>
        :root {
            --brand-blue: #0a7fe8;
            --brand-cyan: #20b7ff;
            --ink: #1f2a44;
            --muted: #68748a;
            --surface: rgba(255, 255, 255, 0.88);
            --line: rgba(10, 127, 232, 0.12);
            --shadow: 0 24px 70px rgba(16, 38, 84, 0.18);
            --error-font-xs: 12px;
            --error-line-tight: 1.15;
            --error-weight-bold: 700;
            --error-weight-heavy: 800;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Proxima Nova', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(520px 320px at 12% 18%, rgba(32, 183, 255, 0.24), transparent 68%),
                radial-gradient(640px 360px at 88% 82%, rgba(10, 127, 232, 0.16), transparent 70%),
                linear-gradient(145deg, #eef6ff 0%, #f8fbff 52%, #eef3fb 100%);
            overflow-x: hidden;
        }

        .error-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
        }

        .error-panel {
            width: 100%;
            max-width: 1040px;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 28px;
            box-shadow: var(--shadow);
            overflow: hidden;
            backdrop-filter: blur(16px);
        }

        .error-grid {
            display: grid;
            grid-template-columns: minmax(280px, 0.95fr) minmax(320px, 1.05fr);
        }

        .error-visual {
            position: relative;
            padding: 44px 36px;
            background:
                linear-gradient(165deg, rgba(10, 127, 232, 0.96), rgba(32, 183, 255, 0.82)),
                #0a7fe8;
            color: #fff;
            isolation: isolate;
        }

        .error-visual::before,
        .error-visual::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            z-index: -1;
        }

        .error-visual::before {
            width: 260px;
            height: 260px;
            right: -90px;
            top: -70px;
            background: rgba(255, 255, 255, 0.12);
        }

        .error-visual::after {
            width: 200px;
            height: 200px;
            left: -70px;
            bottom: -90px;
            background: rgba(255, 255, 255, 0.08);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
        }

        .brand-mark img {
            width: 46px;
            height: 46px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.16);
            border-radius: 14px;
            padding: 7px;
        }

        .brand-label {
            font-size: 18px;
            font-weight: var(--error-weight-bold);
            line-height: var(--error-line-tight);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            font-size: var(--error-font-xs);
            font-weight: var(--error-weight-bold);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .error-code {
            margin: 24px 0 10px;
            font-size: 96px;
            line-height: 0.95;
            font-weight: var(--error-weight-heavy);
            letter-spacing: -0.04em;
        }

        .error-caption {
            max-width: 340px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
        }

        .error-content {
            padding: 48px 42px;
        }

        .error-kicker {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(10, 127, 232, 0.08);
            color: var(--brand-blue);
            font-size: var(--error-font-xs);
            font-weight: var(--error-weight-bold);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .error-title {
            margin: 0;
            font-size: 36px;
            line-height: var(--error-line-tight);
            font-weight: var(--error-weight-heavy);
            color: var(--ink);
        }

        .error-message {
            margin: 16px 0 0;
            font-size: 16px;
            line-height: 1.8;
            color: var(--muted);
            max-width: 520px;
        }

        .error-meta {
            margin-top: 26px;
            padding: 18px 20px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--line);
        }

        .error-meta-label {
            font-size: var(--error-font-xs);
            font-weight: var(--error-weight-bold);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--brand-blue);
            margin-bottom: 8px;
        }

        .error-meta-value {
            margin: 0;
            color: var(--ink);
            font-size: 14px;
            word-break: break-word;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .error-actions .btn {
            min-width: 150px;
            height: 44px;
            border-radius: 12px;
            font-weight: var(--error-weight-bold);
            padding: 10px 18px;
        }

        .btn-primary-solid {
            color: #fff;
            background: linear-gradient(120deg, #0a7fe8, #20b7ff);
            border: 0;
            box-shadow: 0 16px 30px rgba(10, 127, 232, 0.22);
        }

        .btn-primary-solid:hover,
        .btn-primary-solid:focus {
            color: #fff;
            opacity: 0.96;
        }

        .btn-outline-soft {
            border: 1px solid rgba(10, 127, 232, 0.2);
            background: rgba(10, 127, 232, 0.04);
            color: var(--brand-blue);
        }

        .btn-outline-soft:hover,
        .btn-outline-soft:focus {
            color: var(--brand-blue);
            background: rgba(10, 127, 232, 0.08);
        }

        .error-help {
            margin-top: 26px;
            font-size: 13px;
            color: var(--muted);
        }

        .error-help strong {
            color: var(--ink);
        }

        @media (max-width: 991px) {
            .error-grid {
                grid-template-columns: 1fr;
            }

            .error-visual,
            .error-content {
                padding: 36px 24px;
            }

            .error-code {
                font-size: 78px;
            }

            .error-title {
                font-size: 30px;
            }
        }

        @media (max-width: 575px) {
            .error-shell {
                padding: 16px;
            }

            .error-panel {
                border-radius: 22px;
            }

            .error-code {
                font-size: 66px;
            }

            .error-title {
                font-size: 26px;
            }

            .error-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="error-shell">
        <section class="error-panel">
            <div class="error-grid">
                <div class="error-visual">
                    <div class="brand-mark">
                        <img src="{{ asset('theme/img/Career-Institute-logo.webp') }}" alt="Career Institute">
                        <div class="brand-label">Career<br>Institute</div>
                    </div>

                    <div class="status-pill">
                        <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                        HTTP Error
                    </div>

                    <div class="error-code">@yield('code')</div>
                    <p class="error-caption">@yield('caption')</p>
                </div>

                <div class="error-content">
                    <div class="error-kicker">@yield('title')</div>
                    <h1 class="error-title">@yield('headline')</h1>
                    <p class="error-message">@yield('message')</p>

                    @hasSection('meta')
                        <div class="error-meta">
                            @yield('meta')
                        </div>
                    @endif

                    <div class="error-actions">
                        @yield('actions')
                    </div>

                    <p class="error-help">
                        <strong>Need help?</strong> If this keeps happening, contact the CRM administrator and mention error code <strong>@yield('code')</strong>.
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
