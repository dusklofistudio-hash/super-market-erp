<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.auth.forgot_password.title') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans:400,500,600,700|noto-sans-khmer:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:linear-gradient(135deg,#2563eb,#1e40af);">
    <div class="card shadow-lg border-0" style="width:100%;max-width:420px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <h4 class="mt-3 mb-0">{{ __('messages.auth.forgot_password.title') }}</h4>
                <p class="text-muted small mb-0">{{ __('messages.auth.forgot_password.subtitle') }}</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success small">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ __('messages.auth.forgot_password.send_link') }}</button>
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="small">{{ __('messages.auth.forgot_password.back_to_login') }}</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
