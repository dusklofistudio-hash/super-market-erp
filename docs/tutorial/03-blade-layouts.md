# Chapter 03 — Blade layouts

The user asked for a Blade chrome (sidebar + header + scripts) that wraps
every Inertia/React page. This chapter explains why we do it that way and
walks through each of the four partials plus the master layout.

## The hybrid rendering model

Without Blade, every page would need to render the sidebar, header, and
import jQuery + DataTables + SweetAlert2 + flatpickr + Tom Select on every
client-side route change. That is slow, brittle, and breaks the no-refresh
i18n flow we need in Chapter 05.

With Blade chrome:

- **One** Blade master template (`admin_layout.blade.php`) is the root
  view for **all** admin Inertia requests.
- Inertia injects the React-rendered page content into a `<div id="app">`
  inside the master.
- Sidebar, header, and shared scripts render once per HTTP visit.
- The KH/EN switcher fires a custom DOM event that both Blade and React
  listen to, so locale changes never need a full reload.

## File layout

```text
resources/views/admin/layouts/
├── admin_layout.blade.php   # the master template, used by every admin page
├── head.blade.php           # <head> partial — meta, css, vite
├── header.blade.php         # top bar — brand, language pill, user dropdown
├── left_sidebar.blade.php   # navigation tree, RBAC-gated
└── scripts.blade.php        # global JS — jquery, DT, sweetalert, flatpickr, tom-select
```

## Tell Inertia to use the new layout

In `app/Http/Middleware/HandleInertiaRequests.php`:

```php
protected $rootView = 'admin.layouts.admin_layout';
```

If you have public pages (login, forgot password) that should NOT use the
admin chrome, override the root view per-controller:

```php
return Inertia::render('Auth/Login')->rootView('app');
```

## `admin_layout.blade.php` — the master

```blade
<!doctype html>
<html lang="{{ session('locale', 'en') }}" data-locale="{{ session('locale', 'en') }}">
@include('admin.layouts.head')
<body class="d-flex">
    @include('admin.layouts.left_sidebar')

    <div class="flex-grow-1">
        @include('admin.layouts.header')
        <main class="p-3">
            @inertia
        </main>
    </div>

    @include('admin.layouts.scripts')
</body>
</html>
```

Notice the `lang` attribute. Setting it from `session('locale', 'en')`
gives screen readers and the browser the current locale. The matching
`data-locale` attribute is what JavaScript polls in
`smk:locale-changed` (Chapter 05).

## `head.blade.php`

```blade
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Super Market ERP') }}</title>
    @viteReactRefresh
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])
    @routes
    @inertiaHead
</head>
```

- `csrf-token` meta — read by axios in `bootstrap.js` and by inline
  jQuery code in DataTable row actions.
- `@viteReactRefresh` — Vite's React Fast Refresh in dev mode.
- `@vite([...])` — bundles SCSS + JSX entry points.
- `@routes` — Ziggy emits the route name → URL map.
- `@inertiaHead` — Inertia head manager.

## `header.blade.php`

The header sits above page content and contains three things: brand
name, language pill, and user dropdown.

```blade
<header class="bg-white border-bottom py-2 px-3 d-flex align-items-center">
    <button id="sidebar-toggle" class="btn btn-link p-0 me-2 d-md-none">
        <i class="bi bi-list fs-4"></i>
    </button>

    <a href="{{ route('admin.dashboard') }}" class="navbar-brand mb-0"
       data-i18n="brand.name">{{ __('messages.brand.name') }}</a>

    <div class="ms-auto d-flex align-items-center gap-2">
        @include('admin.layouts.partials.language-pill')
        @include('admin.layouts.partials.user-dropdown')
    </div>
</header>
```

`data-i18n="brand.name"` makes the title swap when the locale event
fires. Chapter 05 covers the event in detail.

## `left_sidebar.blade.php`

The sidebar is the only file that grows as you add modules. The
critical pattern is **never** render an `<a>` to a route the current
user lacks permission for.

```blade
<aside id="sidebar" class="bg-dark text-white" style="width: 240px; min-height: 100vh;">
    <div class="p-3 border-bottom">
        <h6 class="text-uppercase small text-muted mb-0" data-i18n="brand.short">SMK</h6>
    </div>

    <nav class="px-2 py-3">
        <a href="{{ route('admin.dashboard') }}" class="d-block py-1 text-white"
           data-i18n="nav.dashboard">{{ __('messages.nav.dashboard') }}</a>

        @if(auth()->user()->can('catalog.view'))
            <h6 class="text-uppercase small text-muted mt-3" data-i18n="nav.catalog">
                {{ __('messages.nav.catalog') }}
            </h6>
            @can('products.view')
                <a href="{{ route('admin.products.index') }}"
                   data-i18n="nav.products" class="d-block py-1 text-white">
                    {{ __('messages.nav.products') }}
                </a>
            @endcan
            {{-- ...categories, brands, units, tax_rates --}}
        @endif

        {{-- repeat for operations, reports, parties, users --}}
    </nav>
</aside>
```

The two important pieces:

1. **`@can('…')` directive** wraps each link. Chapter 04 builds the
   `can()` machinery; for now treat it as a boolean check against the
   logged-in user's permissions.
2. **`data-i18n="nav.products"` attribute** lets the client-side
   switcher swap the link text without a page reload.

## `scripts.blade.php`

```blade
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/js/bootstrap.bundle.min.js"></script>
@flasher_render

<script>
    // Toggle sidebar on small screens
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('d-none');
    });
</script>
```

We keep this file deliberately thin: SweetAlert2, flatpickr, Tom Select,
DataTables, jQuery all ship with the React bundle via Vite (Chapter 07).
The only inline behavior is the responsive sidebar toggle and the
PHPFlasher render directive.

## Common pitfalls

- **Forgetting `@inertia`** — Inertia silently renders nothing inside
  the `<main>` element. Always verify the master contains `@inertia`.
- **Forgetting `@routes`** — Ziggy calls in React fail with
  `route is not defined`. Add `@routes` before `@inertiaHead`.
- **CSP issues with inline scripts** — if you add a Content-Security-Policy
  header later, the inline `<script>` in `scripts.blade.php` will need a
  nonce or moved to a `.js` file.

## Verify

Hit any admin route and view source. You should see:

1. `<aside id="sidebar">` rendered server-side with English labels.
2. `<div id="app">` wrapping the Inertia React content.
3. Bootstrap 5 JS bundle loaded before `</body>`.
4. `data-i18n="…"` attributes on every sidebar link and the brand name.
5. The Ziggy `<script>` block in `<head>` containing a `routes` object.

If any of those is missing, the chapter is not finished — fix before
proceeding to Chapter 04, which assumes the sidebar already exists.
