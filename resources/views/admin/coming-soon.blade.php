@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    <div class="lead-status-shell coming-soon-shell">
        <section class="box-typical box-typical-dashboard panel panel-default coming-soon-card">
            <div class="box-typical-body panel-body">
                <div class="coming-soon-copy">
                    <h1>{{ $pageTitle }}</h1>
                    <p>This page is coming soon.</p>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .coming-soon-shell {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .coming-soon-card {
            width: min(720px, 100%);
            margin: 0 auto;
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(7, 43, 77, 0.12);
        }

        .coming-soon-card .box-typical-body {
            padding: 56px 42px;
            background:
                radial-gradient(circle at top right, rgba(18, 160, 255, 0.16), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f3f8fc 100%);
        }

        .coming-soon-copy {
            text-align: center;
        }

        .coming-soon-copy h1 {
            margin: 0 0 10px;
            color: #14324a;
            font-size: clamp(1.875rem, 4vw, 2.375rem) !important;
            font-weight: 700;
            line-height: 1.1;
        }

        .coming-soon-copy p {
            margin: 0;
            color: #587084;
            font-size: 1.125rem !important;
        }

        @media (max-width: 767px) {
            .coming-soon-card .box-typical-body {
                padding: 40px 24px;
            }

            .coming-soon-copy h1 {
                font-size: clamp(1.875rem, 4vw, 2.375rem) !important;
            }

            .coming-soon-copy p {
                font-size: 1rem !important;
            }
        }
    </style>
@endpush
