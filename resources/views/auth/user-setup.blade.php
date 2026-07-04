<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Institute - Set Your Password</title>
    <link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
    <style>
        :root {
            --dimension-auth-user-setup-1: 100%;
            --space-auth-user-setup-1: 12px;
            --space-auth-user-setup-2: 6px;
            --color-auth-user-setup-1: #fff;
            --typo-auth-user-setup-font-size-1: 13px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; background: linear-gradient(135deg, #0a3d62, #0f766e); }
        .container { width: 460px; max-width: var(--dimension-auth-user-setup-1); background: linear-gradient(135deg, #00b894, #00cec9); color: var(--color-auth-user-setup-1); padding: 32px 36px; border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .logo { display: flex; justify-content: center; margin-bottom: var(--space-auth-user-setup-1); }
        .logo img { height: 58px; max-width: var(--dimension-auth-user-setup-1); object-fit: contain; }
        h2 { text-align: center; font-size: 22px; margin: 8px 0 12px; }
        .user-info { text-align: center; font-size: var(--typo-auth-user-setup-font-size-1); margin-bottom: 22px; opacity: 0.92; }
        .user-info strong { display: block; font-size: 14px; margin-top: 4px; }
        .input-group { margin-bottom: 16px; }
        .input-group label { display: block; font-size: var(--typo-auth-user-setup-font-size-1); margin-bottom: var(--space-auth-user-setup-2); }
        .input-group input { width: var(--dimension-auth-user-setup-1); padding: 10px; border-radius: 8px; border: none; outline: none; background: rgba(255,255,255,0.3); color: var(--color-auth-user-setup-1); }
        .input-group input::placeholder { color: #eee; }
        .field-error { margin-top: var(--space-auth-user-setup-2); font-size: 12px; color: #fff6f6; }
        button[type="submit"] { width: var(--dimension-auth-user-setup-1); padding: var(--space-auth-user-setup-1); border: 0; border-radius: 8px; background: #009e60; color: var(--color-auth-user-setup-1); font-weight: 700; cursor: pointer; transition: 0.3s; }
        button[type="submit"]:hover { background: #007f4f; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('theme/img/login/group-1015.png') }}" alt="Career Institute">
        </div>
        <h2>Set Your Password</h2>
        <div class="user-info">
            Welcome, <strong>{{ $user->name }}</strong>
            Set a password to activate your account.
        </div>

        <form method="POST" action="{{ $signedUrl }}">
            @csrf
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Min 8 characters" required autocomplete="new-password">
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="Repeat password" required autocomplete="new-password">
            </div>
            <button type="submit">Activate My Account</button>
        </form>
    </div>
</body>
</html>
