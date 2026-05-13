<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $available = Language::active()->pluck('code')->all();
        $fallback = config('app.locale');

        $locale = $request->session()->get('locale')
            ?: ($request->user()?->locale ?? $fallback);

        if (! in_array($locale, $available, true)) {
            $locale = $fallback;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
