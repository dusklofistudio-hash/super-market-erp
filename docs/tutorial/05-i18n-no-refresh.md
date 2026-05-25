# Chapter 05 — KH/EN no-refresh language switching

The brief: switching between Khmer and English must update sidebar,
header, dropdowns, and React-rendered page content **without** reloading
the page. This chapter explains the three pieces that make that work:

1. A `SetLocale` middleware that resolves the locale on every request.
2. A `LocaleController::switch` endpoint that updates the session and
   returns the full translation dictionary as JSON.
3. A custom DOM event `smk:locale-changed` that both the Blade-rendered
   sidebar (jQuery) and the React tree (Inertia hook) listen for.

## 1. The `languages` table

Two seeded rows, both `is_active = true`:

| code | name      | native      | direction |
|------|-----------|-------------|-----------|
| `en` | English   | English     | ltr       |
| `kh` | Khmer     | ភាសាខ្មែរ  | ltr       |

You can add more (Spanish, Vietnamese, …) by inserting rows and shipping
matching `lang/<code>/messages.php` files. The switcher discovers active
languages automatically.

## 2. The `SetLocale` middleware

```php
// app/Http/Middleware/SetLocale.php
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $available = Language::active()->pluck('code')->all();
        $fallback  = config('app.locale');

        $locale = $request->session()->get('locale')
            ?: ($request->user()?->locale ?? $fallback);

        if (! in_array($locale, $available, true)) {
            $locale = $fallback;
        }

        app()->setLocale($locale);
        return $next($request);
    }
}
```

Resolution order:

1. **Session** — what the user clicked most recently this browser tab.
2. **User row** — `users.locale` column, persisted across logins.
3. **Config fallback** — `config('app.locale')`, defaults to `en`.

Register globally in `bootstrap/app.php`:

```php
$middleware->web(append: [
    \App\Http\Middleware\SetLocale::class,
    \App\Http\Middleware\HandleInertiaRequests::class,
]);
```

## 3. Translation files

Two flat arrays:

```text
lang/en/messages.php
lang/kh/messages.php
```

Both share the same key tree. Sample fragment:

```php
return [
    'brand' => [
        'name'  => 'Super Market ERP',
        'short' => 'SMK',
    ],
    'nav' => [
        'dashboard'  => 'Dashboard',
        'operations' => 'Operations',
        'reports'    => 'Reports',
        'products'   => 'Products',
        // ...
    ],
    'common' => [
        'save'   => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'create' => 'Create',
    ],
];
```

Khmer equivalent:

```php
return [
    'brand' => ['name' => 'ប្រព័ន្ធ ERP ផ្សារ', 'short' => 'SMK'],
    'nav'   => [
        'dashboard'  => 'ផ្ទាំងគ្រប់គ្រង',
        'operations' => 'ប្រតិបត្តិការ',
        'reports'    => 'របាយការណ៍',
        'products'   => 'ផលិតផល',
    ],
    'common' => [
        'save'   => 'រក្សាទុក',
        'cancel' => 'បោះបង់',
        'delete' => 'លុប',
        'create' => 'បង្កើត',
    ],
];
```

## 4. The `TranslationService`

The DB also holds a `translations` table with `key`, `locale`, `value`
columns. The service merges file + DB into one flat dot-notation array:

```php
// app/Services/TranslationService.php
public function all(string $locale): array
{
    $fromFiles = Arr::dot(__('messages', [], $locale));   // tree → flat
    $fromDb    = Translation::where('locale', $locale)
                    ->pluck('value', 'key')->all();
    return $fromFiles + $fromDb;   // DB overrides
}
```

This lets you ship initial strings in PHP files **and** allow non-tech
users to edit them through the Translations admin page later.

## 5. The `LocaleController::switch` endpoint

```php
// app/Http/Controllers/Admin/LocaleController.php
public function switch(Request $request, TranslationService $service): JsonResponse
{
    $data = $request->validate(['locale' => ['required', 'string', 'max:8']]);

    $available = Language::active()->pluck('code')->all();
    abort_unless(in_array($data['locale'], $available, true), 422, 'Locale not available');

    $request->session()->put('locale', $data['locale']);
    if ($user = $request->user()) {
        $user->forceFill(['locale' => $data['locale']])->saveQuietly();
    }
    app()->setLocale($data['locale']);

    return response()->json([
        'locale'       => $data['locale'],
        'translations' => $service->all($data['locale']),
    ]);
}
```

Route:

```php
Route::middleware('auth')->post('/locale/switch', [LocaleController::class, 'switch'])
    ->name('locale.switch');
```

## 6. The Blade-side switcher (jQuery)

Lives in `header.blade.php`. Three responsibilities:

1. Detect a click on a language option.
2. POST to `/locale/switch` with the new code.
3. Walk every element with a `data-i18n` attribute and replace its text
   with the matching value from the returned JSON.
4. Fire `smk:locale-changed` so React can react too.

```blade
<div class="dropdown">
    <button class="btn btn-sm border d-flex align-items-center" data-bs-toggle="dropdown">
        <span id="lang-pill">{{ strtoupper(session('locale', 'en')) }}</span>
    </button>
    <ul class="dropdown-menu">
        @foreach($languages as $lang)
            <li>
                <button type="button" class="dropdown-item lang-option"
                        data-locale="{{ $lang->code }}">
                    {{ $lang->native }}
                </button>
            </li>
        @endforeach
    </ul>
</div>

<script>
    document.querySelectorAll('.lang-option').forEach(btn => {
        btn.addEventListener('click', async () => {
            const locale = btn.dataset.locale;
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            const res = await fetch('/locale/switch', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json',
                          'Accept': 'application/json'},
                body: JSON.stringify({locale})
            });
            if (!res.ok) return;
            const data = await res.json();

            document.documentElement.setAttribute('data-locale', data.locale);
            document.documentElement.setAttribute('lang', data.locale);
            document.getElementById('lang-pill').textContent = data.locale.toUpperCase();

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (data.translations[`messages.${key}`])
                    el.textContent = data.translations[`messages.${key}`];
            });

            window.dispatchEvent(new CustomEvent('smk:locale-changed', {
                detail: { locale: data.locale, translations: data.translations }
            }));
        });
    });
</script>
```

The `messages.` prefix matters: `Arr::dot()` flattens
`['nav']['products']` to `nav.products`, but `__()` operates inside the
`messages` namespace, so the keys we publish to JS are
`messages.nav.products`.

## 7. The React-side listener

`resources/js/lib/I18nProvider.jsx`:

```jsx
import { createContext, useContext, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

const I18nContext = createContext({ locale: 'en', t: (k) => k });

export function I18nProvider({ children }) {
    const { initialTranslations, initialLocale } = usePage().props;
    const [state, setState] = useState({
        locale: initialLocale ?? 'en',
        dict: initialTranslations ?? {},
    });

    useEffect(() => {
        const handler = (e) => setState({
            locale: e.detail.locale,
            dict: e.detail.translations,
        });
        window.addEventListener('smk:locale-changed', handler);
        return () => window.removeEventListener('smk:locale-changed', handler);
    }, []);

    return (
        <I18nContext.Provider value={{
            locale: state.locale,
            t: (key) => state.dict[`messages.${key}`] ?? key,
        }}>
            {children}
        </I18nContext.Provider>
    );
}

export const useI18n = () => useContext(I18nContext);
```

Share the initial dictionary from
`HandleInertiaRequests::share()`:

```php
'initialLocale'       => fn () => app()->getLocale(),
'initialTranslations' => fn () => app(TranslationService::class)->all(app()->getLocale()),
```

In any page:

```jsx
const { t } = useI18n();
return <h4>{t('nav.dashboard')}</h4>;
```

## 8. The two bugs to watch out for

### Bug A — Sidebar not updating

If the sidebar text stays English after switching, you missed
`data-i18n="…"` on the links. Add the attribute and try again.

### Bug B — React tree not updating

If the sidebar updates but the `h4` inside a React page stays English,
your `I18nProvider` is not listening to the event. Confirm `app.jsx`
wraps the Inertia App component in `<I18nProvider>` and that
`I18nProvider.jsx` is imported (not tree-shaken).

The fix that landed in PR #3 was to add an explicit side-effect import
in `resources/js/app.jsx` so Vite would not drop the module:

```jsx
import './lib/I18nProvider';   // side-effect, ensures bundle
```

## Verify

1. Log in as admin.
2. Open DevTools → Network. Click the language pill → ខ្មែរ.
3. You should see exactly ONE POST `/locale/switch` returning JSON.
4. URL stays `/admin` (no full reload, no browser progress bar).
5. Sidebar labels change to Khmer.
6. Dashboard `h4` welcome heading changes to Khmer.
7. Click back to English — everything reverts.

If only the pill or only the sidebar updates, walk back through this
chapter and fix before Chapter 06.
