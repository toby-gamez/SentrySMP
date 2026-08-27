<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login · SentrySMP</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
</head>
<body>
<div class="admin-login-wrap">
    <div class="admin-login-card">
        <div class="admin-login-logo">
            <img src="{{ asset('images/logo.png') }}" alt="SentrySMP">
        </div>
        <h2 class="admin-login-title">Admin Login</h2>

        @if($errors->has('credentials'))
            <div class="alert alert-error">{{ $errors->first('credentials') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="admin">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;padding:12px;font-size:15px;margin:8px 0 0;">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        <p style="text-align:center;margin-top:16px;font-size:12px;color:#555;">
            <a href="{{ route('home') }}">← Back to shop</a>
        </p>
    </div>
</div>
</body>
</html>
