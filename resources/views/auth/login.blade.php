<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Career Institute - Sign In</title>

    <link href="{{ asset('theme/img/favicon.144x144.png') }}" rel="apple-touch-icon" type="image/png" sizes="144x144">
    <link href="{{ asset('theme/img/favicon.114x114.png') }}" rel="apple-touch-icon" type="image/png" sizes="114x114">
    <link href="{{ asset('theme/img/favicon.72x72.png') }}" rel="apple-touch-icon" type="image/png" sizes="72x72">
    <link href="{{ asset('theme/img/favicon.57x57.png') }}" rel="apple-touch-icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <link href="{{ asset('theme/img/favicon.ico') }}" rel="shortcut icon">

    <link rel="stylesheet" href="{{ asset('theme/separate/pages/login.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/lib/font-awesome/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/lib/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/main.css') }}">
    <style>
        html, body {
            height: 100%;
        }

        body {
            background: #f5f7fb;
            overflow-x: hidden;
        }

        .login-shell {
            position: relative;
            min-height: 100vh;
        }

        .login-media {
            
            
            min-height: 100vh;
            overflow: hidden;
            
}

        .media-glow {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(600px 300px at 20% 20%, rgba(0, 122, 255, 0.35), transparent 60%),
                radial-gradient(500px 300px at 80% 70%, rgba(0, 214, 155, 0.25), transparent 60%);
            pointer-events: none;
        }

        .login-slider {
            position: absolute;
            inset: 0;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
        }

        .slide.is-active {
            opacity: 1;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transform: scale(1.02);
            transition: transform 6s ease;
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(140deg, rgba(6, 16, 28, 0.35), rgba(6, 16, 28, 0.15));
        }

        .slider-caption {
            position: absolute;
            left: 40px;
            bottom: 40px;
            color: #fff;
            max-width: 420px;
        }

        .slider-caption h2 {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }

        .slider-caption p {
            font-size: 14px;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .slider-dots {
            position: absolute;
            right: 32px;
            bottom: 32px;
            display: flex;
            gap: 8px;
        }

        .slider-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
        }

        .slider-dot.is-active {
            background: #fff;
        }

        .login-form-panel {
            
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: end !important;
            padding: 0px;;
            pointer-events: none;
            border-radius:none !important;
        } 

        .sign-box {
    
    height: 100vh;
    background: rgba(255, 255, 255, 0.78);
    border: 0px;
    box-shadow: 0 18px 50px rgba(15, 30, 70, 0.2);
    border-radius: 16px;
    width: 100%;
    margin: 0 !important;
    max-width: 100%;
    padding: 24px 24px 30px;
    backdrop-filter: blur(10px);
    pointer-events: auto;
}
        .sign-box::before {
           display:none;
        }

        .sign-box .sign-avatar {
            margin-bottom: 8px;
            margin-top: 15%;
        }

        .sign-box .form-control {
            height: 44px;
            border-radius: 10px;
            border: 1px solid #dbe5f1;
            padding-left: 14px;
            margin-top:3%;
        }

        .sign-box .form-control:focus {
            border-color: #2b78ff;
            box-shadow: 0 0 0 3px rgba(43, 120, 255, 0.15);
        }

        .sign-box .btn.btn-rounded {
            width: 100%;
            margin-top: 8px;
            background: linear-gradient(120deg, #2b78ff, #00c2ff);
            border: 0;
            box-shadow: 0 12px 24px rgba(43, 120, 255, 0.3);
        }

        .sign-box .btn.btn-rounded:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(43, 120, 255, 0.35);
        }

        .sign-note {
            margin-top: 12px;
        }

        .sign-note.secondary {
            margin-top: 14px;
            font-size: 12px;
            color: #6b7a90;
        }

        .trust-row {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            font-size: 11px;
            color: #6b7a90;
        }

        .sign-box .checkbox label {
            color: #4a5b73;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .sign-box .checkbox label::before,
        .sign-box .checkbox label::after {
            display: none;
            content: none;
        }

        .sign-box .checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
            margin: 0;
            padding: 0;
            appearance: none;
            -webkit-appearance: none;
        }

        .sign-box .checkbox .custom-check {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 2px solid #2b78ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .sign-box .checkbox input[type="checkbox"]:checked + label .custom-check {
            background: #2b78ff;
            border-color: #2b78ff;
        }

        .sign-box .checkbox input[type="checkbox"]:checked + label .custom-check::after {
            content: "";
            width: 6px;
            height: 10px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            margin-top: -2px;
        }

        .slide.is-active img {
            transform: scale(1.07);
        }

        
    @media (max-width: 760px) {
        .login-media,
        .login-slider{
            height:50vh;
        }
         .login-form-panel {
            width:60%;
            height:80vh;
            position: absolute;
            top: 50%;
            left: 23%;
            
            
         }
         .sign-avatar{
            margin-top: 5px !important;
         }
         .keep-me{
            margin-top:1rem;
            margin-left: 0px;
         }
    }

    </style>
</head>
<body>
    <div class="login-shell row">
        <div class="login-media col-md-8">
            <div class="login-slider" id="loginSlider">
                <div class="slide is-active">
                    <img src="{{ asset('media/3.jpg') }}" alt="Career Institute">
                    <div class="slide-overlay" aria-hidden="true"></div>
                    <div class="slider-caption">
                        <h2>Build a future-ready career</h2>
                        <p>Hands-on training, industry mentors, and job-ready skills.</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="{{ asset('media/4.jpg') }}" alt="Career Institute">
                    <div class="slide-overlay" aria-hidden="true"></div>
                    <div class="slider-caption">
                        <h2>Learn from real projects</h2>
                        <p>Practice with live assignments and industry-grade tools.</p>
                    </div>
                </div>
                <div class="slider-dots">
                    <span class="slider-dot is-active"></span>
                    <span class="slider-dot"></span>
                </div>
            </div>
            <div class="media-glow" aria-hidden="true"></div>
           
        </div>
         <div class="login-form-panel col-md-4 ">
                <form class="sign-box" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="sign-avatar">
                        <img src="{{ asset('theme/img/Career-Institute-logo.webp') }}" alt="Career Institute Logo">
                    </div>
                    <header class="sign-title">Welcome back</header>
                    <div class="row">

                        <div class=" col-12">
                            <input type="email" name="email" class="form-control" style="width: 100% !important;" placeholder="E-Mail" value="{{ old('email') }}" required>
                        </div>
                        <div class=" col-12">
                            <input type="password" name="password" class="form-control" style="width: 100% !important;" placeholder="Password" required>
                        </div>
                    </div>
                <div class="form-group keep-me">
                    <div class="checkbox float-left mt-3">
                        <input type="checkbox" id="signed-in" name="remember">
                        <label for="signed-in">
                            <span class="custom-check" aria-hidden="true"></span>
                            Keep me signed in
                        </label>
                    </div>
                    <div class="float-right reset mt-3">
                        <a href="#">Reset Password</a>
                    </div>
                </div>
                    <button type="submit" class="btn btn-rounded mt-3">Sign in</button>
                    @if($errors->any())
                        <div class="sign-note secondary">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="trust-row mt-2">
                        <span>Flexible timings</span>
                        <span>Industry mentors</span>
                        <span>Career support</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('theme/js/lib/jquery/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('theme/js/lib/popper/popper.min.js') }}"></script>
    <script src="{{ asset('theme/js/lib/tether/tether.min.js') }}"></script>
    <script src="{{ asset('theme/js/lib/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('theme/js/plugins.js') }}"></script>
    <script src="{{ asset('theme/js/app.js') }}"></script>
    <script>
        (function () {
            var slider = document.getElementById('loginSlider');
            if (!slider) {
                return;
            }

            var slides = slider.querySelectorAll('.slide');
            var dots = slider.querySelectorAll('.slider-dot');
            var index = 0;

            var showSlide = function (nextIndex) {
                slides[index].classList.remove('is-active');
                dots[index].classList.remove('is-active');
                index = nextIndex;
                slides[index].classList.add('is-active');
                dots[index].classList.add('is-active');
            };

            setInterval(function () {
                showSlide((index + 1) % slides.length);
            }, 5000);
        })();
    </script>
</body>
</html>
