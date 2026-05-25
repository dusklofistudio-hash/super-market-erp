# Chapter 01 — Project bootstrap

Goal: get from an empty directory to a Laravel 12 + Inertia/React app
that loads in your browser, with Yajra DataTables, PHPFlasher,
SweetAlert2, flatpickr, Tom Select, Bootstrap 5, and Ziggy all wired
through Vite.

By the end of this chapter, `php artisan serve` + `npm run dev` will
serve a hello-world Inertia page at `http://127.0.0.1:8000`.

## 1. Create the Laravel project

```bash
cd ~/code
composer create-project laravel/laravel super-market-erp "12.*"
cd super-market-erp
```

Composer pulls the Laravel 12 skeleton and runs `php artisan key:generate`
automatically. Confirm by running the placeholder app:

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` — you should see the default Laravel
welcome page. Stop the server with `Ctrl+C`.

## 2. Configure the database

The default `.env` uses SQLite. Confirm the SQLite file exists:

```bash
ls database/database.sqlite
```

If you prefer MySQL, edit `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=super_market_erp
DB_USERNAME=erp
DB_PASSWORD=password
```

Then verify with the default Laravel migrations:

```bash
php artisan migrate
```

If migrations fail, fix the credentials before going further.

## 3. Install Inertia (server side)

```bash
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
```

That command creates `app/Http/Middleware/HandleInertiaRequests.php`.
Open `bootstrap/app.php` and register the middleware in the `web`
group:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

## 4. Install React, Inertia client, Vite plugin

```bash
npm install --save-dev @vitejs/plugin-react
npm install react@^19 react-dom@^19 @inertiajs/react
```

Add the React plugin to `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
```

Rename `resources/js/app.js` to `resources/js/app.jsx` and replace its
contents with the Inertia client bootstrap:

```jsx
import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

createInertiaApp({
    resolve: (name) =>
        import.meta.glob('./Pages/**/*.jsx', { eager: true })[`./Pages/${name}.jsx`],
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
```

## 5. Install Bootstrap 5, jQuery, DataTables, and friends

The list below mirrors `package.json` in the finished repo. Install them
all in one go:

```bash
npm install bootstrap @popperjs/core jquery \
            datatables.net datatables.net-bs5 \
            datatables.net-buttons datatables.net-buttons-bs5 \
            datatables.net-responsive-bs5 \
            sweetalert2 flatpickr tom-select sass \
            ziggy-js
```

## 6. Install PHP-side packages

```bash
composer require yajra/laravel-datatables-oracle
composer require php-flasher/flasher-sweetalert-laravel
composer require tightenco/ziggy
```

Publish Yajra's config (optional but useful for tuning):

```bash
php artisan vendor:publish --provider="Yajra\DataTables\DataTablesServiceProvider"
```

## 7. Add a root Blade view for Inertia

The default Laravel `welcome.blade.php` works, but we will replace it
with a Blade chrome in Chapter 03. For now, create
`resources/views/app.blade.php`:

```blade
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Market ERP</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @routes
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

Tell Inertia to use this view by editing
`app/Http/Middleware/HandleInertiaRequests.php`:

```php
protected $rootView = 'app';
```

## 8. Wire Ziggy

Ziggy exposes Laravel route names to JavaScript. The `@routes` Blade
directive (added above) already writes them. To use them in React,
import the helper in `resources/js/bootstrap.js`:

```js
import { route } from 'ziggy-js';
window.route = route;
```

After Chapter 04 adds the admin routes, you will call
`route('admin.products.index')` in JSX instead of hard-coding URLs.

## 9. First Inertia route

Replace the `/` route in `routes/web.php` with an Inertia render:

```php
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', ['greeting' => 'Hello']));
```

Create `resources/js/Pages/Welcome.jsx`:

```jsx
export default function Welcome({ greeting }) {
    return <h1 style={{ padding: 24 }}>{greeting}, Inertia!</h1>;
}
```

## 10. Run it

In one terminal:

```bash
php artisan serve
```

In another terminal:

```bash
npm run dev
```

Open `http://127.0.0.1:8000` — you should see "Hello, Inertia!" rendered
by React. View source to confirm the HTML contains a single empty
`<div id="app">` and a `<script>` tag pointing at the Vite dev server.

## Verify

- `php artisan route:list` lists `GET /`.
- `composer show yajra/laravel-datatables-oracle` prints a version.
- `composer show inertiajs/inertia-laravel` prints a version.
- `npm ls react` prints `react@19.x`.
- The browser shows "Hello, Inertia!" with no console errors.

If any of the four checks fails, re-read the corresponding step. Do not
proceed to Chapter 02 with a broken bootstrap — every later chapter
assumes the Inertia + React + Vite pipeline already works.
