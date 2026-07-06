<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Institute - Forgot Password</title>
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <style>
        :root {
            --dimension-auth-forgot-password-1: 100%;
            --space-auth-forgot-password-1: 12px;
            --space-auth-forgot-password-2: 16px;
            --space-auth-forgot-password-3: 6px;
            --color-auth-forgot-password-1: #fff;
            --typo-auth-forgot-password-font-size-1: 13px;
            --typo-auth-forgot-password-font-size-2: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; background: linear-gradient(135deg, #0a3d62, #0f766e); }
        .container { width: 460px; max-width: var(--dimension-auth-forgot-password-1); background: linear-gradient(135deg, #00b894, #00cec9); color: var(--color-auth-forgot-password-1); padding: 32px 36px; border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .logo { display: flex; justify-content: center; margin-bottom: var(--space-auth-forgot-password-1); }
        .logo img { height: 58px; max-width: var(--dimension-auth-forgot-password-1); object-fit: contain; }
        h2 { text-align: center; font-size: 1.375rem; margin: 8px 0 18px; }
        p.subtitle { text-align: center; font-size: var(--typo-auth-forgot-password-font-size-1); margin-bottom: 22px; opacity: 0.9; }
        .input-group { margin-bottom: var(--space-auth-forgot-password-2); }
        .input-group label { display: block; font-size: var(--typo-auth-forgot-password-font-size-1); margin-bottom: var(--space-auth-forgot-password-3); }
        .input-group input { width: var(--dimension-auth-forgot-password-1); padding: 10px; border-radius: 8px; border: none; outline: none; background: rgba(255,255,255,0.3); color: var(--color-auth-forgot-password-1); }
        .input-group input::placeholder { color: #eee; }
        .field-error { margin-top: var(--space-auth-forgot-password-3); font-size: var(--typo-auth-forgot-password-font-size-2); color: #fff6f6; }
        .flash { padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.18); margin-bottom: var(--space-auth-forgot-password-2); font-size: var(--typo-auth-forgot-password-font-size-1); }
        button[type="submit"] { width: var(--dimension-auth-forgot-password-1); padding: var(--space-auth-forgot-password-1); border: 0; border-radius: 8px; background: #009e60; color: var(--color-auth-forgot-password-1); font-weight: 700; cursor: pointer; transition: 0.3s; }
        button[type="submit"]:hover { background: #007f4f; }
        .footer-links { display: flex; justify-content: space-between; margin-top: var(--space-auth-forgot-password-2); font-size: var(--typo-auth-forgot-password-font-size-2); }
        .footer-links a { color: var(--color-auth-forgot-password-1); text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('theme/img/login/group-1015.png') }}" alt="Career Institute">
        </div>
        <h2>Forgot Password</h2>
        <p class="subtitle">Enter your email below. An OTP will be sent to the system administrator, who will share it with you.</p>

        @include('partials.auth-session-status-flash')

        <form method="POST" action="{{ route('password.request') }}">
            @csrf
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your registered email" required>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">Request OTP</button>
        </form>

        <div class="footer-links">
            <a href="{{ route('login') }}">Back to Login</a>
            <a href="{{ route('password.reset.form') }}">I already have an OTP</a>
        </div>
    </div>
</body>
</html>
