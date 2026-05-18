<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.login') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans:400,500,600,700|noto-sans-khmer:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:linear-gradient(135deg,#2563eb,#1e40af);">
    <div class="card shadow-lg border-0" style="width:100%;max-width:420px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:14px;background:#2563eb;color:#fff;font-size:28px;font-weight:700;">S</div>
                <h4 class="mt-3 mb-0">{{ config('app.name') }}</h4>
                <p class="text-muted small mb-0">{{ __('messages.pages.dashboard.title') }}</p>
            </div>

            @if (session('error'))
                <div class="alert alert-danger small">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.username') }} / {{ __('messages.email') }}</label>
                    <input type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" required autofocus>
                    @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.password') }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">{{ __('messages.remember_me') }}</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ __('messages.login') }}</button>
                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}" class="small">{{ __('messages.auth.forgot_password.title') }}?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
