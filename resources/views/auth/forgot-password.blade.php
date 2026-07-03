<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Institute - Forgot Password</title>
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <style>
        :root {
            --typo-auth-forgot-password-font-size-1: 13px;
            --typo-auth-forgot-password-font-size-2: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; background: linear-gradient(135deg, #0a3d62, #0f766e); }
        .container { width: 460px; max-width: 100%; background: linear-gradient(135deg, #00b894, #00cec9); color: #fff; padding: 32px 36px; border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .logo { display: flex; justify-content: center; margin-bottom: 12px; }
        .logo img { height: 58px; max-width: 100%; object-fit: contain; }
        h2 { text-align: center; font-size: 22px; margin: 8px 0 18px; }
        p.subtitle { text-align: center; font-size: var(--typo-auth-forgot-password-font-size-1); margin-bottom: 22px; opacity: 0.9; }
        .input-group { margin-bottom: 16px; }
        .input-group label { display: block; font-size: var(--typo-auth-forgot-password-font-size-1); margin-bottom: 6px; }
        .input-group input { width: 100%; padding: 10px; border-radius: 8px; border: none; outline: none; background: rgba(255,255,255,0.3); color: #fff; }
        .input-group input::placeholder { color: #eee; }
        .field-error { margin-top: 6px; font-size: var(--typo-auth-forgot-password-font-size-2); color: #fff6f6; }
        .flash { padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.18); margin-bottom: 16px; font-size: var(--typo-auth-forgot-password-font-size-1); }
        button[type="submit"] { width: 100%; padding: 12px; border: 0; border-radius: 8px; background: #009e60; color: #fff; font-weight: 700; cursor: pointer; transition: 0.3s; }
        button[type="submit"]:hover { background: #007f4f; }
        .footer-links { display: flex; justify-content: space-between; margin-top: 16px; font-size: var(--typo-auth-forgot-password-font-size-2); }
        .footer-links a { color: #fff; text-decoration: none; }
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

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

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
