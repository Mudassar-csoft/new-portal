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
            overflow: hidden;
        }

        .login-shell {
            display: flex;
            min-height: 100vh;
        }

        .login-media {
            position: relative;
            width: 70%;
            overflow: hidden;
            background: #0b1622;
        }

        .login-media-grid {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-media-tile {
            position: relative;
            overflow: hidden;
        }

        .login-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            object-position: center;
        }

        .login-overlay {
            position: absolute;
            inset: 0;
            background: rgba(6, 16, 28, 0.2);
        }

        .login-form-panel {
            width: 30%;
            min-width: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #ffffff;
        }

        .sign-box {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
        }

        .sound-toggle {
            position: absolute;
            right: 24px;
            bottom: 24px;
            background: #0a69d6;
            color: #fff;
            border: 0;
            padding: 10px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            z-index: 2;
        }

        .sound-toggle.is-hidden {
            display: none;
        }

        @media (max-width: 991px) {
            .login-shell {
                flex-direction: column;
            }

            .login-media,
            .login-form-panel {
                width: 100%;
            }

            .login-media {
                min-height: 45vh;
            }

            .login-form-panel {
                min-height: 55vh;
            }
        }

        @media (max-width: 768px) {
            .sound-toggle {
                right: 12px;
                bottom: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-media">
            <div class="login-media-grid">
                  <div class="login-media-tile">
                    <video class="login-video" autoplay loop playsinline muted>
                        <source src="{{ asset('media/IMG_1498.MP4') }}" type="video/mp4">
                    </video>
                    <div class="login-overlay" aria-hidden="true"></div>
                </div>
                <div class="login-media-tile">
                    <video class="login-video" autoplay loop playsinline muted>
                        <source src="{{ asset('media/IMG_1497.MP4') }}" type="video/mp4">
                    </video>
                    <div class="login-overlay" aria-hidden="true"></div>
                </div>
              
            </div>
            <button id="toggleSound" class="sound-toggle" type="button">Enable sound</button>
        </div>

        <div class="login-form-panel">
            <form class="sign-box" onsubmit="return false;">
                <div class="sign-avatar">
                    <img src="{{ asset('theme/img/Career-Institute-logo.webp') }}" alt="Career Institute Logo">
                </div>
                {{-- <header class="sign-title">S</header> --}}
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="E-Mail or Phone">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password">
                </div>
                <div class="form-group">
                    <div class="checkbox float-left">
                        <input type="checkbox" id="signed-in">
                        <label for="signed-in">Keep me signed in</label>
                    </div>
                    <div class="float-right reset">
                        <a href="#">Reset Password</a>
                    </div>
                </div>
                <button type="submit" class="btn btn-rounded">Sign in</button>
                <p class="sign-note">New to our website? <a href="#">Sign up</a></p>
            </form>
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
            var videos = document.querySelectorAll('.login-video');
            var toggle = document.getElementById('toggleSound');

            if (!videos.length || !toggle) {
                return;
            }

            var tryPlay = function () {
                videos.forEach(function (item) {
                    var playAttempt = item.play();
                    if (playAttempt && typeof playAttempt.catch === 'function') {
                        playAttempt.catch(function () {});
                    }
                });
            };

            toggle.addEventListener('click', function () {
                videos.forEach(function (item) {
                    item.muted = false;
                    item.volume = 1;
                });
                tryPlay();
                toggle.classList.add('is-hidden');
            });

            document.addEventListener('click', function () {
                if (!toggle.classList.contains('is-hidden')) {
                    videos.forEach(function (item) {
                        item.muted = false;
                        item.volume = 1;
                    });
                    tryPlay();
                    toggle.classList.add('is-hidden');
                }
            }, { once: true });

            tryPlay();
        })();
    </script>
</body>
</html>
