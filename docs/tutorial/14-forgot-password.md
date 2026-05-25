# Chapter 14 — Forgot password flow

The `password_reset_tokens` table is in the migration; this chapter
wires the two endpoints + two Blade pages that let an unauthenticated
user request a reset link by email and then set a new password.

We do this **without** installing Laravel Fortify or Breeze — just the
plain `Password` broker that ships with the framework.

## 1. The table (Laravel-standard)

```php
Schema::create('password_reset_tokens', function (Blueprint $t) {
    $t->string('email')->primary();
    $t->string('token');
    $t->timestamp('created_at')->nullable();
});
```

Laravel's `Password::sendResetLink()` and `Password::reset()` use
exactly these column names.

## 2. Routes

```php
// routes/auth.php (or web.php)
use App\Http\Controllers\Auth\{PasswordResetLinkController, NewPasswordController};

Route::middleware('guest')->group(function () {
    Route::get ('/forgot-password',  [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password',  [PasswordResetLinkController::class, 'store']) ->name('password.email');
    Route::get ('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',         [NewPasswordController::class, 'store']) ->name('password.update');
});
```

Note: the `guest` middleware redirects logged-in users back to `/admin`.
Log out before testing.

## 3. `PasswordResetLinkController`

```php
class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');     // Blade form (not Inertia)
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
```

`Password::sendResetLink` does three things:

1. Looks up the user by email; if missing, returns `INVALID_USER`.
2. Generates a token and upserts a row in `password_reset_tokens`.
3. Dispatches a `ResetPassword` notification — by default mailable.

For development, set `MAIL_MAILER=log` in `.env` so the reset URL ends
up in `storage/logs/laravel.log` instead of failing.

## 4. `NewPasswordController`

```php
class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
```

## 5. The two Blade views

`resources/views/auth/forgot-password.blade.php`:

```blade
@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('password.email') }}" class="card p-4">
    @csrf

    <h4>{{ __('messages.auth.forgot_title') }}</h4>
    <p class="text-muted">{{ __('messages.auth.forgot_intro') }}</p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <label class="form-label">{{ __('messages.fields.email') }}</label>
    <input name="email" type="email" class="form-control" required>
    @error('email')<small class="text-danger">{{ $message }}</small>@enderror

    <button type="submit" class="btn btn-primary mt-3 w-100">
        {{ __('messages.auth.send_reset_link') }}
    </button>
    <a href="{{ route('login') }}" class="d-block text-center mt-2">
        {{ __('messages.auth.back_to_signin') }}
    </a>
</form>
@endsection
```

`resources/views/auth/reset-password.blade.php`:

```blade
@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="card p-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <label class="form-label">{{ __('messages.fields.email') }}</label>
    <input name="email" type="email" class="form-control" value="{{ $email }}" required>

    <label class="form-label mt-2">{{ __('messages.fields.password') }}</label>
    <input name="password" type="password" class="form-control" required>
    @error('password')<small class="text-danger">{{ $message }}</small>@enderror

    <label class="form-label mt-2">{{ __('messages.fields.password_confirmation') }}</label>
    <input name="password_confirmation" type="password" class="form-control" required>

    <button type="submit" class="btn btn-primary mt-3 w-100">
        {{ __('messages.auth.reset_password') }}
    </button>
</form>
@endsection
```

Both forms are server-rendered Blade because Inertia is overkill for a
public, one-shot auth screen.

## 6. Don't ship dead routes

PR #7 of this repo discovered a `/admin/forgot-password/done` route
that referenced a non-existent view and 500'd. Lesson: every named
route must have a working view, or remove the route. Run
`php artisan route:list | grep done` periodically to catch orphans.

## 7. Localized notification

The mailable that ships with Laravel's `ResetPassword` notification can
be customized to render the email in the user's locale:

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Auth\Notifications\ResetPassword;

public function boot(): void
{
    ResetPassword::toMailUsing(function ($notifiable, string $token) {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->locale($notifiable->locale ?? 'en')
            ->subject(__('messages.auth.email.subject'))
            ->line(__('messages.auth.email.intro'))
            ->action(__('messages.auth.email.cta'), $url)
            ->line(__('messages.auth.email.outro'));
    });
}
```

## Verify

```bash
php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
echo "rows before: " . DB::table("password_reset_tokens")->count() . "\n";
'

curl -s -c c.txt -b c.txt http://127.0.0.1:8000/forgot-password >/dev/null   # GET form for CSRF
TOKEN=$(grep XSRF-TOKEN c.txt | tail -1 | awk "{print \$7}")
curl -s -b c.txt -X POST http://127.0.0.1:8000/forgot-password \
     -H "X-XSRF-TOKEN: $TOKEN" \
     -d 'email=admin@example.com&_token=…' >/dev/null

php artisan tinker --execute='
use Illuminate\Support\Facades\DB;
echo "rows after: " . DB::table("password_reset_tokens")->count() . "\n";
'
```

Easier: hit the form in the browser, submit `admin@example.com`, then
re-run the tinker count. The count rises by 1 (or stays the same if a
row already existed — the broker uses `updateOrCreate`).

Check `storage/logs/laravel.log` for the reset URL with the token, copy
it into a new tab, set a new password, and confirm you can log in with
the new credentials.
