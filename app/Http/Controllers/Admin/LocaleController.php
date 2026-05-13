<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Switches the UI locale and returns the full translation map for that locale.
 * Used by the language switcher in resources/views/admin/layouts/header.blade.php
 * (jQuery-driven) and resources/js/lib/i18n.jsx (React-driven). The page does
 * NOT refresh: callers update their reactive state from the JSON payload.
 */
class LocaleController extends Controller
{
    public function switch(Request $request, TranslationService $service): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'max:8'],
        ]);

        $available = Language::active()->pluck('code')->all();
        abort_unless(in_array($data['locale'], $available, true), 422, 'Locale not available');

        $request->session()->put('locale', $data['locale']);
        if ($user = $request->user()) {
            $user->forceFill(['locale' => $data['locale']])->saveQuietly();
        }
        app()->setLocale($data['locale']);

        $translations = $service->all($data['locale']);

        // Pre-extract a small map of menu labels so the Blade chrome can do an
        // instant in-place swap without re-fetching.
        $menu = collect($translations)
            ->filter(fn ($_v, $k) => str_starts_with($k, 'menu.'))
            ->all();

        return response()->json([
            'locale' => $data['locale'],
            'translations' => $translations,
            'menu' => $menu,
        ]);
    }
}
