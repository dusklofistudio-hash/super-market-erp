# Super Market ERP Management System

Multi-branch supermarket ERP / POS built on **Laravel 12 + Inertia (React) + Bootstrap 5**.

## Stack

- **Backend:** Laravel 12, PHP 8.3, SQLite (default) / MySQL / Postgres
- **Frontend:** Inertia.js + React 19, Vite, Bootstrap 5
- **Server-side tables:** [Yajra DataTables](https://yajrabox.com/docs/laravel-datatables)
- **Toasts:** [PHPFlasher (SweetAlert2)](https://php-flasher.io/)
- **Confirms:** SweetAlert2
- **Dates:** flatpickr
- **Selects:** Tom Select
- **i18n:** English + Khmer with no-refresh switching
- **RBAC:** Manual (no external package) — Roles · Permissions · Per-branch user assignment

## Phase 1 modules (this PR)

- Authentication (login, profile, change password)
- Manual RBAC (Roles, Permissions, manual middleware)
- Branches (multi-branch with per-user assignment + default branch)
- Languages + Translation editor + KH/EN switcher (no page reload)
- Settings
- Catalog: Categories, Brands, Units, Tax rates, Products
- Parties: Suppliers, Customers, Customer groups

## Phase 2 (follow-up PRs)

Purchases · Sales / POS · Stock adjustments · Stock transfers · Expenses · Reports

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Then sign in at `http://127.0.0.1:8000/login`:

| Username | Password |
| -------- | -------- |
| `admin`  | `password` |

## Local development

```bash
composer run dev   # serves php artisan serve + vite + queue + pail concurrently
```

Or run them individually:

```bash
php artisan serve
npm run dev
```

## Project layout

```
app/
  Http/Controllers/Admin/      # All admin CRUD controllers
  Http/Middleware/             # SetLocale, EnsurePermission, HandleInertiaRequests
  Http/Requests/Admin/         # FormRequests
  Models/                      # Eloquent models (manual RBAC via Concerns/HasRolesAndPermissions)
  Services/TranslationService  # File + DB-overridable translations
  Support/Uploads              # Tiny file-upload helper
database/
  migrations/2026_05_13_*      # Single consolidated migration (~30 tables)
  seeders/                     # One seeder per table (41 total) orchestrated by DatabaseSeeder
lang/
  en/messages.php              # English UI strings
  kh/messages.php              # Khmer UI strings
resources/
  views/admin/layouts/         # 5 Blade partials (admin_layout, head, header, scripts, left_sidebar)
  views/auth/login.blade.php   # Blade-rendered login page
  sass/app.scss                # Bootstrap 5 + flatpickr + Tom Select + DataTables CSS
  js/
    app.jsx                    # Inertia entry; wraps in I18n + Permission providers
    bootstrap.js               # axios + jQuery + bootstrap globals
    lib/i18n.jsx               # React translation context + no-refresh switcher
    lib/permissions.jsx        # React `can()` helper + <Can> component
    lib/flasher.js             # SweetAlert2 toast + delete confirm
    Hooks/                     # useFlatpickr, useTomSelect, useDataTable
    Components/                # Form fields, ServerDataTable, PageHeader, RowActions
    Pages/                     # All Inertia React pages (Dashboard, Branches, Users, …)
routes/web.php                 # All routes; admin/* guarded by permission middleware
```

## Architecture notes

- The Blade master layout (`resources/views/admin/layouts/admin_layout.blade.php`) is set as
  Inertia's `rootView` in `App\Http\Middleware\HandleInertiaRequests`. Inertia pages render
  inside `@inertia`, while the Blade chrome (sidebar + header + scripts) wraps everything.
- The language switcher in `resources/views/admin/layouts/header.blade.php` posts to
  `POST /admin/locale` and uses the returned translation map to update both the Blade chrome
  (menu labels via `data-i18n`) and the React side (`I18nProvider` listens via the
  `smk:locale-changed` custom event).
- List pages mount a `<ServerDataTable>` React component that initialises a jQuery DataTable
  in server-side mode pointing at a Yajra endpoint (`/admin/<resource>/data`). Delete buttons
  are rendered as raw HTML with `data-smk-delete="…"` and intercepted by a global delegated
  handler in `resources/js/Components/RowActions.jsx` that shows a SweetAlert2 confirm and
  fires the Inertia `delete()` call.
- Manual RBAC is implemented as `App\Models\Concerns\HasRolesAndPermissions` (trait on the
  `User` model). Routes are guarded by the `permission:<slug>` middleware alias defined in
  `bootstrap/app.php`. React uses the `can()` helper from `resources/js/lib/permissions.jsx`.
