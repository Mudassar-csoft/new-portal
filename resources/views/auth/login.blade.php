<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Career Institute - Sign In</title>

	<link href="{{ asset('theme/img/favicon.144x144.png') }}" rel="apple-touch-icon" type="image/png" sizes="144x144">
	<link href="{{ asset('theme/img/favicon.114x114.png') }}" rel="apple-touch-icon" type="image/png" sizes="114x114">
	<link href="{{ asset('theme/img/favicon.72x72.png') }}" rel="apple-touch-icon" type="image/png" sizes="72x72">
	<link href="{{ asset('theme/img/favicon.57x57.png') }}" rel="apple-touch-icon" type="image/png">
	<link href="{{ asset('theme/img/favicon.png') }}" rel="icon" type="image/png">
	<link href="{{ asset('theme/img/favicon.ico') }}" rel="shortcut icon">

	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: Arial, sans-serif;
		}

		body {
			min-height: 100vh;
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 20px;
			background: linear-gradient(135deg, #0a3d62, #0f766e);
		}

		.container {
			width: 850px;
			max-width: 100%;
			min-height: 420px;
			display: flex;
			border-radius: 15px;
			overflow: hidden;
			box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
			background: transparent;
		}

		.left {
			width: 50%;
			padding: 25px 40px;
			background: linear-gradient(135deg, #00b894, #00cec9);
			color: #fff;
			display: flex;
			flex-direction: column;
			justify-content: center;
		}

		.logo {
			height: 80px;
			display: flex;
			justify-content: center;
			align-items: flex-start;
		}

		.logo img {
			height: 58px;
			max-width: 100%;
			object-fit: contain;
		}

		.left h2 {
			margin: 17px 0;
			font-size: 22px;
			text-align: center;
		}

		.input-group {
			margin-bottom: 15px;
		}

		.input-group label {
			font-size: 13px;
			display: block;
			margin-bottom: 5px;
		}

		.input-group input {
			width: 100%;
			padding: 10px;
			border-radius: 8px;
			border: none;
			outline: none;
			background: rgba(255, 255, 255, 0.3);
			color: #fff;
		}

		.input-group input::placeholder {
			color: #eee;
		}

		.input-group input.is-invalid {
			box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.7);
		}

		.field-error {
			margin-top: 6px;
			font-size: 12px;
			color: #fff6f6;
		}

		.form-meta {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 12px;
			margin-bottom: 20px;
			font-size: 12px;
		}

		.remember-me {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		.remember-me input {
			accent-color: #009e60;
		}

		.forgot {
			color: #fff;
			text-decoration: none;
			cursor: pointer;
		}

		.forgot:hover {
			text-decoration: underline;
		}

		button {
			padding: 12px;
			border: none;
			border-radius: 8px;
			background: #009e60;
			color: white;
			font-weight: bold;
			cursor: pointer;
			transition: 0.3s;
		}

		button:hover {
			background: #007f4f;
		}

		button[type="submit"] {
			width: 100%;
		}

		.right {
			width: 50%;
			background: linear-gradient(135deg, #0a3d62, #1e5f74);
			color: #fff;
			display: flex;
			justify-content: center;
			align-items: center;
			position: relative;
			padding: 20px;
			overflow: hidden;
		}

		.right h1 {
			position: absolute;
			top: 97px;
			font-size: 26px;
			z-index: 2;
		}

		.right::before {
			content: "";
			position: absolute;
			top: 6px;
			right: -8px;
			width: 136px;
			height: 136px;
			background: url("{{ asset('theme/img/login/group.png') }}") no-repeat center / contain;
			opacity: 0.8;
			pointer-events: none;
		}

		.right::after {
			content: "";
			position: absolute;
			top: -34px;
			right: -28px;
			width: 240px;
			height: 240px;
			background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.34) 0%, rgba(210, 218, 226, 0.13) 34%, rgba(255, 255, 255, 0) 72%);
			pointer-events: none;
		}

		.illustration img {
			height: 160px;
			display: flex;
			justify-content: center;
			align-items: center;
			margin-top: 95px;
			max-width: 100%;
			object-fit: contain;
		}

		.general-error {
			margin-top: 16px;
			font-size: 12px;
			text-align: center;
			color: #fff6f6;
		}

		@media (max-width: 768px) {
			.container {
				flex-direction: column;
			}

			.left,
			.right {
				width: 100%;
			}

			.left {
				padding: 24px 20px;
			}

			.right {
				min-height: 240px;
			}

			.right h1 {
				top: 32px;
			}

			.illustration img {
				margin-top: 54px;
				height: 150px;
			}

			.form-meta {
				flex-direction: column;
				align-items: flex-start;
			}
		}
	</style>
</head>

<body>
	<div class="container">
		<div class="left">
			<div class="logo">
				<img src="{{ asset('theme/img/login/group-1015.png') }}" alt="Career Institute">
			</div>
			<h2>Login to your account</h2>

			<form method="POST" action="{{ route('login.store') }}">
				@csrf

				<div class="input-group">
					<label>Email</label>
					<input
						type="email"
						name="email"
						placeholder="Enter email"
						value="{{ old('email') }}"
						class="@error('email') is-invalid @enderror"
						required
					>
					@error('email')
						<div class="field-error">{{ $message }}</div>
					@enderror
				</div>

				<div class="input-group">
					<label>Password</label>
					<input
						type="password"
						name="password"
						placeholder="Enter password"
						class="@error('password') is-invalid @enderror"
						required
					>
					@error('password')
						<div class="field-error">{{ $message }}</div>
					@enderror
				</div>

				<div class="form-meta">
					<label class="remember-me">
						<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
						<span>Keep me signed in</span>
					</label>
					<a class="forgot" href="#">Forgot Password?</a>
				</div>

				<button type="submit">Login</button>

				@if($errors->any() && !$errors->has('email') && !$errors->has('password'))
					<div class="general-error">{{ $errors->first() }}</div>
				@endif
			</form>
		</div>

		<div class="right">
			<h1>Welcome Back</h1>
			<div class="illustration">
				<img src="{{ asset('theme/img/login/group-7.png') }}" alt="Welcome illustration">
			</div>
		</div>
	</div>
</body>
</html>


































